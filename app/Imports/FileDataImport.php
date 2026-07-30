<?php

namespace App\Imports;

use App\Models\FileDetail;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class FileDataImport implements 
    ToModel, 
    WithHeadingRow, 
    WithBatchInserts, 
    WithChunkReading, 
    WithCustomCsvSettings
{
    protected $fileId;
    protected $logged = false;

    public function __construct($fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * CSV Settings
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'input_encoding' => 'UTF-8',
        ];
    }

    /**
     * Main Row Mapping
     */
    public function model(array $row)
    {
        // Log first row for debugging
        if (!$this->logged) {
            Log::info('🔍 Sample Excel Row Parsed:', $row);
            $this->logged = true;
        }

        // Skip empty rows
        if (empty($row['dd_reference']) && empty($row['account_no'])) {
            return null;
        }

        try {
            // ✅ CLEAN & SAFE AMOUNT
            $amount = $this->cleanAmount($row['amount'] ?? 0);

            return new FileDetail([
                'file_id'        => $this->fileId,
                'dd_reference'   => $row['dd_reference'] ?? null,
                'sort_code'      => $this->cleanText($row['sort_code'] ?? null),
                'account_number' => $this->cleanText($row['account_no'] ?? null),
                'account_name'   => $this->cleanName($row['account_name'] ?? null),

                // ✅ FIXED AMOUNT
                'amount'         => $amount,

                'bacs_code'      => $this->cleanBacs($row['bacs_code'] ?? null),
                'invoice_no'     => $row['invoice_no_optional'] ?? $row['invoice_no'] ?? null,
                'title'          => $row['title'] ?? null,
                'initial'        => $row['initial'] ?? null,
                'forename'       => $row['forename'] ?? null,
                'surname'        => $row['surname'] ?? null,
                'salutation_1'   => $row['salutation_1'] ?? null,
                'salutation_2'   => $row['salutation_2'] ?? null,
                'address_1'      => $row['address_1'] ?? null,
                'address_2'      => $row['address_2'] ?? null,
                'area'           => $row['area'] ?? null,
                'town'           => $row['town'] ?? null,
                'postcode'       => $row['postcode'] ?? null,
                'phone'          => $row['phone'] ?? null,
                'mobile'         => $row['mobile'] ?? null,
                'email'          => $row['email'] ?? null,
                'notes'          => $row['notes_optional'] ?? $row['notes'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Row import failed', [
                'file_id'  => $this->fileId,
                'error'    => $e->getMessage(),
                'row_data' => $row,
            ]);
            return null;
        }
    }

    /**
     * ✅ CLEAN AMOUNT (CRITICAL FIX)
     */
    private function cleanAmount($value)
    {
        if (is_null($value) || $value === '') {
            return 0;
        }

        // Convert to string
        $value = (string) $value;

        // Remove currency symbols, commas, spaces
        $value = str_replace(['£', ',', ' '], '', $value);

        // Remove hidden / invalid characters
        $value = preg_replace('/[^\d.\-]/', '', $value);

        // Validate numeric
        if (!is_numeric($value)) {
            Log::warning('Invalid amount detected', ['value' => $value]);
            return 0;
        }

        return round((float) $value, 2);
    }

    /**
     * ✅ CLEAN TEXT (avoid DB issues like sort_code error)
     */
    private function cleanText($value)
    {
        if (!$value) return null;

        return trim(preg_replace('/[^A-Za-z0-9]/', '', $value));
    }

    /**
     * ✅ CLEAN ACCOUNT NAME — keep spaces and BACS-allowed punctuation
     * (& . / - and apostrophe). Only strips disallowed characters so names
     * like "Dr. Junaid Tonse" or "Ghafoor & Zahid" survive intact.
     */
    private function cleanName($value)
    {
        if (!$value) return null;

        $value = preg_replace('/[^A-Za-z0-9 .&\/\'\-]/', '', $value);
        return trim(preg_replace('/\s+/', ' ', $value)); // collapse repeated spaces
    }
private function cleanBacs($value)
    {
        $v = strtoupper(trim((string) $value));
        if ($v === '') return null;

        return ctype_digit($v) ? str_pad($v, 2, '0', STR_PAD_LEFT) : $v;
    }
    /**
     * Performance Optimization
     */
    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }
}