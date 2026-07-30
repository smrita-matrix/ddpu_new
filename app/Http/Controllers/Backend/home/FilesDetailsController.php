<?php

namespace App\Http\Controllers\Backend\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\File;
use App\Models\FileDetail;
use App\Imports\FileDataImport;
use App\Exports\FileDataExport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Barryvdh\DomPDF\Facade as PDF; // ✅ IMPORTANT

class FilesDetailsController extends Controller
{
    public function index(Request $request)
    {
        $query = File::latest();

        // Period filter (from-to date)
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        if ($fromDate) {
            $query->whereDate('uploaded_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('uploaded_date', '<=', $toDate);
        }

        $files = $query->get();

        return view('backend.files.index', compact('files', 'fromDate', 'toDate'));
    }

public function import(Request $request)
    {
        $request->validate([
            'file'            => 'required|mimes:xlsx,xls,csv,ods,xml,txt|max:10240',
            'notes'           => 'nullable|string|max:255',
            'collection_date' => 'required|date',
        ]);

        $uploadedFile   = $request->file('file');
        $originalName   = $uploadedFile->getClientOriginalName();
        $collectionDate = $request->collection_date;
        $notes          = $request->notes;

        $submissionDate = strtoupper(Carbon::parse($collectionDate)->format('d/M/Y'));
        $uploadDate     = strtoupper(now()->format('d/M/Y'));

        $fastpayFileName = Carbon::parse($collectionDate)->format('y-m-d')
            . ' DDPU (Monthly on the 10th).csv';

        // ✅ CREATE FILE RECORD
        $fileRecord = File::create([
            'file_name'        => $originalName,
            'fastpay_filename' => $fastpayFileName,
            'collection_date'  => $collectionDate,
            'uploaded_date'    => now(),
            'notes'            => $notes,
            'status'           => 'pending',
        ]);

        try {

            // ✅ IMPORT EXCEL
            Excel::import(
                new FileDataImport($fileRecord->id),
                $uploadedFile
            );

            // ✅ FETCH DATA FROM DB
            $rows = FileDetail::where('file_id', $fileRecord->id)->get();

            if ($rows->count() === 0) {
                throw new \Exception('No rows imported');
            }

            // ✅ CALCULATE TOTAL
            $totalAmount = $rows->sum('amount');

            // ✅ GENERATE CSV FROM DB — must match FastPay's full import template
            // (same 22-column layout produced by export()). Sending only 6 columns
            // prevented FastPay from mapping the Amount column, so the dashboard
            // Total Amount showed blank.
            $csv = fopen('php://temp', 'r+');

            // Header (FastPay template column names)
            fputcsv($csv, [
                'DD REFERENCE', 'Sort Code', 'Account No', 'Account Name', 'Amount', 'BACS Code',
                'Invoice No (Optional)', 'Title', 'Initial', 'Forename', 'Surname',
                'Salutation 1', 'Salutation 2', 'Address 1', 'Address 2', 'Area', 'Town',
                'Postcode', 'Phone', 'Mobile', 'Email', 'Notes (Optional)',
            ]);

            foreach ($rows as $row) {
                fputcsv($csv, [
                    $row->dd_reference,
                    $row->sort_code,
                    $row->account_number,
                    $row->account_name,
                    number_format($row->amount, 2, '.', ''), // ✅ correct amount
 ctype_digit((string) $row->bacs_code) ? str_pad((string) $row->bacs_code, 2, '0', STR_PAD_LEFT) : $row->bacs_code,
 '',                       // Invoice No (Optional)
                    '',                       // Title
                    $row->initial ?? '',
                    $row->forename ?? '',
                    $row->surname ?? '',
                    '', '', '', '', '', '', '', '', '', '', // Salutations, Address, Area, Town, Postcode, Phone, Mobile, Email
                    $row->error_message ?? '', // Notes (Optional)
                ]);
            }

            rewind($csv);
            $fileContent = stream_get_contents($csv);
            fclose($csv);

            $base64Content = base64_encode($fileContent);

            // ✅ DEBUG LOGS
            Log::info('FASTPAY FINAL CSV', ['csv' => $fileContent]);
            Log::info('FASTPAY TOTAL', ['total' => $totalAmount]);

            // ✅ API PAYLOAD
            $payload = [
                "MessageControl" => ["Version" => "1.0"],
                "Data" => [
                    "ClientID"         => config('services.fastpay.client_id'),
                    "Filename"         => $fastpayFileName,
                    "InternalFilename" => $fastpayFileName,
                    "FileContent"      => $base64Content,
                    "SubmissionDate"   => $submissionDate,
                    "UploadDate"       => $uploadDate,
                    "FileId"           => 0,
                    "Status"           => 0,
                    "TotalAmount"      => (float) $totalAmount,
                    "Notes"            => $notes ?? null,
                    "StatusName"       => "",
                ]
            ];

            // ✅ CALL FASTPAY API
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Bearer-Token' => config('services.fastpay.token'),
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.fastpay.url') . '/api/Files', $payload);

            $responseData = $response->json();

            Log::info('FASTPAY RESPONSE', $responseData);

            if (
                !$response->successful() ||
                ($responseData['MessageControl']['Status'] ?? 'Error') !== 'Success'
            ) {
                throw new \Exception("FastPay upload failed: " . json_encode($responseData));
            }

            // ✅ SAVE FILE (OPTIONAL)
            $storedPath = $uploadedFile->store('uploads/fastpay/' . now()->format('Y/m'), 'local');

            // ✅ Capture FastPay's returned FileId so the status-sync job can
            // poll api/Transactions?FileId=... (Strategy A). Without this the
            // sync could never reliably match the file and status stayed "processing".
            $fastpayFileId = data_get($responseData, 'Data.FileId')
                ?? data_get($responseData, 'Data.Id')
                ?? data_get($responseData, 'Data.FileID');

            $fileRecord->update([
                'file_path'        => $storedPath,
                'total_amount'     => $totalAmount,
                'fastpay_response' => json_encode($responseData),
                'fastpay_file_id'  => $fastpayFileId ?: null,
                'status'           => 'uploaded',
            ]);

            return redirect()->route('files.details')
                ->with('success', "File processed: {$rows->count()} records, £" . number_format($totalAmount, 2));

        } catch (\Exception $e) {

            FileDetail::where('file_id', $fileRecord->id)->delete();

            $fileRecord->update([
                'status' => 'failed',
                'fastpay_response' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

  public function export($id)
{
    $file = File::findOrFail($id);

    // ✅ File name format: DDPULTD_YYYYMMDD.xlsx
    $fileName = 'DDPULTD_' . \Carbon\Carbon::parse($file->collection_date)->format('Ymd') . '.xlsx';

    return Excel::download(
        new class($file->id) implements FromCollection, WithHeadings {

            private $fileId;

            public function __construct($fileId)
            {
                $this->fileId = $fileId;
            }

            public function collection()
            {
                return FileDetail::where('file_id', $this->fileId)
                    ->get()
                    ->map(function ($row) {
                        return [
                            $row->dd_reference ?? '',
                            $row->sort_code ?? '',
                            $row->account_number ?? '',
                            $row->account_name ?? '',
                            $row->amount ?? 0,
                            $row->bacs_code ?? '',
                            '', // Invoice No (Optional)
                            '', // Title
                            $row->initial ?? '',
                            $row->forename ?? '',
                            $row->surname ?? '',
                            '', // Salutation 1
                            '', // Salutation 2
                            '', // Address 1
                            '', // Address 2
                            '', // Area
                            '', // Town
                            '', // Postcode
                            '', // Phone
                            '', // Mobile
                            '', // Email
                            $row->error_message ?? '',
                        ];
                    });
            }

            public function headings(): array
            {
                return [
                    'DD REFERENCE',
                    'Sort Code',
                    'Account No',
                    'Account Name',
                    'Amount',
                    'BACS Code',
                    'Invoice No (Optional)',
                    'Title',
                    'Initial',
                    'Forename',
                    'Surname',
                    'Salutation 1',
                    'Salutation 2',
                    'Address 1',
                    'Address 2',
                    'Area',
                    'Town',
                    'Postcode',
                    'Phone',
                    'Mobile',
                    'Email',
                    'Notes (Optional)',
                ];
            }

        },
        $fileName
    );
}

    // NOTE: status syncing lives in the `sync:fastpay-status` command, which uses
    // FastPay's remittance report. FastPay's GET /api/Transactions endpoint is
    // broken (HTTP 500), so the old per-file sync method was removed.

    public function generatePDF()
    {
        $data = [
            'title' => 'My PDF',
            'date' => date('d-m-Y')
        ];

        $pdf = PDF::loadView('pdf.simple_pdf', $data);

        return $pdf->download('sample.pdf');
    }

  
}