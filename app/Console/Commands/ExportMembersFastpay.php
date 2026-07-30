<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MembershipApplicationform;

/**
 * Export active members as a FastPay collection CSV.
 *
 * Matches the proven-working file format (public/uploads/fastpay/.../DDPULtd_*.csv):
 * a 22-column header, with the first 6 columns populated per row:
 *   DD REFERENCE, Sort Code, Account No, Account Name, Amount, BACS Code
 *
 * Sort codes / account numbers are already zero-padded at import time
 * (6 / 8 digits). Amount = member price (monthly instalment for monthly plans).
 *
 *   php artisan members:export-fastpay
 *   php artisan members:export-fastpay --bacs=17
 *   php artisan members:export-fastpay --new        (emit 0N setup + 01 first collection)
 *   php artisan members:export-fastpay --out=path.csv
 */
class ExportMembersFastpay extends Command
{
    protected $signature = 'members:export-fastpay
        {--bacs=17 : BACS transaction code for regular collections}
        {--new : Treat as new mandates: emit a 0N (£0 setup) + 01 (first collection) pair per member}
        {--status=active : Only include members with this status (blank = all final_submit_signup=1)}
        {--out= : Output CSV path (default: public/uploads/fastpay/YYYY/MM/DDPULtd_YYYYMMDD.csv)}';

    protected $description = 'Export active members to a FastPay collection CSV';

    /** Full 22-column FastPay header (only the first 6 are populated). */
    private const HEADER = [
        'DD REFERENCE', 'Sort Code', 'Account No', 'Account Name', 'Amount', 'BACS Code',
        'Invoice No (Optional)', 'Title', 'Initial', 'Forename', 'Surname', 'Salutation 1',
        'Salutation 2', 'Address 1', 'Address 2', 'Area', 'Town', 'Postcode',
        'Phone', 'Mobile', 'Email', 'Notes (Optional)',
    ];

    public function handle(): int
    {
        $bacs = (string) $this->option('bacs');
        $new = (bool) $this->option('new');
        $status = $this->option('status');

        $query = MembershipApplicationform::where('final_submit_signup', 1);
        if ($status !== '' && $status !== null) {
            $query->where('status', $status);
        }
        $members = $query->orderBy('id')->get();

        if ($members->isEmpty()) {
            $this->warn('No members matched. Nothing exported.');
            return self::SUCCESS;
        }

        $out = $this->option('out') ?: public_path(
            'uploads/fastpay/' . now()->format('Y/m') . '/DDPULtd_' . now()->format('Ymd') . '.csv'
        );
        @mkdir(dirname($out), 0777, true);

        $handle = fopen($out, 'w');
        fwrite($handle, "\xEF\xBB\xBF"); // BOM so Excel shows headers cleanly
        fputcsv($handle, self::HEADER);

        $rows = 0; $total = 0.0; $blankBank = 0;
        foreach ($members as $m) {
            $payment = is_array($m->step1_signup) ? $m->step1_signup : (json_decode($m->step1_signup, true) ?: []);
            $step1   = is_array($m->step1) ? $m->step1 : (json_decode($m->step1, true) ?: []);

            $ref  = trim((string) $m->dd_reference);
            $sort = trim((string) ($payment['sort_code'] ?? ''));
            $acc  = trim((string) ($payment['account_number'] ?? ''));

            $fullName = trim(implode(' ', array_filter([
                ($step1['title'] ?? null) !== 'NA' ? ($step1['title'] ?? '') : '',
                ($step1['last_name'] ?? null) !== 'NA' ? ($step1['last_name'] ?? '') : '',
            ])));
            $accountName = trim((string) ($payment['account_holder'] ?? '')) ?: $fullName;
            $amount = number_format((float) ($m->price ?? 0), 2, '.', '');

            if ($ref === '' || $sort === '' || $sort === 'NA' || $acc === '' || $acc === 'NA') {
                $blankBank++;
            }

            if ($new) {
                fputcsv($handle, $this->pad([$ref, $sort, $acc, $accountName, '0.00', '0N']));
                fputcsv($handle, $this->pad([$ref, $sort, $acc, $accountName, $amount, '01']));
                $rows += 2;
            } else {
                fputcsv($handle, $this->pad([$ref, $sort, $acc, $accountName, $amount, $bacs]));
                $rows++;
            }
            $total += (float) ($m->price ?? 0);
        }
        fclose($handle);

        $this->info('✅ FastPay CSV written: ' . $out);
        $this->table(['Metric', 'Value'], [
            ['Members exported', $members->count()],
            ['CSV data rows', $rows],
            ['BACS mode', $new ? '0N + 01 (new mandates)' : $bacs . ' (regular)'],
            ['Total amount (per collection)', '£' . number_format($total, 2)],
            ['Rows with missing bank/ref', $blankBank],
        ]);
        if ($blankBank > 0) {
            $this->warn("{$blankBank} row(s) have a missing DD reference / sort code / account number — review before uploading.");
        }

        return self::SUCCESS;
    }

    /** Right-pad a data row to the full 22-column width. */
    private function pad(array $row): array
    {
        return array_pad($row, count(self::HEADER), '');
    }
}
