<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MemberImportService;

/**
 * Bulk-import existing members from Membership_Import_Updates.xlsx into
 * users_membership + membership_applicationforms.
 *
 * SAFE BY DEFAULT: dry-run (no writes) unless --execute is passed.
 * IDEMPOTENT: keyed on dd_reference, so re-running updates instead of duplicating.
 *
 *   php artisan members:import                 (dry-run, default file)
 *   php artisan members:import --execute       (write to DB)
 *   php artisan members:import path/to.xlsx --execute
 */
class ImportMembers extends Command
{
    protected $signature = 'members:import
        {file=Membership_Import_Updates.xlsx : Path to the .xlsx (relative to project root or absolute)}
        {--execute : Actually write to the DB (otherwise dry-run preview only)}
        {--country=United Kingdom : Default country when the cell is blank}
        {--preview-csv= : Write a full computed-values review CSV to this path (dry-run inspection)}';

    protected $description = 'Bulk-import members from the Excel sheet into users_membership + membership_applicationforms';

    public function handle(MemberImportService $service): int
    {
        $execute = (bool) $this->option('execute');
        $defaultCountry = (string) $this->option('country');

        $path = $this->argument('file');
        if (!preg_match('#^([A-Za-z]:|/)#', $path)) {
            $path = base_path($path);
        }
        if (!is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $this->info(($execute ? '🟢 EXECUTE' : '🟡 DRY-RUN (no writes)') . " — reading {$path}");

        // Optional full review CSV of the computed values.
        $csvHandle = null;
        $previewCsvPath = $this->option('preview-csv');
        if ($previewCsvPath) {
            $csvHandle = fopen($previewCsvPath, 'w');
            fputcsv($csvHandle, [
                'dd_reference', 'name', 'email', 'phone', 'plan', 'price', 'annual_fee',
                'sort_code', 'account_number', 'account_holder', 'start_date', 'renewal_date',
                'address_line_1', 'city', 'postcode', 'country', 'contact_same_as_main', 'status',
            ]);
        }

        $preview = [];
        $onRow = function (array $m) use (&$preview, $csvHandle) {
            if (count($preview) < 5) {
                $preview[] = [$m['dd_reference'], $m['name'], $m['plan'], number_format($m['price'], 2),
                    $m['sort_code'], $m['account_number'], $m['step1_signup']['account_holder']];
            }
            if ($csvHandle) {
                fputcsv($csvHandle, [
                    $m['dd_reference'], $m['name'], $m['email'], $m['phone'], $m['plan'],
                    number_format($m['price'], 2), number_format($m['annual_fee'], 2),
                    $m['sort_code'], $m['account_number'], $m['step1_signup']['account_holder'],
                    $m['start_date']?->format('d/m/Y'), $m['renewal_date']?->format('d/m/Y'),
                    $m['step1']['address_line_1'], $m['step1']['city'], $m['step1']['postal-code'],
                    $m['step1']['country'], $m['step4']['pni_required_yes'], 'active',
                ]);
            }
        };

        $stats = $service->import($path, $execute, $defaultCountry, $onRow);

        if ($csvHandle) {
            fclose($csvHandle);
            $this->info("Full review CSV written to: {$previewCsvPath}");
        }

        $this->newLine();
        $this->line('Preview (first ' . count($preview) . ' rows): DD Ref | Name | Plan | Price | Sort | Account | Account Holder');
        foreach ($preview as $p) {
            $this->line('  ' . implode('  |  ', $p));
        }
        $this->newLine();
        $this->table(['Metric', 'Value'], [
            ['Mode', $execute ? 'EXECUTE (written)' : 'DRY-RUN (nothing written)'],
            ['Would create / created', $stats['created']],
            ['Would update / updated', $stats['updated']],
            ['Skipped blank rows', $stats['skipped']],
            ['Total annual fees', '£' . number_format($stats['total_annual'], 2)],
            ['Default country used', $stats['default_country']],
        ]);

        $execute
            ? $this->info('✅ Import complete.')
            : $this->warn('Dry-run only. Re-run with --execute to write to the database.');

        return self::SUCCESS;
    }
}
