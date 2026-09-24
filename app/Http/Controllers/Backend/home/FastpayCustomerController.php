<?php

namespace App\Http\Controllers\Backend\home;

use App\Http\Controllers\Controller;
use App\Services\FastPayService;
use App\Models\FileDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FastpayCustomerController extends Controller
{
    /** Portal statuses are cached so the list page isn't four API calls on every hit. */
    private const STATUS_CACHE_KEY     = 'fastpay.customer_statuses';
    private const STATUS_CACHE_MINUTES = 10;

    protected FastPayService $fastpay;

    public function __construct(FastPayService $fastpay)
    {
        $this->fastpay = $fastpay;
    }

    /**
     * Customers tab — the customers that come from OUR uploaded files
     * (one row per unique DD reference found in file_details). The detail
     * popup still fetches that customer's history live from FastPay.
     */
    public function index(Request $request)
    {
        $error = null;

        // Pull the live MANDATE state for every customer from FastPay and write it
        // to file_details.mandate_status. It is deliberately NOT written to
        // file_details.status — that column is the per-transaction collection
        // result (processing / paid / failed) owned by `sync:fastpay-status`.
        try {
            if ($request->boolean('refresh')) {
                Cache::forget(self::STATUS_CACHE_KEY);
            }

            $statusMap = Cache::remember(
                self::STATUS_CACHE_KEY,
                now()->addMinutes(self::STATUS_CACHE_MINUTES),
                fn () => $this->fastpay->getCustomerStatusMap()
            );

            $this->syncStatuses($statusMap);
        } catch (\Throwable $e) {
            Log::error('FastPay status sync failed', ['error' => $e->getMessage()]);
            $error = 'Could not reach FastPay to refresh statuses — showing the last known status.';
        }

        $customers = FileDetail::query()
            ->whereNotNull('dd_reference')
            ->where('dd_reference', '!=', '')
            ->orderByDesc('id')
            ->get()
            // Drop the 0N "setup only" lines so each member shows once with the real amount.
            ->reject(fn ($d) => strtoupper((string) $d->bacs_code) === '0N')
            ->unique('dd_reference')
            ->map(function ($d) {
                return [
                    'dd_reference'   => $d->dd_reference,
                    'account_name'   => $d->account_name,
                    'sort_code'      => $this->padSortCode($d->sort_code),
                    'account_number' => $this->padAccount($d->account_number),
                    'amount'         => (float) ($d->amount ?? 0),
                    // Mandate state from the portal; falls back to the collection
                    // result until the first sync has run for that reference.
                    'status'         => $d->mandate_status ?: ($d->status ?: 'processing'),
                ];
            })
            ->values()
            ->all();

        return view('backend.fastpay-customers.index', compact('customers', 'error'));
    }

    /**
     * Write the portal mandate state onto every file_details row whose DD
     * reference FastPay knows about. Grouped into one UPDATE per status so a
     * full sync is a handful of queries rather than one per member.
     *
     * @param array<string, string> $statusMap normalised reference => status
     */
    private function syncStatuses(array $statusMap): void
    {
        if (!$statusMap) {
            return;
        }

        $rows = FileDetail::query()
            ->whereNotNull('dd_reference')
            ->where('dd_reference', '!=', '')
            ->get(['id', 'dd_reference', 'mandate_status']);

        $byStatus = [];

        foreach ($rows as $r) {
            $status = $statusMap[$this->normRef($r->dd_reference)] ?? null;

            if ($status === null || $status === $r->mandate_status) {
                continue;
            }

            $byStatus[$status][] = $r->id;
        }

        foreach ($byStatus as $status => $ids) {
            foreach (array_chunk($ids, 500) as $chunk) {
                FileDetail::whereIn('id', $chunk)->update(['mandate_status' => $status]);
            }
        }
    }

    /** Same normalisation FastPayService uses, so the two sides match. */
    private function normRef($ref): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $ref));
    }

    /**
     * Customer detail (the "+" expand / modal) — profile + transactions.
     * Returns JSON consumed by the modal via fetch().
     */
    public function show(string $ddReference)
    {
        try {
            $data         = $this->fastpay->getCustomerDetail($ddReference);
            $transactions = $data['Transactions'] ?? [];
            $custStatus   = $data['Customer']['Status'] ?? '';

            return response()->json([
                'success'      => true,
                'customer'     => $data['Customer'] ?? null,
                'accounts'     => $this->buildAccounts($transactions, $custStatus),
                'transactions' => $this->normaliseTransactions($transactions),
            ]);
        } catch (\Throwable $e) {
            Log::error('FastPay customer detail failed', ['ref' => $ddReference, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * FastPay's GetCustomer returns CustomerAccounts as null, so we derive the
     * bank account(s) from the transaction history (unique sort code + account
     * number). "From" is the earliest submission seen for that account.
     */
    private function buildAccounts(array $transactions, string $custStatus): array
    {
        $accounts = [];

        foreach ($transactions as $t) {
            $sort = $this->padSortCode($t['SortCode'] ?? '');
            $acc  = $this->padAccount($t['AccountNumber'] ?? '');
            $key  = $sort . '|' . $acc;
            $sub  = $t['SubmissionDate'] ?? null;

            if (!isset($accounts[$key])) {
                $accounts[$key] = [
                    'sort_code'      => $sort,
                    'account_number' => $acc,
                    'account_name'   => $t['CustomerAccountName'] ?? '',
                    'from_raw'       => $sub,
                    'status'         => $custStatus,
                ];
            } elseif ($sub && (empty($accounts[$key]['from_raw']) || $sub < $accounts[$key]['from_raw'])) {
                $accounts[$key]['from_raw'] = $sub;
            }
        }

        return array_values(array_map(function ($a) {
            $a['from'] = $this->formatDate($a['from_raw'] ?? null);
            unset($a['from_raw']);
            return $a;
        }, $accounts));
    }

    private function padSortCode($v): string
    {
        $v = preg_replace('/\D/', '', (string) $v);
        return $v === '' ? '' : str_pad($v, 6, '0', STR_PAD_LEFT);
    }

    private function padAccount($v): string
    {
        $v = preg_replace('/\D/', '', (string) $v);
        return $v === '' ? '' : str_pad($v, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Reduce FastPay's verbose transaction objects to what the UI needs and
     * derive a human label for the status.
     *
     * NOTE: FastPay returns SubmissionStatus / WebStatus as numeric codes and
     * does not document them. We derive Paid/Failed from ErrorCodeID (0 = no
     * error) and surface any ErrorCode/WebStatusDetails text when present.
     * Adjust statusLabel() once the official code list is confirmed.
     */
    private function normaliseTransactions(array $transactions): array
    {
        // Newest first, like the FastPay portal.
        usort($transactions, function ($a, $b) {
            return strcmp($b['SubmissionDate'] ?? '', $a['SubmissionDate'] ?? '');
        });

        return array_map(function ($t) {
            return [
                'submission_date' => $this->formatDate($t['SubmissionDate'] ?? null),
                'amount'          => (float) ($t['Amount'] ?? 0),
                'bacs_code'       => $t['BacsCode'] ?? '',
                'account_name'    => $t['CustomerAccountName'] ?? '',
                'sort_code'       => $this->padSortCode($t['SortCode'] ?? ''),
                'account_number'  => $this->padAccount($t['AccountNumber'] ?? ''),
                'file_name'       => $t['ImportFileName'] ?? '',
                'status'          => $this->statusLabel($t),
            ];
        }, $transactions);
    }

    private function statusLabel(array $t): string
    {
        if (!empty($t['WebStatusDetails'])) return (string) $t['WebStatusDetails'];

        // Verified against the FastPay portal: WebStatus 1 = Paid, 2 = Failed.
        // (ErrorCodeID is 0 even for failed rows, so it can't be used.)
        return match ((int) ($t['WebStatus'] ?? 0)) {
            1       => 'Paid',
            2       => 'Failed',
            0       => 'Pending',
            default => 'Other',
        };
    }

    private function formatDate(?string $raw): string
    {
        if (!$raw || str_starts_with($raw, '0001-01-01')) return '';

        try {
            return \Carbon\Carbon::parse($raw)->format('d/m/Y');
        } catch (\Throwable $e) {
            return '';
        }
    }
}
