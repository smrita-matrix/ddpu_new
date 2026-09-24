<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Signupform;
use App\Models\MembershipApplicationform;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class SignupController extends Controller
{
    public function index($id)
{
    $app = MembershipApplicationform::where('user_id', $id)->latest()->first();

    $step1Data = [];
    $step2Data = [];

    if ($app && $app->step1) {
        $step1Data = is_array($app->step1)
            ? $app->step1
            : json_decode($app->step1, true);
    }

    if ($app && $app->step2) {
        $step2Data = is_array($app->step2)
            ? $app->step2
            : json_decode($app->step2, true);
    }
// dd($step2Data);
    return view('frontend.signup', compact('id', 'step1Data', 'step2Data'));
}

    // STEP 1 SAVE
    public function saveStep1(Request $request)
    {
       $record = MembershipApplicationform::where('user_id', $request->user_id)->first(); 
      
       if (!$record) { return response()->json(['error' => 'User not found'], 404); }

        $stepData = $request->step1_data;

        // PHYSICAL FILE UPLOAD
        if ($request->hasFile('mandate_file')) {

            $file = $request->file('mandate_file');
            $fileName = 'direct_debit_' . time() . '.' . $file->getClientOriginalExtension();

            $path = public_path('direct-debit');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $file->move($path, $fileName);
            $stepData['file_name'] = $fileName;
        }

        $record->step1_signup = $stepData;
        $record->final_submit_signup = 0;
        $record->save();

        return response()->json(['success' => true]);
    }

  

public function getSignupProgress(Request $request)
{
    $app = MembershipApplicationform::where('user_id', $request->user_id)->latest()->first();

    if (!$app) {
        return response()->json([
            'step' => 1,
            'data' => null
        ]);
    }

    // ðŸ”¥ IMPORTANT LOGIC
    if (!empty($app->step1_signup)) {
        return response()->json([
            'step' => 2,
            'data' => [
                'step1' => is_array($app->step1_signup)
                    ? $app->step1_signup
                    : json_decode($app->step1_signup, true)
            ]
        ]);
    }

    return response()->json([
        'step' => 1,
        'data' => null
    ]);
}
 




public function finalSubmit(Request $request)
{
    \Log::info('Final submit called', ['user_id' => $request->user_id]);

    $record = MembershipApplicationform::where('user_id', $request->user_id)->first();
/* =========================================================
    SAVE UPDATED ADDRESS (IF USER EDITED)
=========================================================*/
if ($request->has('updated_address') && 
    !empty($request->updated_address['address_line_1'])) {

    $record->updated_address = json_encode($request->updated_address);
    $record->save();

    \Log::info('Updated address saved', [
        'user_id' => $request->user_id,
        'updated_address' => $request->updated_address
    ]);
}
    // ❌ Step 1 not completed
    if (!$record || !$record->step1_signup) {
        \Log::warning('Step 1 not completed', ['user_id' => $request->user_id]);
        return response()->json(['error' => 'Step 1 not completed'], 422);
    }

    // 🔴 Prevent duplicate submission
    if ($record->final_submit_signup == 1) {
        \Log::info('Already submitted', ['user_id' => $request->user_id, 'dd_reference' => $record->dd_reference]);
        return response()->json([
            'already_submitted' => true,
            'dd_reference' => $record->dd_reference
        ]);
    }

    /* =========================================================
        GENERATE DD REFERENCE (SAFE)
    ==========================================================*/
    DB::beginTransaction();
    try {
    \Log::info('Generating DD Reference', ['user_id' => $request->user_id]);

    // 🔥 Set your starting base number here
    $baseNumber = 68803;

    // Take the HIGHEST DDPU###### number across ALL rows (not just the latest
    // row). Imported/migrated members may carry lower or non-DDPU references
    // (e.g. DDPU000093, DDPU048486, MEDIC-001238); ordering by id could pick one
    // of those and reset the sequence onto an already-used number. Scanning for
    // the max keeps new sign-ups strictly increasing and collision-free.
    $lastNumber = MembershipApplicationform::whereNotNull('dd_reference')
        ->lockForUpdate()
        ->pluck('dd_reference')
        ->map(function ($ref) {
            return preg_match('/^DDPU(\d+)$/', (string) $ref, $m) ? (int) $m[1] : 0;
        })
        ->max() ?? 0;

    \Log::info('Highest DD Reference number fetched', ['lastNumber' => $lastNumber]);

    // 👉 Ensure it never goes below your base
    if ($lastNumber < $baseNumber) {
        $lastNumber = $baseNumber;
    }

    $nextNumber  = $lastNumber + 1;
 
$ddReference = 'DDPU' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT); 
    \Log::info('Next DD Reference generated', ['ddReference' => $ddReference]);
 
    // Save submission
    $record->final_submit_signup = 1;
    $record->submitted_at = now();
    $record->dd_reference = $ddReference;
    $record->save();
 
    \Log::info('Record saved successfully', [
        'user_id' => $request->user_id,
        'ddReference' => $ddReference
    ]);
 
    DB::commit();
 
} catch (\Exception $e) {
    DB::rollBack();
 
    \Log::error('Final submit DD generation failed', [
        'user_id' => $request->user_id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
 
    return response()->json([
        'error' => 'Failed to generate DD Reference: ' . $e->getMessage()
    ], 500);
}
    /* =========================================================
        DECODE ALL STEP DATA
    ==========================================================*/
    \Log::info('Decoding step data', ['user_id' => $request->user_id]);

    $step1_signup = is_array($record->step1_signup) ? $record->step1_signup : (json_decode($record->step1_signup, true) ?: []);
$step1_personal = is_array($record->step1) ? $record->step1 : (json_decode($record->step1, true) ?: []);
$step2 = is_array($record->step2) ? $record->step2 : (json_decode($record->step2, true) ?: []);
$step3 = is_array($record->step3) ? $record->step3 : (json_decode($record->step3, true) ?: []);
$step4 = is_array($record->step4) ? $record->step4 : (json_decode($record->step4, true) ?: []);
$step5 = is_array($record->step5) ? $record->step5 : (json_decode($record->step5, true) ?: []);
$step6 = is_array($record->step6) ? $record->step6 : (json_decode($record->step6, true) ?: []);
$step7 = is_array($record->step7) ? $record->step7 : (json_decode($record->step7, true) ?: []);

    /* =========================================================
        USER NAME & EMAIL
    ==========================================================*/
    $userName = trim(implode(' ', array_filter([
        $step1_personal['title'] ?? '',
        $step1_personal['first_name'] ?? '',
        $step1_personal['middle_name'] ?? '',
        $step1_personal['last_name'] ?? ''
    ])));
    $userName = $userName ?: 'User';
    $userEmail = $step2['primary_email'] ?? null;

    \Log::info('User info', ['userName' => $userName, 'userEmail' => $userEmail]);

    /* =========================================================
        STEP 7 ADMIN MAIL (optional, safe)
   /* =========================================================
   STEP 7 ADMIN MAIL (optional, safe)
=========================================================*/
try {

    if (!empty($step7) && (
        !empty($step7['last_appraisal_date']) ||
        !empty($step7['revalidation_date']) ||
        !empty($step7['last_db_name']) ||
        !empty($step7['last_appraisal_file'])
    )) {

        \Log::info('Sending Step7 admin mail', ['user_id' => $request->user_id]);

        $mailData = [
            'name' => $userName,
            'ddReference' => $ddReference,

            // Step data
            'step1' => $step1_personal,
            'step2' => $step2,
            'step3' => $step3,
            'step4' => $step4,
            'step5' => $step5,
            'step6' => $step6,

            // Step 7
            'last_appraisal_date' => $step7['last_appraisal_date'] ?? null,
            'revalidation_date' => $step7['revalidation_date'] ?? null,
            'last_db_name' => $step7['last_db_name'] ?? null,
            'file' => isset($step7['last_appraisal_file'])
                ? asset($step7['last_appraisal_file'])
                : null,
        ];

        // Generate PDF with all steps
        $pdf = Pdf::loadView('pdf.designated-body', $mailData);

        Mail::send('emails.step7-admin-alert', $mailData, function ($message) use ($userName, $pdf) {

            // $message->to('smrita@matrixbricks.com')
            $message->to('admin@ddpu.co.uk')
                ->subject('Designated Body - ' . $userName)

                // Attach PDF
                ->attachData(
                    $pdf->output(),
                    'Designated-Body-Details.pdf',
                    [
                        'mime' => 'application/pdf',
                    ]
                );
        });
    }

} catch (\Exception $e) {
    \Log::error('Step7 admin mail failed', ['error' => $e->getMessage()]);
}

    /* =========================================================
        GENERATE PDF (safe)
    ==========================================================*/
    try {
        \Log::info('Generating PDF', ['user_id' => $request->user_id]);

        $pdf = Pdf::loadView('pdf.direct-debit', [
            'step11' => $step1_personal,
            'step2' => $step2,
            'step3' => $step3,
            'step4' => $step4,
            'step5' => $step5,
            'step6' => $step6,
            'step7' => $step7,
            'type' => $step1_signup['type'] ?? null,
            'accountHolder' => $step1_signup['account_holder'] ?? null,
            'accountNumber' => $step1_signup['account_number'] ?? null,
            'sortCode' => $step1_signup['sort_code'] ?? null,
            'bankName' => $step1_signup['bank_name'] ?? null,
            'branchName' => $step1_signup['branch_name'] ?? null,
            'companyName' => $step1_signup['company_name'] ?? null,
            'serviceNumber' => $step1_signup['service_number'] ?? null,
            'paymentplan' => $step1_signup['payment_plan'] ?? null,
            'ddReference' => $ddReference,
            'date' => now()->format('d F, Y'),
        ]);
        $pdfFileName = 'Direct_Debit_' . $ddReference . '.pdf';

        \Log::info('PDF generated', ['pdfFileName' => $pdfFileName]);
    } catch (\Exception $e) {
        \Log::error('PDF generation failed', ['error' => $e->getMessage()]);
        return response()->json([
            'error' => 'PDF generation failed: ' . $e->getMessage()
        ], 500);
    }

    /* =========================================================
        EMAIL DATA
    ==========================================================*/
    $emailData = [
        'name' => $userName,
        'type' => $step1_signup['type'] ?? null,
        'serviceNumber' => $step1_signup['service_number'] ?? null,
        'paymentplan' => $step1_signup['payment_plan'] ?? null,
        'ddReference' => $ddReference,
        'companyName' => $step1_signup['company_name'] ?? null,
        'mandateUrl' => isset($step1_signup['mandate_file'])
            ? asset('storage/' . $step1_signup['mandate_file'])
            : null,
    ];
    $adminEmail = 'admin@ddpu.co.uk';

    /* =========================================================
        SEND EMAIL TO USER
    ==========================================================*/
  /* =========================================================
    SEND EMAIL TO USER
==========================================================*/
try {
    if ($userEmail) {
        \Log::info('Sending email to user', ['userEmail' => $userEmail]);

        Mail::send('emails.direct-debit', $emailData, function ($message) use ($userEmail, $pdf, $pdfFileName, $adminEmail) {

            $message->to($userEmail)
                ->cc([
                    $adminEmail,
                    // 'smrita@matrixbricks.com',
                    // 'shweta@matrixbricks.com',
                ])
                ->subject('Direct Debit Instruction - DDPU')
                ->attachData($pdf->output(), $pdfFileName);
        });
    }
} catch (\Exception $e) {
    \Log::error('User email send failed', ['error' => $e->getMessage()]);
}
    /* =========================================================
        SEND EMAIL TO ADMIN
    ==========================================================*/
    try {
        \Log::info('Sending email to admin', ['adminEmail' => $adminEmail]);
        Mail::send('emails.direct-debit-admin', $emailData, function ($message) use ($adminEmail, $pdf, $pdfFileName) {
            $message->to($adminEmail)
                ->subject('New Direct Debit Application Submitted - ' . $pdfFileName)
                ->attachData($pdf->output(), $pdfFileName);
        });
    } catch (\Exception $e) {
        \Log::error('Admin email send failed', ['error' => $e->getMessage()]);
    }

    /* =========================================================
        FINAL RESPONSE
    ==========================================================*/
    \Log::info('Final submit completed successfully', ['user_id' => $request->user_id, 'ddReference' => $ddReference]);

    return response()->json([
        'success' => true,
        'dd_reference' => $ddReference
    ]);
}

public function generatePdf($userId)
{
    
    $record = MembershipApplicationform::find($userId);

    if (!$record || !$record->step1_signup) {
        abort(404, "Application data not found.");
    }

    // Decode JSON if step1_data is stored as JSON
    $data = is_array($record->step1_signup) ? $record->step1_signup : json_decode($record->step1_signup, true);
    if (!$data) {
        abort(404, "Step1 data is empty or invalid.");
    }

    $serviceNumber = $data['service_number'] ?? 'UNKNOWN';

    $pdf = Pdf::loadView('pdf.direct-debit', [
        'accountHolder' => $data['account_holder'] ?? null,
        'accountNumber' => $data['account_number'] ?? null,
        'sortCode'      => $data['sort_code'] ?? null,
        'bankName'      => $data['bank_name'] ?? null,
        'branchName'    => $data['branch_name'] ?? null,
        'companyName'   => $data['company_name'] ?? null,
        'serviceNumber' => $serviceNumber,
        'date'          => now()->format('d F, Y')
    ]);

$safeServiceNumber = str_replace(['/', '\\'], '_', $serviceNumber);
return $pdf->download("Direct_Debit_Instruction_{$safeServiceNumber}.pdf");
}


 public function direct_debit($id)
{
    $app = MembershipApplicationform::where('user_id', $id)->latest()->first();

    $step1Data = [];
    $step2Data = [];

    if ($app && $app->step1) {
        $step1Data = is_array($app->step1)
            ? $app->step1
            : json_decode($app->step1, true);
    }

    if ($app && $app->step2) {
        $step2Data = is_array($app->step2)
            ? $app->step2
            : json_decode($app->step2, true);
    }

    $pdf = PDF::loadView('frontend.direct_debit_form', compact('step1Data', 'step2Data'));

    return $pdf->stream('direct_debit_form.pdf'); // Open in browser
}
public function thankyou()
{

    return view('frontend.thankyou');
}
}
