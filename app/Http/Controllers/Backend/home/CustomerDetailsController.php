<?php

namespace App\Http\Controllers\Backend\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MembershipApplicationform;
use App\Models\FileDetail;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CustomerDetailsController extends Controller
{
    // =========================================================
    // HELPERS
    // =========================================================

    private function getMemberPhase(MembershipApplicationform $member): string
    {
        if (!$member->start_date) return 'onboarding';
        return Carbon::parse($member->start_date)->addYear()->isPast() ? 'renewal' : 'onboarding';
    }

    private function getExpiry(MembershipApplicationform $member): ?Carbon
    {
        return $member->start_date ? Carbon::parse($member->start_date)->addYear() : null;
    }

    private function isPriceInvalid(MembershipApplicationform $member): bool
    {
        return is_null($member->price) || (float)$member->price == 0;
    }

    private function decode($value): array
    {
        if (is_array($value))  return $value;
        if (is_string($value)) return json_decode($value, true) ?? [];
        return [];
    }

    private function resolveName(MembershipApplicationform $member): string
    {
        $step1 = $this->decode($member->step1);
        return trim(implode(' ', array_filter([
            data_get($step1, 'title'),
            data_get($step1, 'first_name'),
            data_get($step1, 'middle_name'),
            data_get($step1, 'last_name'),
        ])));
    }

    private function extractMemberData(MembershipApplicationform $member): array
    {
        $step1   = is_array($member->step1)        ? $member->step1        : json_decode($member->step1, true);
        $step2   = is_array($member->step2)        ? $member->step2        : json_decode($member->step2, true);
        $step7   = is_array($member->step7)        ? $member->step7        : json_decode($member->step7, true);
        $payment = is_array($member->step1_signup) ? $member->step1_signup : json_decode($member->step1_signup, true);
        $fullName = trim(implode(' ', array_filter([
            data_get($step1, 'title'),
            data_get($step1, 'last_name'),
        ])));
        $email = data_get($step2, 'primary_email') ?? data_get($step1, 'primary_email');
        return [$step1, $step2, $step7, $payment, $fullName, $email];
    }

    /**
     * =====================================================================
     * FIRST COLLECTION LOGIC  (single source of truth)
     * =====================================================================
     * Anchored to the START DATE, NOT to the system/email date.
     * The email date is ONLY used as a cut-off test (Logic 2 only).
     *
     * Step 1 — Classify by START DATE day:
     *   - day 25..10  -> Logic 2
     *   - day 11..24  -> Logic 3
     *
     * Step 2 — "First 10th that follows the start date":
     *   - start day <= 10 -> 10th of SAME month
     *   - start day  > 10 -> 10th of NEXT month
     *
     * Step 3 — Logic 2 only: test welcome/confirmation email date against
     *          the 25th of the month BEFORE that 10th (DD processing cut-off):
     *            - email BEFORE the 25th     -> first 10th,  1 instalment
     *            - email ON/AFTER the 25th   -> second 10th, 2 instalments
     *
     * Step 4 — Logic 3: ALWAYS first 10th, ALWAYS 1 instalment.
     *          (The 25th rule has NO application under Logic 3.)
     *
     * Weekend -> roll forward to the next working day.
     *
     * @return array{date: Carbon, installments: int}
     * =====================================================================
     */
    private function firstCollection(Carbon $start, Carbon $emailDate, string $context = 'unspecified'): array
    {
        $startDay = $start->day;

        // "First 10th that follows the start date"
        $firstTenth = ($startDay <= 10)
            ? $start->copy()->setDay(10)
            : $start->copy()->addMonthNoOverflow()->setDay(10);

        $isLogic2     = ($startDay >= 25 || $startDay <= 10); // 25..10 window
        $collection   = $firstTenth->copy();
        $installments = 1;
        $logicApplied = $isLogic2 ? 'Logic 2 (25th-10th)' : 'Logic 3 (11th-24th)';
        $cutoffStr    = null;
        $emailVsCutoff = 'n/a (Logic 3)';

        if ($isLogic2) {
            // 25th of the month BEFORE the target 10th = processing cut-off
            $cutoff    = $firstTenth->copy()->subMonthNoOverflow()->setDay(25);
            $cutoffStr = $cutoff->toDateString();
            if ($emailDate->gte($cutoff)) {
                $collection    = $firstTenth->copy()->addMonthNoOverflow()->setDay(10); // second 10th
                $installments  = 2;
                $emailVsCutoff = 'ON/AFTER cutoff -> second 10th, 2 instalments';
            } else {
                $emailVsCutoff = 'BEFORE cutoff -> first 10th, 1 instalment';
            }
        }
        // Logic 3 (day 11..24): stays on first 10th, 1 instalment.

        // Weekend adjustment -> roll forward to Monday
        $preWeekend = $collection->toDateString();
        while ($collection->isWeekend()) {
            $collection->addDay();
        }

        // ---- TRACE LOG: full decision breakdown ----
        Log::info('[firstCollection] decision trace', [
            'context'                   => $context,
            'start_date'                => $start->toDateString(),
            'start_day'                 => $startDay,
            'email_date'                => $emailDate->toDateString(),
            'logic_applied'             => $logicApplied,
            'first_tenth'               => $firstTenth->toDateString(),
            'cutoff_25th'               => $cutoffStr,
            'email_vs_cutoff'           => $emailVsCutoff,
            'collection_before_weekend' => $preWeekend,
            'weekend_rolled'            => ($preWeekend !== $collection->toDateString()),
            'final_collection'          => $collection->toDateString(),
            'installments'              => $installments,
        ]);
        // ---------------------------------------------

        return ['date' => $collection, 'installments' => $installments];
    }

    /**
     * =====================================================================
     * ⚠️ TEMPORARY TEST MODE — PROCESSING / EMAIL DATE HARDCODED TO THE 25th
     * ---------------------------------------------------------------------
     * Returns the "today" value used as the email/processing date for the
     * first-collection cut-off test. While in test mode this forces today's
     * date to the 25th of the CURRENT month so the client can preview the
     * 2-payment (second 10th) case.
     *
     * 🔴 BEFORE GO-LIVE: change the body back to `return Carbon::now();`
     * =====================================================================
     */
    private function processingDate(): Carbon
    {
        // TEMPORARY: pretend today is the 25th of the current month.
        return Carbon::now()->setDay(25);

        // LIVE (revert to this before go-live):
        // return Carbon::now();
    }

    // =========================================================
    // AUTO-INIT RENEWAL
    // Overdue retired — everything collapses to 'renewal_due'.
    // =========================================================

    private function autoInitRenewal(MembershipApplicationform $member): void
    {
        if ($this->getMemberPhase($member) !== 'renewal') return;
        if ($member->renewal_status === 'due') return;

        $today = Carbon::today(); $changed = false;

        if (!$member->renewal_date) {
            $expiry = $this->getExpiry($member);
            if ($expiry) { $member->renewal_date = $expiry->toDateString(); $changed = true; }
        }

        if ($member->renewal_date) {
            $daysLeft = $today->diffInDays(Carbon::parse($member->renewal_date), false);
            if ($daysLeft <= 21) {
                if ($member->renewal_status !== 'renewal_due') {
                    $member->renewal_status = 'renewal_due';
                    $changed = true;
                }
            }
        }

        // Normalise any legacy 'overdue' rows
        if ($member->renewal_status === 'overdue') {
            $member->renewal_status = 'renewal_due';
            $changed = true;
        }

        if ($changed) $member->save();
    }

    // =========================================================
    // INDEX
    // =========================================================

    public function index()
    {
        $memberships = MembershipApplicationform::where('final_submit_signup', 1)->orderByDesc('id')->get();
        foreach ($memberships as $member) $this->autoInitRenewal($member);
        return view('backend.customer-details.index', compact('memberships'));
    }

    // =========================================================
    // UPDATE METHODS
    // =========================================================

    public function updatePayment(Request $request)
    {
        $record  = MembershipApplicationform::findOrFail($request->id);
        $payment = is_array($record->step1_signup) ? $record->step1_signup : json_decode($record->step1_signup, true);
        if (!$payment) $payment = [];
        $payment[$request->field] = $request->value;
        $record->step1_signup = json_encode($payment);
        $record->save();
        return response()->json(['success' => true]);
    }

    public function updateStartDate(Request $request)
    {
        $request->validate([
            'id'         => 'required|exists:membership_applicationforms,id',
            'start_date' => 'nullable|date',
        ]);

        $membership = MembershipApplicationform::findOrFail($request->id);
        $membership->start_date = $request->start_date ? Carbon::parse($request->start_date) : null;

        // End date = start + 1 year - 1 day
        if ($membership->start_date && !$membership->end_date) {
            $membership->end_date = Carbon::parse($membership->start_date)->copy()->addYear()->subDay();
        }

        $membership->mail_trigger_date = null;
        $membership->mail_trigger_type = null;
        $membership->save();

        return response()->json([
            'success'    => true,
            'message'    => 'Start date updated',
            'start_date' => $membership->start_date?->format('Y-m-d'),
            'end_date'   => $membership->end_date?->format('Y-m-d'),
            'phase'      => $this->getMemberPhase($membership),
        ]);
    }

    public function updateEndDate(Request $request)
    {
        $request->validate([
            'id'       => 'required|exists:membership_applicationforms,id',
            'end_date' => 'nullable|date',
        ]);
        $membership = MembershipApplicationform::findOrFail($request->id);
        $membership->end_date = $request->end_date ? Carbon::parse($request->end_date) : null;
        $membership->save();
        return response()->json([
            'success'  => true,
            'message'  => 'End date updated',
            'end_date' => $membership->end_date?->format('Y-m-d'),
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:membership_applicationforms,id',
            'status' => 'required|in:active,inactive',
        ]);

        $membership = MembershipApplicationform::findOrFail($request->id);

        if ($request->status === 'active') {
            if ($membership->mail_sent_at && $membership->status === 'active') {
                return response()->json(['success' => false, 'message' => 'Already active. Active status can only be set once.']);
            }
            if ($this->getMemberPhase($membership) === 'renewal') {
                return response()->json(['success' => false, 'message' => 'Cannot set Active during renewal phase. Use the renewal workflow.']);
            }
            // End date = start + 1 year - 1 day
            if ($membership->start_date && !$membership->end_date) {
                $membership->end_date = Carbon::parse($membership->start_date)->copy()->addYear()->subDay();
            }
            $membership->status      = 'active';
            $membership->inactive_at = null;
            $membership->save();
            return response()->json(['success' => true, 'message' => 'Status updated to active', 'mail_auto_sent' => false]);
        }

        // Inactive: mark record, no mail of any kind.
        $membership->mail_trigger_date = null;
        $membership->mail_trigger_type = null;
        $membership->mail_sent_at      = null;
        $membership->inactive_at       = now();
        $membership->status            = 'inactive';
        $membership->save();

        return response()->json([
            'success'        => true,
            'message'        => 'Status set to Inactive.',
            'mail_auto_sent' => false,
            'inactive_at'    => $membership->inactive_at->format('d M Y'),
        ]);
    }

    public function updateRenewal(Request $request)
    {
        $request->validate(['id' => 'required|exists:membership_applicationforms,id']);

        $membership = MembershipApplicationform::findOrFail($request->id);
        $today = now();

        if ($request->renewal_date) $membership->renewal_date = $request->renewal_date;

        // Legacy 'overdue' → 'renewal_due'
        $requestedStatus = $request->renewal_status;
        if ($requestedStatus === 'overdue') {
            $requestedStatus = 'renewal_due';
        }

        if ($requestedStatus === 'due') {
            // Renewal Completed — set status, NO mail is triggered here or elsewhere.
            $expiry = $this->getExpiry($membership);
            $membership->renewal_date              = $expiry ? $expiry->copy()->addYear() : $today->copy()->addYear();
            $membership->renewal_status            = 'due';
            $membership->renewal_mail_sent_at      = null;
            $membership->renewal_mail_trigger_date = null;
            $membership->renewal_mail_trigger_type = null;
            $membership->renewal_reminder_sent_at  = null;
        } elseif ($requestedStatus === 'renewal_due') {
            $membership->renewal_status = 'renewal_due';
        } else {
            if ($membership->renewal_date) {
                $days = $today->diffInDays(Carbon::parse($membership->renewal_date), false);
                if ($days <= 21) $membership->renewal_status = 'renewal_due';
            }
        }

        $membership->save();

        return response()->json([
            'success'        => true,
            'message'        => 'Renewal updated',
            'renewal_status' => $membership->renewal_status,
            'renewal_date'   => $membership->renewal_date ? Carbon::parse($membership->renewal_date)->format('Y-m-d') : null,
        ]);
    }

    public function updatePriceRenewal(Request $request)
    {
        $request->validate([
            'id'           => 'required|exists:membership_applicationforms,id',
            'price'        => 'nullable|numeric|min:0',
            'renewal_date' => 'nullable|date',
        ]);
        $membership = MembershipApplicationform::findOrFail($request->id);
        $membership->price = $request->price ?? 0;
        if ($request->renewal_date) $membership->renewal_date = Carbon::parse($request->renewal_date);
        $membership->save();
        return response()->json([
            'success'      => true,
            'message'      => 'Updated successfully',
            'price'        => $membership->price,
            'renewal_date' => $membership->renewal_date?->format('Y-m-d'),
        ]);
    }

    // =========================================================
    // MAIL METHODS
    // =========================================================

    public function sendMail(Request $request)
    {
        // Only 'active' is allowed — inactive members receive NO mail.
        $request->validate([
            'id'     => 'required|exists:membership_applicationforms,id',
            'status' => 'required|in:active',
        ]);

        $member = MembershipApplicationform::findOrFail($request->id);

        if ($this->isPriceInvalid($member))
            return response()->json(['success' => false, 'message' => 'Cannot send mail. Price is not set or £0.']);
        if (!$member->start_date)
            return response()->json(['success' => false, 'message' => 'Cannot send mail. Start date is not set.']);
        if ($member->mail_sent_at && $member->status === 'active')
            return response()->json(['success' => false, 'message' => 'Active status can only be set once.']);
        if ($this->getMemberPhase($member) === 'renewal')
            return response()->json(['success' => false, 'message' => 'Active onboarding mail is locked in renewal phase.']);

        $result = $this->dispatchStatusMail($member, 'active');

        if ($result['success']) {
            $member->mail_trigger_date = null;
            $member->mail_trigger_type = null;
            $member->mail_sent_at      = now();
            $member->save();
            return response()->json(['success' => true, 'message' => 'Mail sent successfully']);
        }

        return response()->json(['success' => false, 'message' => $result['message']]);
    }

    public function sendRenewalMail(Request $request)
    {
        // Only 'reminder' is allowed — 'completed' no longer triggers mail.
        $request->validate([
            'id'        => 'required|exists:membership_applicationforms,id',
            'mail_type' => 'required|in:reminder',
        ]);

        $member = MembershipApplicationform::findOrFail($request->id);

        if ($this->isPriceInvalid($member))
            return response()->json(['success' => false, 'message' => 'Cannot send renewal mail. Price is not set or £0.']);

        $result = $this->dispatchRenewalMail($member, 'reminder');

        if ($result['success']) {
            $member->renewal_mail_trigger_date = null;
            $member->renewal_mail_trigger_type = null;
            $member->renewal_mail_sent_at      = now();
            $member->save();
            return response()->json(['success' => true, 'message' => 'Renewal mail sent']);
        }

        return response()->json(['success' => false, 'message' => $result['message']]);
    }

    /**
     * Active onboarding mail.
     *
     *   • Monthly: $member->price stores the MONTHLY amount.
     *     Annual fee = price × 12.
     *
     *   • Yearly : $member->price stores the ANNUAL amount as-is.
     *
     * Collection date + first-instalment count come from firstCollection(),
     * which is anchored to the START DATE (email date is only a cut-off test).
     */
    private function dispatchStatusMail(MembershipApplicationform $member, string $status): array
    {
        [$step1, $step2, $step7, $payment, $fullName, $email] = $this->extractMemberData($member);
        if (!$email) return ['success' => false, 'message' => 'Member email not found'];

        $ccEmail = 'admin@ddpu.co.uk';
        $ccEmail = 'smrita@matrixbricks.com';


        // Inactive never sends mail — defensive guard.
        if ($status !== 'active') {
            return ['success' => false, 'message' => 'Inactive status does not send mail.'];
        }

        try {
            $type = 'status';
            $pdf  = Pdf::loadView('backend.customer-details.pdfstatus',
                compact('member', 'step1', 'step2', 'step7', 'payment', 'type'));
            $pdfContent = $pdf->output();

            $start = $member->start_date ? Carbon::parse($member->start_date) : null;
            // End date = start + 1 year - 1 day
            $end = $start ? $start->copy()->addYear()->subDay() : null;

            $paymentPlan = strtolower(trim(data_get($payment, 'payment_plan', '')));
            $isMonthly   = str_contains($paymentPlan, 'monthly');

            $price = (float) ($member->price ?? 0);

            // ---- Single source of truth for collection date + instalments ----
            // Email date = the welcome/confirmation email is being sent now.
            $nextCollectionDate = null;
            $firstInstallments  = 1;
            $firstDebitAmount   = $price;

            if ($start) {
                // ⚠️ TEMPORARY TEST MODE: processingDate() forces "today" to the 25th.
                $emailDate          = $this->processingDate();
                $first              = $this->firstCollection($start, $emailDate, 'status:member#' . $member->id);
                $nextCollectionDate = $first['date'];
                $firstInstallments  = $isMonthly ? $first['installments'] : 1;
                $firstDebitAmount   = $isMonthly ? $price * $firstInstallments : $price;
            }
            // ------------------------------------------------------------------

            if ($isMonthly) {
                // price = monthly amount. Total = price × 12.
                $monthlyAmount     = number_format($price, 2);
                $annualFee         = number_format($price * 12, 2);
                $totalAnnualAmount = number_format($price * 12, 2);
            } else {
                // price = annual amount.
                $monthlyAmount     = number_format($price, 2);
                $annualFee         = number_format($price, 2);
                $totalAnnualAmount = number_format($price, 2);
            }

            Log::info('[dispatchStatusMail] computed', [
                'member_id'          => $member->id,
                'payment_plan'       => $paymentPlan,
                'is_monthly'         => $isMonthly,
                'price'              => $price,
                'start_date'         => $start?->toDateString(),
                'email_date_used'    => $start ? $this->processingDate()->toDateString() : now()->toDateString(),
                'next_collection'    => $nextCollectionDate?->toDateString(),
                'first_installments' => $firstInstallments,
                'first_debit_amount' => $firstDebitAmount,
            ]);

            Mail::send('backend.customer-details.mail-active', [
                'member'             => $member,
                'name'               => $fullName,
                'type'               => $type,
                'paymentPlan'        => $paymentPlan,
                'start'              => $start,
                'end'                => $end,
                'isMonthly'          => $isMonthly,
                'annualFee'          => $annualFee,
                'monthlyAmount'      => $monthlyAmount,
                'totalAnnualAmount'  => $totalAnnualAmount,
                'firstInstallments'  => $firstInstallments,
                'firstDebitAmount'   => $firstDebitAmount,
                'nextCollectionDate' => $nextCollectionDate,
                'startDate'          => $start ? $start->format('d F Y') : 'N/A',
                'membershipNumber'   => $member->dd_reference ?? 'N/A',
                'price'              => $member->price ?? 0,
            ], function ($msg) use ($email, $fullName, $pdfContent, $member, $ccEmail) {
                $msg->to($email, $fullName)->cc($ccEmail)
                    ->subject('Welcome to DDPU – Your Membership Certificate')
                    ->attachData(
                        $pdfContent,
                        'DDPU_Certificate_' . ($member->dd_reference ?? $member->id) . '.pdf',
                        ['mime' => 'application/pdf']
                    );
            });

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Status Mail Error – member ' . $member->id . ': ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed: ' . $e->getMessage()];
        }
    }

    /**
     * Renewal REMINDER only.
     * (Renewal Completed no longer triggers mail.)
     *
     * Monthly → price = monthly instalment; totalAnnual = price × 12.
     * Yearly  → price = annual fee.
     *
     * Collection date + first-instalment count come from firstCollection(),
     * anchored to the RENEWAL period start date.
     */
    private function dispatchRenewalMail(MembershipApplicationform $member, string $mailType): array
    {
        [$step1, $step2, $step7, $payment, $fullName, $email] = $this->extractMemberData($member);
        if (!$email) return ['success' => false, 'message' => 'Member email not found'];

        $ccEmail = 'admin@ddpu.co.uk';
        $ccEmail = 'smrita@matrixbricks.com';

        // Only reminder is supported now.
        if ($mailType !== 'reminder') {
            return ['success' => false, 'message' => 'This mail type is not supported.'];
        }

        try {
            $expiry = $this->getExpiry($member);

            // Anchor the renewed period on the admin-selected renewal date
            // (same source the certificate now uses, so mail + cert stay aligned)
            $renewalPeriodStart = $member->renewal_date
                ? Carbon::parse($member->renewal_date)
                : ($expiry ? $expiry->copy() : Carbon::now());
            $renewalPeriodEnd = $renewalPeriodStart->copy()->addYear()->subDay();

            $paymentPlan = strtolower(trim(data_get($payment, 'payment_plan', '')));
            $isMonthly   = str_contains($paymentPlan, 'monthly');
            $price       = (float) ($member->price ?? 0);

            // ---- Collection date + instalments for the renewed period ----
            // ⚠️ TEMPORARY TEST MODE: processingDate() forces "today" to the 25th.
            $emailDate          = $this->processingDate();
            $first              = $this->firstCollection($renewalPeriodStart, $emailDate, 'renewal:member#' . $member->id);
            $nextCollectionDate = $first['date'];
            $firstInstallments  = $isMonthly ? $first['installments'] : 1;
            $firstDebitAmount   = $isMonthly ? $price * $firstInstallments : $price;
            // --------------------------------------------------------------

            if ($isMonthly) {
                $monthlyAmount     = number_format($price, 2);
                $totalAnnualAmount = number_format($price * 12, 2);
                $annualFee         = $price * 12;
            } else {
                $monthlyAmount     = number_format($price, 2);
                $totalAnnualAmount = number_format($price, 2);
                $annualFee         = $price;
            }

            $salutationTitle = trim((string) data_get($step1, 'title', ''));
            $surname = trim(data_get($step1, 'last_name', ''));

            $subjectName    = $fullName !== '' ? $fullName : trim($salutationTitle . ' ' . $surname);
            $renewalSubject = 'Annual Membership Renewal - ' . ($subjectName !== '' ? $subjectName : 'Member');

            Log::info('[dispatchRenewalMail] computed', [
                'member_id'           => $member->id,
                'payment_plan'        => $paymentPlan,
                'is_monthly'          => $isMonthly,
                'price'               => $price,
                'renewal_period_start'=> $renewalPeriodStart->toDateString(),
                'renewal_period_end'  => $renewalPeriodEnd->toDateString(),
                'email_date_used'     => $emailDate->toDateString(),
                'next_collection'     => $nextCollectionDate?->toDateString(),
                'first_installments'  => $firstInstallments,
                'first_debit_amount'  => $firstDebitAmount,
            ]);

            // ---- Generate renewal certificate PDF ----
            $type = 'renewal';

            $pdf = Pdf::loadView(
                'backend.customer-details.pdfstatus',
                compact('member', 'step1', 'step2', 'step7', 'payment', 'type')
            );
            $pdfContent = $pdf->output();
            // ------------------------------------------

            Mail::send('backend.customer-details.mail-renewal-reminder', [
                'member'             => $member,
                'name'               => $fullName,
                'salutationTitle'    => $salutationTitle,
                'surname'            => $surname,
                'type'               => 'reminder',
                'paymentPlan'        => $paymentPlan,
                'start'              => $renewalPeriodStart,
                'end'                => $renewalPeriodEnd,
                'isMonthly'          => $isMonthly,
                'annualFee'          => number_format($annualFee, 2),
                'monthlyAmount'      => $monthlyAmount,
                'totalAnnualAmount'  => $totalAnnualAmount,
                'firstInstallments'  => $firstInstallments,
                'firstDebitAmount'   => $firstDebitAmount,
                'nextCollectionDate' => $nextCollectionDate,
                'startDate'          => $renewalPeriodStart->format('d F Y'),
                'renewalDate'        => $member->renewal_date
                    ? Carbon::parse($member->renewal_date)->format('d F Y')
                    : $renewalPeriodStart->format('d F Y'),
                'membershipNumber'   => $member->dd_reference ?? 'N/A',
                'price'              => $member->price ?? 0,
            ], function ($msg) use ($email, $fullName, $ccEmail, $renewalSubject, $pdfContent, $member) {
                $msg->to($email, $fullName)->cc($ccEmail)
                    ->subject($renewalSubject)
                    ->attachData(
                        $pdfContent,
                        'DDPU_Renewal_Certificate_' . ($member->dd_reference ?? $member->id) . '.pdf',
                        ['mime' => 'application/pdf']
                    );
            });

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Renewal Mail Error – member ' . $member->id . ': ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed: ' . $e->getMessage()];
        }
    }

    // =========================================================
    // AUTO TRIGGER (cron)
    // Onboarding auto-mail kept; inactive/renewal-completed never mail.
    // =========================================================

    public function autoTriggerMails()
    {
        $today = Carbon::today(); $triggered = 0;

        MembershipApplicationform::where('final_submit_signup', 1)
            ->whereNotNull('start_date')
            ->where('renewal_status', '!=', 'due')
            ->get()->each(function ($m) { $this->autoInitRenewal($m); });

        MembershipApplicationform::where('final_submit_signup', 1)
            ->where('status', 'active')
            ->whereNull('mail_sent_at')
            ->whereNotNull('start_date')
            ->whereBetween('start_date', [$today, Carbon::today()->addDays(3)])
            ->get()->each(function ($member) use (&$triggered) {
                if ($this->isPriceInvalid($member)) return;
                $result = $this->dispatchStatusMail($member, 'active');
                if ($result['success']) { $member->update(['mail_sent_at' => now()]); $triggered++; }
            });

        MembershipApplicationform::where('final_submit_signup', 1)
            ->where('renewal_status', 'renewal_due')
            ->whereNull('renewal_reminder_sent_at')
            ->whereNotNull('renewal_date')
            ->get()->each(function ($member) use (&$triggered) {
                if ($this->isPriceInvalid($member)) return;
                $result = $this->dispatchRenewalMail($member, 'reminder');
                if ($result['success']) { $member->update(['renewal_reminder_sent_at' => now()]); $triggered++; }
            });

        return response()->json(['success' => true, 'triggered' => $triggered]);
    }

    // =========================================================
    // PDF DOWNLOAD
    // =========================================================

    public function downloadPdf($id)
    {
        $member = MembershipApplicationform::findOrFail($id);
        [$step1, $step2, $step7, $payment] = $this->extractMemberData($member);
        $type = 'status';
        $pdf  = Pdf::loadView('backend.customer-details.pdf', compact('member', 'step1', 'step2', 'step7', 'payment', 'type'));
        return $pdf->stream('member-profile.pdf');
    }

    // =========================================================
    // EXPORT
    // =========================================================

    public function export($type, Request $request)
    {
        $query = MembershipApplicationform::where('final_submit_signup', 1);

        if ($request->renewal_status) $query->where('renewal_status', $request->renewal_status);
        if ($request->status)         $query->where('status', $request->status);
        if ($request->renewal_from)   $query->whereDate('renewal_date', '>=', $request->renewal_from);
        if ($request->renewal_to)     $query->whereDate('renewal_date', '<=', $request->renewal_to);

        $memberships = $query->orderByDesc('id')->get();

        if ($type === 'report') return $this->streamReport($memberships);
        if ($type === 'csv')    return $this->streamCsv($memberships);

        $filename = now()->format('y-m-d') . ' DDPU (Monthly on the 10th)';
        return Excel::download(new \App\Exports\CustomerExport($memberships), $filename . '.xlsx');
    }

    /**
     * Build a lookup of members already known to us, keyed by a normalised DD
     * reference, with every bank-detail pair ever seen for that reference:
     *   normRef => [ ['sort' => '..', 'acc' => '..'], ... ]
     *
     * Sources (merged):
     *   1. FastPay portal — whichever portal the current credentials target
     *      (test now, live after credentials are switched). A member found under
     *      ANY status (Live/Expired/Cancelled/Suspended) counts as existing,
     *      matching the client's rule "previously paid with the same bank
     *      details". To restrict to active mandates, filter on $c['Status'] ===
     *      'Live' in the portal loop.
     *   2. Local file_details — members from files we've previously processed.
     *      Empty on the very first upload, so the portal decides that time.
     *
     * Portal failure is non-fatal: we log and continue with local records only.
     */
    private function buildKnownMemberIndex(): \Illuminate\Support\Collection
    {
        $index = collect();

        $add = function ($ref, $sort, $acc) use ($index) {
            $key = $this->normRef($ref);
            if ($key === '') return;
            $pairs = $index->get($key, []);
            $pairs[] = ['sort' => (string) $sort, 'acc' => (string) $acc];
            $index->put($key, $pairs);
        };

        // 1) FastPay portal
        try {
            foreach (app(\App\Services\FastPayService::class)->getAllCustomers() as $c) {
                if (!empty($c['DDReference'])) {
                    $add($c['DDReference'], $c['SortCode'] ?? '', $c['AccountNumber'] ?? '');
                }
            }
        } catch (\Throwable $e) {
            Log::warning('FastPay portal lookup failed; using local records only for 0N. ' . $e->getMessage());
        }

        // 2) Local previously-processed records
        foreach (FileDetail::whereNotNull('dd_reference')->get(['dd_reference', 'sort_code', 'account_number']) as $r) {
            $add($r->dd_reference, $r->sort_code, $r->account_number);
        }

        return $index;
    }

    /** Normalise a DD reference for matching (strip punctuation/spaces, upper). */
    private function normRef($ref): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $ref));
    }

    /** Normalise a sort code / account number to bare digits without leading zeros. */
    private function normDigits($v): string
    {
        $digits = preg_replace('/\D/', '', (string) $v);
        return ltrim($digits, '0');
    }

        private function streamCsv($memberships)
    {
        $filename = now()->format('y-m-d') . ' DDPU (Monthly on the 10th)';

        // Decide '0N' (new-mandate setup) per member by checking whether they
        // already exist — against BOTH sources:
        //   1. The FastPay portal (whichever the current credentials target).
        //   2. Our local DB of previously-processed files (file_details).
        // First upload: local is empty, so the portal decides. After the first
        // file is processed, those records live locally too, so later exports
        // check local + portal together.
        //
        //   - Not found in either            -> new member       -> 0N + 01
        //   - Found, but bank details differ -> re-setup mandate -> 0N + 01
        //   - Found, same bank details       -> existing member  -> 17 (no 0N)
        $known = $this->buildKnownMemberIndex();

        $rows = collect();

        foreach ($memberships as $member) {
            $step1    = is_array($member->step1)        ? $member->step1        : json_decode($member->step1, true);
            $payment  = is_array($member->step1_signup) ? $member->step1_signup : json_decode($member->step1_signup, true);
            $fullName = trim(implode(' ', array_filter([
                data_get($step1, 'title'),
                data_get($step1, 'last_name'),
            ])));

            $ref    = $member->dd_reference ?? '';
            $sort   = (string) data_get($payment, 'sort_code', '');
            $acc    = (string) data_get($payment, 'account_number', '');
            $amount = round((float) ($member->price ?? 0), 2);

            $accountName = trim((string) data_get($payment, 'account_holder', '')) ?: $fullName;

            $normRef  = $this->normRef($ref);
            $existing = $ref !== '' && $known->has($normRef);

            $bankChanged = false;
            if ($existing) {
                // Existing means "same bank details" only if the current sort/acc
                // matches at least one previously-known pair (portal or local).
                $curSort = $this->normDigits($sort);
                $curAcc  = $this->normDigits($acc);
                $matched = false;
                foreach ($known->get($normRef) as $bp) {
                    if ($this->normDigits($bp['sort']) === $curSort && $this->normDigits($bp['acc']) === $curAcc) {
                        $matched = true;
                        break;
                    }
                }
                $bankChanged = !$matched;
            }

            $isNewDirectDebit = !$existing || $bankChanged;

            if ($isNewDirectDebit) {
                $rows->push([$ref, $sort, $acc, $accountName, 0.00, '0N']);
                $rows->push([$ref, $sort, $acc, $accountName, $amount, '01']);
            } else {
                $rows->push([$ref, $sort, $acc, $accountName, $amount, '17']);
            }
        }

        $export = new class($rows) implements
            \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithColumnFormatting
        {
            private $rows;
            public function __construct($rows) { $this->rows = $rows; }
            public function collection() { return $this->rows; }
            public function headings(): array
            {
                return ['DD Reference', 'Sort Code', 'Account No', 'Account Name', 'Amount', 'BACS Code'];
            }
            public function columnFormats(): array
            {
                return [
                    'B' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
                    'C' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
                    'E' => '0.00',
                    'F' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
                ];
            }
        };

        return Excel::download($export, $filename . '.xlsx');
    }

    private function streamReport($memberships): StreamedResponse
    {
        $filename = 'DDPU_Members_Report_' . now()->format('Y-m-d_His') . '.csv';

        return new StreamedResponse(function () use ($memberships) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'DD Reference', 'Title', 'First Name', 'Middle Name', 'Last Name', 'Full Name',
                'Status', 'Start Date', 'End Date', 'Inactive Since',
                'Renewal Status', 'Renewal Date', 'Renewal Mail Sent At', 'Renewal Reminder Sent At',
                'Price (£)', 'Payment Plan', 'Submitted At', 'Mail Sent At',
                'Account Holder', 'Sort Code', 'Account Number',
                'Date of Birth', 'Gender', 'Email', 'Phone', 'Mobile',
                'Address Line 1', 'Address Line 2', 'City', 'County', 'Postcode', 'Country',
                'GMC / GDC Number', 'Specialty',
                'Employer / Practice Name', 'Employer Address',
                'Indemnity Provider', 'Declaration Agreed', 'Declaration Date',
            ]);

            foreach ($memberships as $member) {
                $step1   = $this->decode($member->step1);
                $step2   = $this->decode($member->step2);
                $payment = $this->decode($member->step1_signup);
                $decl    = array_merge((array) $step1, (array) $step2);

                fputcsv($handle, [
                    $member->dd_reference ?? '',
                    data_get($decl, 'title', ''),
                    data_get($decl, 'first_name', ''),
                    data_get($decl, 'middle_name', ''),
                    data_get($decl, 'last_name', ''),
                    $this->resolveName($member),

                    $member->status ?? '',
                    $member->start_date   ? Carbon::parse($member->start_date)->format('d/m/Y')   : '',
                    $member->end_date     ? Carbon::parse($member->end_date)->format('d/m/Y')     : '',
                    $member->inactive_at  ? Carbon::parse($member->inactive_at)->format('d/m/Y')  : '',
                    $member->renewal_status ?? '',
                    $member->renewal_date ? Carbon::parse($member->renewal_date)->format('d/m/Y') : '',
                    $member->renewal_mail_sent_at     ? Carbon::parse($member->renewal_mail_sent_at)->format('d/m/Y H:i')     : '',
                    $member->renewal_reminder_sent_at ? Carbon::parse($member->renewal_reminder_sent_at)->format('d/m/Y H:i') : '',
                    number_format((float) ($member->price ?? 0), 2),
                    data_get($payment, 'payment_plan', ''),
                    $member->submitted_at
                        ? Carbon::parse($member->submitted_at)->format('d/m/Y H:i')
                        : Carbon::parse($member->created_at)->format('d/m/Y H:i'),
                    $member->mail_sent_at ? Carbon::parse($member->mail_sent_at)->format('d/m/Y H:i') : '',

                    data_get($payment, 'account_holder', ''),
                    data_get($payment, 'sort_code', ''),
                    data_get($payment, 'account_number', ''),

                    data_get($decl, 'dob', '')                ?: data_get($decl, 'date_of_birth', ''),
                    data_get($decl, 'gender', ''),
                    data_get($decl, 'email', '')              ?: data_get($decl, 'primary_email', ''),
                    data_get($decl, 'phone', '')              ?: data_get($decl, 'telephone', ''),
                    data_get($decl, 'mobile', ''),
                    data_get($decl, 'address_line_1', '')     ?: data_get($decl, 'address1', ''),
                    data_get($decl, 'address_line_2', '')     ?: data_get($decl, 'address2', ''),
                    data_get($decl, 'city', '')               ?: data_get($decl, 'town', ''),
                    data_get($decl, 'county', ''),
                    data_get($decl, 'postcode', '')           ?: data_get($decl, 'post_code', ''),
                    data_get($decl, 'country', ''),
                    data_get($decl, 'gmc_number', '')         ?: data_get($decl, 'gdc_number', '')    ?: data_get($decl, 'registration_number', ''),
                    data_get($decl, 'specialty', '')          ?: data_get($decl, 'speciality', ''),
                    data_get($decl, 'employer_name', '')      ?: data_get($decl, 'practice_name', ''),
                    data_get($decl, 'employer_address', '')   ?: data_get($decl, 'practice_address', ''),
                    data_get($decl, 'indemnity_provider', '') ?: data_get($decl, 'current_indemnifier', ''),
                    data_get($decl, 'declaration_agreed', '') ?: data_get($decl, 'agreed', ''),
                    data_get($decl, 'declaration_date', '')   ?: data_get($decl, 'signed_at', ''),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // =========================================================
    // DELETE MEMBER
    // =========================================================

    public function deleteMember(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:membership_applicationforms,id',
        ]);

        $member = MembershipApplicationform::findOrFail($request->id);

        // Normal manual delete — admin can remove any member at any time
        // (no inactive/3-year restriction). Also removes the linked login row.
        $memberName = $this->resolveName($member);

        \DB::transaction(function () use ($member) {
            if ($member->user_id) {
                \App\Models\UsersMembership::where('id', $member->user_id)->delete();
            }
            $member->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "{$memberName} has been permanently deleted.",
        ]);
    }
}