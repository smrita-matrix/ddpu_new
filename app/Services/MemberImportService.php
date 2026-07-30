<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\UsersMembership;
use App\Models\MembershipApplicationform;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

/**
 * Shared bulk-import logic for Membership_Import_Updates.xlsx.
 *
 * Used by both the `members:import` artisan command and the admin
 * "Import Members" upload button. For each Excel row it upserts:
 *   - one users_membership login row (name/email/phone/password)
 *   - one linked membership_applicationforms row (step1..step6 + step1_signup)
 *
 * SAFE: pass $execute=false for a dry-run (no writes).
 * IDEMPOTENT: keyed on dd_reference — re-runs update instead of duplicating.
 */
class MemberImportService
{
    /** Excel column letter => field name (fallback if header row is missing). */
    public const COLS = [
        'A' => 'dd_reference', 'B' => 'member_type', 'C' => 'payment_plan', 'D' => 'annual_fee',
        'E' => 'dd_status', 'F' => 'start_date', 'G' => 'next_renewal_date', 'H' => 'gmc_gdc_type',
        'I' => 'gmc_gdc_number', 'J' => 'registration_year', 'K' => 'qualification_year', 'L' => 'specialty',
        'M' => 'professional_qualification', 'N' => 'title', 'O' => 'first_name', 'P' => 'middle_name',
        'Q' => 'last_name', 'R' => 'date_of_birth', 'S' => 'gender', 'T' => 'address_line_1',
        'U' => 'address_line_2', 'V' => 'city', 'W' => 'postal_code', 'X' => 'country',
        'Y' => 'contact_address_line_1', 'Z' => 'contact_address_line_2', 'AA' => 'contact_city',
        'AB' => 'contact_postal_code', 'AC' => 'contact_country', 'AD' => 'telephone_day',
        'AE' => 'telephone_evening', 'AF' => 'mobile_number', 'AG' => 'primary_email', 'AH' => 'secondary_email',
        'AI' => 'username', 'AJ' => 'employment_status', 'AK' => 'current_employer', 'AL' => 'employment_grade',
        'AM' => 'lead_employer', 'AN' => 'current_role_description', 'AO' => 'pni_required', 'AP' => 'issue_q31',
        'AQ' => 'issue_q32', 'AR' => 'issue_q33', 'AS' => 'claims_q1', 'AT' => 'claims_q2',
        'AU' => 'membership_cancelled', 'AV' => 'previous_membership_name', 'AW' => 'previous_membership_expiry',
        'AX' => 'account_holder', 'AY' => 'bank_name', 'AZ' => 'branch_name', 'BA' => 'sort_code',
        'BB' => 'account_number', 'BC' => 'company_name', 'BD' => 'service_number',
    ];

    /** Fields that get "NA" when blank (required-ish per the membership form). */
    public const NA_REQUIRED = [
        'gmc_gdc_type', 'gmc_gdc_number', 'specialty', 'professional_qualification', 'title',
        'first_name', 'last_name', 'date_of_birth', 'gender', 'postal_code', 'address_line_1', 'city',
        'telephone_day', 'mobile_number', 'primary_email',
        'employment_status', 'current_role_description',
        'issue_q31', 'issue_q32', 'issue_q33',
        'claims_q1', 'claims_q2', 'membership_cancelled', 'previous_membership_name', 'previous_membership_expiry',
        'account_holder', 'sort_code', 'account_number', 'service_number',
    ];

    /**
     * @param  string        $path            Absolute path to the .xlsx
     * @param  bool          $execute         false = dry-run (no writes)
     * @param  string        $defaultCountry  Country used when the cell is blank
     * @param  callable|null $onRow           Optional fn(array $mapped): void — per-row hook (e.g. preview CSV)
     * @return array{created:int,updated:int,skipped:int,total_annual:float,default_country:string}
     */
    public function import(string $path, bool $execute, string $defaultCountry = 'United Kingdom', ?callable $onRow = null): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $raw = $reader->load($path)->getSheet(0)->toArray(null, true, true, true);

        // Map header row -> column letters (resilient to column order changes).
        $headerRow = array_shift($raw);
        $letterFor = [];
        foreach ($headerRow as $letter => $name) {
            $name = strtolower(trim((string) $name));
            if ($name !== '') $letterFor[$name] = $letter;
        }

        $created = 0; $updated = 0; $skipped = 0; $totalAnnual = 0.0;

        foreach ($raw as $r) {
            $get = function (string $field) use ($r, $letterFor) {
                $letter = $letterFor[$field] ?? array_search($field, self::COLS, true);
                if ($letter === false || !array_key_exists($letter, $r)) return null;
                $v = $r[$letter];
                return is_string($v) ? trim($v) : $v;
            };

            $ref = $get('dd_reference');
            if (!$ref) { $skipped++; continue; }

            $mapped = $this->mapRow($get, $defaultCountry);
            $totalAnnual += $mapped['annual_fee'];

            if ($onRow) $onRow($mapped);

            if (!$execute) {
                MembershipApplicationform::where('dd_reference', $ref)->exists() ? $updated++ : $created++;
                continue;
            }

            DB::transaction(function () use ($mapped, &$created, &$updated) {
                $app = MembershipApplicationform::where('dd_reference', $mapped['dd_reference'])->first();

                // users_membership: reuse the linked row on re-run, else create.
                $user = ($app && $app->user_id) ? UsersMembership::find($app->user_id) : null;
                $user = $user ?? new UsersMembership();
                $isNewUser = !$user->exists;
                $user->name  = $mapped['name'];
                $user->email = $mapped['email'];
                $user->phone = $mapped['phone'];
                if ($isNewUser) $user->password = Hash::make('123456');
                $user->save();

                $app = $app ?: new MembershipApplicationform();
                // NOTE: step1..step6 are cast to array by the model; step1_signup is
                // NOT cast, so it must be json_encoded manually. status/price/dates/
                // mail_sent_at are not in $fillable, so assign directly (not via fill()).
                $app->user_id             = $user->id;
                $app->step1               = $mapped['step1'];
                $app->step2               = $mapped['step2'];
                $app->step3               = $mapped['step3'];
                $app->step4               = $mapped['step4'];
                $app->step5               = $mapped['step5'];
                $app->step6               = $mapped['step6'];
                $app->step1_signup        = json_encode($mapped['step1_signup']);
                $app->dd_reference        = $mapped['dd_reference'];
                $app->final_status        = 1;
                $app->final_submit_signup = 1;
                $app->status              = 'active';
                $app->price               = $mapped['price'];
                $app->start_date          = $mapped['start_date'];
                $app->renewal_date        = $mapped['renewal_date'];
                $app->submitted_at        = now();
                // Suppress the onboarding welcome auto-mail for these existing members.
                $app->mail_sent_at        = now();

                $exists = $app->exists;
                $app->save();
                $exists ? $updated++ : $created++;
            });
        }

        return [
            'created'         => $created,
            'updated'         => $updated,
            'skipped'         => $skipped,
            'total_annual'    => $totalAnnual,
            'default_country' => $defaultCountry,
        ];
    }

    /** Build the fully-mapped member payload from a row accessor. */
    public function mapRow(callable $get, string $defaultCountry): array
    {
        $na = fn ($field) => $this->naValue($get($field), $field);

        $country = $get('country') ?: $defaultCountry;

        $addrMain = [
            'postal-code'    => $na('postal_code'),
            'address_line_1' => $na('address_line_1'),
            'address_line_2' => (string) ($get('address_line_2') ?? ''),
            'city'           => $na('city'),
            'country'        => $country,
        ];

        $contactBlank = !$get('contact_address_line_1');
        $contact = $contactBlank ? [
            'same_as_main_address'   => '1',
            'contact_postal_code'    => $addrMain['postal-code'],
            'contact_address_line_1' => $addrMain['address_line_1'],
            'contact_address_line_2' => $addrMain['address_line_2'],
            'contact_city'           => $addrMain['city'],
            'contact_country'        => $addrMain['country'],
        ] : [
            'same_as_main_address'   => '0',
            'contact_postal_code'    => (string) ($get('contact_postal_code') ?? $addrMain['postal-code']),
            'contact_address_line_1' => (string) $get('contact_address_line_1'),
            'contact_address_line_2' => (string) ($get('contact_address_line_2') ?? ''),
            'contact_city'           => (string) ($get('contact_city') ?? $addrMain['city']),
            'contact_country'        => $get('contact_country') ?: $country,
        ];

        $step1 = array_merge([
            'gmc_gdc_type'               => $na('gmc_gdc_type'),
            'gmc_gdc_number'             => $na('gmc_gdc_number'),
            'registration_year'          => (string) ($get('registration_year') ?? ''),
            'qualification_year'         => (string) ($get('qualification_year') ?? ''),
            'specialty'                  => $na('specialty'),
            'professional_qualification' => $na('professional_qualification'),
            'title'                      => $na('title'),
            'first_name'                 => $na('first_name'),
            'middle_name'                => (string) ($get('middle_name') ?? ''),
            'last_name'                  => $na('last_name'),
            'date_of_birth'              => $this->fmtDate($get('date_of_birth')) ?: 'NA',
            'gender'                     => $na('gender'),
        ], $addrMain, $contact);

        $step2 = [
            'telephone_day'     => $na('telephone_day'),
            'telephone_evening' => (string) ($get('telephone_evening') ?? ''),
            'mobile_number'     => $na('mobile_number'),
            'primary_email'     => $na('primary_email'),
            'secondary_email'   => (string) ($get('secondary_email') ?? ''),
            'username'          => (string) ($get('username') ?? ''),
        ];

        $step3 = [
            'employment_status'        => $na('employment_status'),
            'current_employer'         => $get('current_employer') ?: null,
            'employment_grade'         => $get('employment_grade') ?: null,
            'lead_employer'            => $get('lead_employer') ?: null,
            'current_role_description' => $na('current_role_description'),
        ];

        $pni = strtolower((string) $get('pni_required'));
        $pniYes = in_array($pni, ['1', 'yes', 'y', 'true'], true) ? '1' : '0';
        $step4 = [
            'pni_required_yes' => $pniYes,
            'pni_required_no'  => $pniYes === '1' ? '0' : '1',
        ];

        $step5 = [
            'issue_q31' => $na('issue_q31'),
            'issue_q32' => $na('issue_q32'),
            'issue_q33' => $na('issue_q33'),
        ];

        $step6 = [
            'claims_q1'                  => $na('claims_q1'),
            'claims_q2'                  => $na('claims_q2'),
            'membership_cancelled'       => $na('membership_cancelled'),
            'previous_membership_name'   => $na('previous_membership_name'),
            'previous_membership_expiry' => $this->fmtDate($get('previous_membership_expiry')) ?: 'NA',
        ];

        $sortCode  = $this->pad($get('sort_code'), 6);
        $accountNo = $this->pad($get('account_number'), 8);
        $step1_signup = [
            'type'           => strtolower((string) ($get('member_type') ?: 'electronic')),
            'company_name'   => (string) ($get('company_name') ?? ''),
            'service_number' => (string) ($get('service_number') ?: 'NA'),
            'payment_plan'   => strtolower((string) ($get('payment_plan') ?: 'monthly')),
            'account_holder' => $na('account_holder'),
            'bank_name'      => (string) ($get('bank_name') ?? ''),
            'branch_name'    => (string) ($get('branch_name') ?? ''),
            'sort_code'      => $sortCode,
            'account_number' => $accountNo,
        ];

        $annualFee = (float) ($get('annual_fee') ?: 0);
        $plan = strtolower((string) ($get('payment_plan') ?: 'monthly'));
        $isMonthly = str_contains($plan, 'month');
        $price = $isMonthly ? round($annualFee / 12, 2) : round($annualFee, 2);

        $startDate   = $this->toCarbon($get('start_date'));
        $renewalDate = $this->toCarbon($get('next_renewal_date'));

        $name = trim(implode(' ', array_filter([
            $step1['title'] !== 'NA' ? $step1['title'] : '',
            $step1['first_name'] !== 'NA' ? $step1['first_name'] : '',
            $step1['middle_name'],
            $step1['last_name'] !== 'NA' ? $step1['last_name'] : '',
        ]))) ?: 'Member';

        return [
            'dd_reference'  => (string) $get('dd_reference'),
            'name'          => $name,
            'email'         => $step2['primary_email'],
            'phone'         => $step2['mobile_number'],
            'step1'         => $step1,
            'step2'         => $step2,
            'step3'         => $step3,
            'step4'         => $step4,
            'step5'         => $step5,
            'step6'         => $step6,
            'step1_signup'  => $step1_signup,
            'price'         => $price,
            'annual_fee'    => $annualFee,
            'plan'          => $plan,
            'sort_code'     => $sortCode,
            'account_number'=> $accountNo,
            'start_date'    => $startDate,
            'renewal_date'  => $renewalDate,
        ];
    }

    private function naValue($value, string $field): string
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value !== null && $value !== '') return (string) $value;
        return in_array($field, self::NA_REQUIRED, true) ? 'NA' : '';
    }

    private function pad($value, int $len): string
    {
        $digits = preg_replace('/\D/', '', (string) $value);
        return $digits === '' ? 'NA' : str_pad($digits, $len, '0', STR_PAD_LEFT);
    }

    private function fmtDate($value): ?string
    {
        $c = $this->toCarbon($value);
        return $c ? $c->format('d/m/Y') : null;
    }

    private function toCarbon($value): ?Carbon
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                );
            } catch (\Throwable $e) { /* fall through */ }
        }
        $value = trim((string) $value);
        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-m-y', 'd/m/y'] as $fmt) {
            try {
                $c = Carbon::createFromFormat($fmt, $value);
                if ($c) return $c->startOfDay();
            } catch (\Throwable $e) { /* try next */ }
        }
        try { return Carbon::parse($value); } catch (\Throwable $e) { return null; }
    }
}
