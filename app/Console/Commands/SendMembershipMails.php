<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MembershipApplicationform;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendMembershipMails extends Command
{
    protected $signature   = 'membership:send-scheduled-mails';
    protected $description = 'Send all scheduled membership mails and auto-initialize renewal data';

    public function handle()
    {
        Log::info('===== CRON START: SendMembershipMails =====');
        $today = Carbon::today();
        Log::info('Today: ' . $today->toDateString());

        // =================================================================
        // BLOCK 0 — Auto-initialize renewal data for members who have
        //            entered the renewal phase but have no renewal_date set.
        //            Sets renewal_date = start_date + 1 year (the expiry).
        //            Sets renewal_status = overdue | renewal_due automatically.
        // =================================================================
        $this->line('');
        $this->info('--- [0] Auto-initializing renewal data ---');

        $needsInit = MembershipApplicationform::where('final_submit_signup', 1)
            ->whereNotNull('start_date')
            ->where(function($q) {
                $q->whereNull('renewal_status')
                  ->orWhere('renewal_status', '!=', 'due');
            })
            ->get();

        $initCount = 0;
        foreach ($needsInit as $member) {
            $expiry = Carbon::parse($member->start_date)->addYear();
            if (!$expiry->isPast()) continue; // still in onboarding
            if ($member->renewal_status === 'due') continue; // already completed

            $changed = false;

            // Auto-set renewal_date from expiry if not set
            if (!$member->renewal_date) {
                $member->renewal_date = $expiry->toDateString();
                $changed = true;
            }

            // Auto-calculate renewal_status
            $daysLeft = $today->diffInDays(Carbon::parse($member->renewal_date), false);
            if ($daysLeft < 0 && $member->renewal_status !== 'overdue') {
                $member->renewal_status = 'overdue';
                $changed = true;
            } elseif ($daysLeft >= 0 && $daysLeft <= 21 && $member->renewal_status !== 'renewal_due') {
                $member->renewal_status = 'renewal_due';
                $changed = true;
            }

            if ($changed) {
                $member->save();
                $initCount++;
                Log::info("Auto-init renewal – member ID {$member->id}: status={$member->renewal_status}, date={$member->renewal_date}");
            }
        }

        $this->info("Auto-initialized: {$initCount} members");

        // =================================================================
        // BLOCK 1 — Status mails (active/inactive) scheduled by admin
        // =================================================================
        $this->line('');
        $this->info('--- [1] Processing scheduled STATUS mails ---');

        $statusMails = MembershipApplicationform::where('final_submit_signup', 1)
            ->whereNull('mail_sent_at')
            ->whereNotNull('mail_trigger_date')
            ->whereDate('mail_trigger_date', '<=', $today)
            ->get();

        $this->info("Found: {$statusMails->count()}");

        foreach ($statusMails as $member) {
            try {
                [$step1, $step2, $step7, $payment, $fullName, $email] = $this->extractData($member);
                if (!$email) { $this->warn("  ⚠ No email – ID {$member->id}"); continue; }

                $mailType = $member->mail_trigger_type ?? 'active';

                if ($mailType === 'active') {
                    $this->sendActiveStatusMail($member, $step1, $step2, $step7, $payment, $fullName, $email);
                } else {
                    $this->sendInactiveStatusMail($member, $fullName, $email);
                }

                $member->mail_sent_at      = now();
                $member->mail_trigger_date = null;
                $member->mail_trigger_type = null;
                $member->save();

                $this->info("  ✅ Status mail ({$mailType}) → ID {$member->id}");
            } catch (\Exception $e) {
                $this->error("  ❌ Failed – ID {$member->id}: " . $e->getMessage());
                Log::error("Status mail FAILED – ID {$member->id}: " . $e->getMessage());
            }
        }

        // =================================================================
        // BLOCK 2 — Renewal COMPLETED mails (with PDF) scheduled by admin
        // =================================================================
        $this->line('');
        $this->info('--- [2] Processing scheduled RENEWAL COMPLETED mails ---');

        $renewalMails = MembershipApplicationform::where('final_submit_signup', 1)
            ->whereNull('renewal_mail_sent_at')
            ->whereNotNull('renewal_mail_trigger_date')
            ->whereDate('renewal_mail_trigger_date', '<=', $today)
            ->where('renewal_mail_trigger_type', 'completed')
            ->get();

        $this->info("Found: {$renewalMails->count()}");

        foreach ($renewalMails as $member) {
            try {
                [$step1, $step2, $step7, $payment, $fullName, $email] = $this->extractData($member);
                if (!$email) { $this->warn("  ⚠ No email – ID {$member->id}"); continue; }

                $this->sendRenewalCompletedMail($member, $step1, $step2, $step7, $payment, $fullName, $email);

                $member->renewal_mail_sent_at      = now();
                $member->renewal_mail_trigger_date = null;
                $member->renewal_mail_trigger_type = null;
                $member->save();

                $this->info("  ✅ Renewal completed mail → ID {$member->id}");
            } catch (\Exception $e) {
                $this->error("  ❌ Failed – ID {$member->id}: " . $e->getMessage());
                Log::error("Renewal-completed mail FAILED – ID {$member->id}: " . $e->getMessage());
            }
        }

        // =================================================================
        // BLOCK 3 — Auto 21-day RENEWAL REMINDER (no PDF, fires once only)
        // =================================================================
        $this->line('');
        $this->info('--- [3] Processing auto 21-day RENEWAL REMINDER mails ---');

        $reminderCandidates = MembershipApplicationform::where('final_submit_signup', 1)
            ->where('renewal_status', 'renewal_due')
            ->whereNull('renewal_reminder_sent_at')
            ->whereNotNull('renewal_date')
            ->get();

        $this->info("Candidates: {$reminderCandidates->count()}");
        $reminderSent = 0;

        foreach ($reminderCandidates as $member) {
            $daysLeft = $today->diffInDays(Carbon::parse($member->renewal_date), false);
            if ($daysLeft < 0 || $daysLeft > 21) continue;

            try {
                [$step1, $step2, $step7, $payment, $fullName, $email] = $this->extractData($member);
                if (!$email) { $this->warn("  ⚠ No email – ID {$member->id}"); continue; }

                $this->sendRenewalReminderMail($member, $fullName, $email, $step1, $payment);

                $member->renewal_reminder_sent_at = now();
                $member->save();

                $this->info("  ✅ 21-day reminder → ID {$member->id} ({$daysLeft}d remaining)");
                Log::info("21-day reminder sent – member ID {$member->id}");
                $reminderSent++;
            } catch (\Exception $e) {
                $this->error("  ❌ Failed – ID {$member->id}: " . $e->getMessage());
                Log::error("Reminder mail FAILED – ID {$member->id}: " . $e->getMessage());
            }
        }

        $this->info("Reminders sent: {$reminderSent}");

        // =================================================================
        // BLOCK 4 — Auto OVERDUE notification (fires once per cycle)
        //           Sends when renewal_date has passed and reminder not sent
        // =================================================================
        $this->line('');
        $this->info('--- [4] Processing OVERDUE notifications ---');

        $overdueCandidates = MembershipApplicationform::where('final_submit_signup', 1)
            ->where('renewal_status', 'overdue')
            ->whereNull('renewal_reminder_sent_at')
            ->whereNotNull('renewal_date')
            ->whereDate('renewal_date', '<', $today)
            ->get();

        $this->info("Overdue without notification: {$overdueCandidates->count()}");
        $overdueSent = 0;

        foreach ($overdueCandidates as $member) {
            try {
                [$step1, $step2, $step7, $payment, $fullName, $email] = $this->extractData($member);
                if (!$email) { $this->warn("  ⚠ No email – ID {$member->id}"); continue; }

                $this->sendRenewalReminderMail($member, $fullName, $email, $step1, $payment); // same template, overdue context

                $member->renewal_reminder_sent_at = now();
                $member->save();

                $this->info("  ✅ Overdue notice → ID {$member->id}");
                Log::info("Overdue notice sent – member ID {$member->id}");
                $overdueSent++;
            } catch (\Exception $e) {
                $this->error("  ❌ Failed – ID {$member->id}: " . $e->getMessage());
                Log::error("Overdue notice FAILED – ID {$member->id}: " . $e->getMessage());
            }
        }

        $this->info("Overdue notices sent: {$overdueSent}");

        Log::info('===== CRON END =====');
        $this->line('');
        $this->info('✅ All done.');
    }

    // =========================================================================
    // MAIL HELPERS
    // =========================================================================

    private function sendActiveStatusMail($member, $step1, $step2, $step7, $payment, $fullName, $email)
    {
        $type       = 'status';
        $pdf        = Pdf::loadView('backend.customer-details.pdfstatus', compact('member','step1','step2','step7','payment','type'));
        $pdfContent = $pdf->output();

        Mail::send('backend.customer-details.mail-active', [
            'name'             => $fullName,
            'startDate'        => $member->start_date ? Carbon::parse($member->start_date)->format('d F Y') : 'N/A',
            'membershipNumber' => $member->dd_reference ?? 'N/A',
        ], function ($msg) use ($email, $fullName, $pdfContent, $member) {
            $msg->to($email, $fullName)->cc('admin@ddpu.co.uk')
                ->subject('Welcome to DDPU – Your Membership Certificate')
                ->attachData($pdfContent, 'DDPU_Certificate_' . ($member->dd_reference ?? $member->id) . '.pdf', ['mime' => 'application/pdf']);
        });
    }

    private function sendInactiveStatusMail($member, $fullName, $email)
    {
        Mail::send('backend.customer-details.mail-inactive', ['name' => $fullName],
            function ($msg) use ($email, $fullName) {
                $msg->to($email, $fullName)->cc('admin@ddpu.co.uk')->subject('DDPU Membership – Account Status Update');
            });
    }

    private function sendRenewalCompletedMail($member, $step1, $step2, $step7, $payment, $fullName, $email)
    {
        $type       = 'renewal';
        $pdf        = Pdf::loadView('backend.customer-details.pdfstatus', compact('member','step1','step2','step7','payment','type'));
        $pdfContent = $pdf->output();

        Mail::send('backend.customer-details.mail-renewal-completed', [
            'name'             => $fullName,
            'renewalDate'      => $member->renewal_date ? Carbon::parse($member->renewal_date)->format('d F Y') : 'N/A',
            'membershipNumber' => $member->dd_reference ?? 'N/A',
        ], function ($msg) use ($email, $fullName, $pdfContent, $member) {
            $msg->to($email, $fullName)->cc('admin@ddpu.co.uk')
                ->subject('DDPU – Your Membership Has Been Renewed')
                ->attachData($pdfContent, 'DDPU_Renewal_' . ($member->dd_reference ?? $member->id) . '.pdf', ['mime' => 'application/pdf']);
        });
    }

    private function sendRenewalReminderMail($member, $fullName, $email, $step1 = [], $payment = [])
    {
        // The renewal-reminder view requires the full renewed-period context
        // ($start, $end, $isMonthly, amounts, salutation). Previously only name/
        // renewalDate/membershipNumber were passed, which threw
        // "Undefined variable $start" and failed every overdue notice in the cron.

        // Renewed membership period — anchored on renewal_date (overdue rows always have one)
        $start = $member->renewal_date ? Carbon::parse($member->renewal_date) : Carbon::now();
        $end   = $start->copy()->addYear()->subDay();

        $paymentPlan = strtolower(trim((string) data_get($payment, 'payment_plan', '')));
        $isMonthly   = str_contains($paymentPlan, 'monthly');
        $price       = (float) ($member->price ?? 0);

        if ($isMonthly) {
            $monthlyAmount     = number_format($price, 2);
            $totalAnnualAmount = number_format($price * 12, 2);
        } else {
            $monthlyAmount     = number_format($price, 2);
            $totalAnnualAmount = number_format($price, 2);
        }

        $salutationTitle = trim((string) data_get($step1, 'title', ''));
        $surname         = trim((string) data_get($step1, 'last_name', ''));

        Mail::send('backend.customer-details.mail-renewal-reminder', [
            'name'              => $fullName,
            'salutationTitle'   => $salutationTitle,
            'surname'           => $surname,
            'start'             => $start,
            'end'               => $end,
            'isMonthly'         => $isMonthly,
            'monthlyAmount'     => $monthlyAmount,
            'totalAnnualAmount' => $totalAnnualAmount,
            'renewalDate'       => $member->renewal_date ? Carbon::parse($member->renewal_date)->format('d F Y') : 'N/A',
            'membershipNumber'  => $member->dd_reference ?? 'N/A',
        ], function ($msg) use ($email, $fullName) {
            $msg->to($email, $fullName)->cc('admin@ddpu.co.uk')->subject('DDPU – Your Membership Renewal Is Due Soon');
        });
    }

    private function extractData(MembershipApplicationform $member): array
    {
        $step1   = is_array($member->step1)        ? $member->step1        : json_decode($member->step1, true);
        $step2   = is_array($member->step2)        ? $member->step2        : json_decode($member->step2, true);
        $step7   = is_array($member->step7)        ? $member->step7        : json_decode($member->step7, true);
        $payment = is_array($member->step1_signup) ? $member->step1_signup : json_decode($member->step1_signup, true);

        $fullName = trim(implode(' ', array_filter([
            data_get($step1, 'first_name'),
            data_get($step1, 'middle_name'),
            data_get($step1, 'last_name'),
        ])));

        $email = data_get($step2, 'primary_email') ?? data_get($step1, 'primary_email');
        return [$step1, $step2, $step7, $payment, $fullName, $email];
    }
}