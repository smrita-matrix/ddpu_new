<!doctype html>
<html lang="en">

<head>
    @include('components.backend.head')
</head>

@include('components.backend.header')

<!--start sidebar wrapper-->
@include('components.backend.sidebar')
<!--end sidebar wrapper-->

<style>
    /* status pills (theme-consistent) */
    .fp-badge{padding:4px 11px;border-radius:999px;font-size:11px;font-weight:600;display:inline-block;}
    .fp-live{background:#dcfce7;color:#166534;}
    .fp-cancelled{background:#fee2e2;color:#991b1b;}
    .fp-expired{background:#fef3c7;color:#92400e;}
    .fp-suspended{background:#e5e7eb;color:#374151;}
    .fp-paid{background:#dcfce7;color:#166534;}
    .fp-failed{background:#fee2e2;color:#991b1b;}

    /* detail modal */
    .fp-modal{position:fixed;inset:0;background:rgba(17,24,39,.55);display:none;align-items:flex-start;justify-content:center;z-index:1080;padding:40px 16px;overflow:auto;}
    .fp-modal.open{display:flex;}
    .fp-modal-box{background:#fff;border-radius:10px;width:100%;max-width:940px;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;}
    .fp-modal-head{padding:15px 22px;display:flex;justify-content:space-between;align-items:center;}
    .fp-modal-head h5{margin:0;font-size:16px;font-weight:600;color:#fff;}
    .fp-modal-close{background:none;border:none;color:#fff;font-size:24px;cursor:pointer;line-height:1;}
    .fp-modal-body{padding:20px 22px;}
    .fp-sub{font-size:14px;color:#374151;margin:18px 0 10px;font-weight:600;font-style:italic;text-align:center;}
    .fp-meta{font-size:14px;margin-bottom:6px;}
    .fp-loading{text-align:center;padding:30px;color:#6b7280;}
    .fp-modal-body .table{font-size:13px;margin-bottom:0;}
    .fp-modal-body .table th{background:#f9fafb;white-space:nowrap;}
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6"><h4>FastPay Customers</h4></div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">
                            <svg class="stroke-icon"><use href="../assets/svg/icon-sprite.svg#stroke-home"></use></svg>
                        </a></li>
                        <li class="breadcrumb-item active">FastPay Customers</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <p class="mb-0" style="font-size:13px;color:#6b7280;">
                                Customers from your uploaded Direct Debit files
                                &mdash; {{ count($customers) }} customer(s). Statuses come from FastPay.
                                Click <b>View</b> for full history.
                            </p>
                            <a href="{{ url('/fastpay/customers') }}?refresh=1"
                               class="btn btn-primary btn-sm" style="color:#fff;white-space:nowrap;padding:5px 16px;">
                                Refresh statuses
                            </a>
                        </div>

                        @if($error)
                            <div class="alert alert-danger" style="font-size:13.5px;">⚠️ {{ $error }}</div>
                        @endif

                        <div class="table-responsive custom-scrollbar">
                            <table class="table table-bordered" id="basic-1">
                                <thead>
                                    <tr>
                                        <th>DD Reference</th>
                                        <th>Account Name</th>
                                        <th>Sort Code</th>
                                        <th>Account Number</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $c)
                                        @php
                                            $status = $c['status'] ?? '';
                                            $sl     = strtolower($status);
                                            $cls    = match ($sl) {
                                                'live'      => 'fp-live',
                                                'paid'      => 'fp-paid',
                                                'cancelled' => 'fp-cancelled',
                                                'expired'   => 'fp-expired',
                                                'failed'    => 'fp-failed',
                                                default     => 'fp-suspended',
                                            };
                                            $ref    = $c['dd_reference'] ?? '';
                                        @endphp
                                        <tr>
                                            <td>{{ $ref }}</td>
                                            <td>{{ $c['account_name'] ?? '' }}</td>
                                            <td>{{ $c['sort_code'] ?? '' }}</td>
                                            <td>{{ $c['account_number'] ?? '' }}</td>
                                            <td>£{{ number_format($c['amount'] ?? 0, 2) }}</td>
                                            <td><span class="fp-badge {{ $cls }}">{{ ucfirst($status) }}</span></td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm"
                                                        style="color:#fff;white-space:nowrap;padding:5px 16px;"
                                                        onclick="fpViewCustomer(@js($ref))">View</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail modal -->
<div class="fp-modal" id="fpModal">
    <div class="fp-modal-box">
        <div class="fp-modal-head bg-primary">
            <h5 id="fpModalTitle">Customer details</h5>
            <button type="button" class="fp-modal-close" onclick="fpCloseModal()">&times;</button>
        </div>
        <div class="fp-modal-body" id="fpModalBody">
            <div class="fp-loading">Loading…</div>
        </div>
    </div>
</div>

@include('components.backend.footer')
@include('components.backend.main-js')

<script>
const FP_SHOW_URL = @js(url('/fastpay/customers'));

function fpCloseModal(){ document.getElementById('fpModal').classList.remove('open'); }

function fpViewCustomer(ref){
    document.getElementById('fpModalTitle').innerText = 'Customer details — ' + ref;
    document.getElementById('fpModalBody').innerHTML = '<div class="fp-loading">Loading…</div>';
    document.getElementById('fpModal').classList.add('open');

    fetch(FP_SHOW_URL + '/' + encodeURIComponent(ref), {headers:{'Accept':'application/json'}})
        .then(r => r.json())
        .then(res => {
            if(!res.success){ throw new Error(res.message || 'Failed to load'); }
            document.getElementById('fpModalBody').innerHTML = fpRender(res.customer, res.accounts, res.transactions);
        })
        .catch(e => {
            document.getElementById('fpModalBody').innerHTML =
                '<div class="alert alert-danger" style="font-size:13.5px;">⚠️ ' + e.message + '</div>';
        });
}

function fpEsc(s){ return (s==null?'':String(s)).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

function fpRender(cust, accounts, txns){
    let html = '';
    if(cust){
        html += '<div class="fp-meta"><b>DD Reference:</b> ' + fpEsc(cust.DDReference)
              + ' &nbsp;|&nbsp; <b>Status:</b> ' + fpEsc(cust.Status)
              + ' &nbsp;|&nbsp; <b>Current Collection Total:</b> £' + Number(cust.CurrentCollectionTotal||0).toFixed(2)
              + '</div>';
    }

    // Accounts section (like FastPay)
    html += '<p class="fp-sub">Accounts</p>';
    if(accounts && accounts.length){
        html += '<div class="table-responsive custom-scrollbar"><table class="table table-bordered"><thead><tr>'
              + '<th>Sort Code</th><th>Account Number</th><th>Account Name</th><th>From</th><th>Status</th>'
              + '</tr></thead><tbody>';
        accounts.forEach(a => {
            const sc = 'fp-' + (a.status||'').toLowerCase();
            html += '<tr>'
                + '<td>' + fpEsc(a.sort_code) + '</td>'
                + '<td>' + fpEsc(a.account_number) + '</td>'
                + '<td>' + fpEsc(a.account_name) + '</td>'
                + '<td>' + fpEsc(a.from) + '</td>'
                + '<td><span class="fp-badge ' + sc + '">' + fpEsc(a.status) + '</span></td>'
                + '</tr>';
        });
        html += '</tbody></table></div>';
    } else {
        html += '<div class="alert alert-light" style="font-size:13.5px;">No account information.</div>';
    }

    // Transactions section
    html += '<p class="fp-sub">Transactions</p>';
    if(!txns || !txns.length){
        html += '<div class="alert alert-light" style="font-size:13.5px;">No transactions for this customer yet.</div>';
        return html;
    }
    html += '<div class="table-responsive custom-scrollbar"><table class="table table-bordered"><thead><tr>'
          + '<th>Submission Date</th><th>Amount</th><th>Bacs Type</th><th>Status</th>'
          + '</tr></thead><tbody>';
    txns.forEach(t => {
        const sc = t.status === 'Paid' ? 'fp-paid' : (t.status === 'Failed' ? 'fp-failed' : 'fp-suspended');
        html += '<tr>'
            + '<td>' + fpEsc(t.submission_date) + '</td>'
            + '<td>£' + Number(t.amount||0).toFixed(2) + '</td>'
            + '<td>' + fpEsc(t.bacs_code) + '</td>'
            + '<td><span class="fp-badge ' + sc + '">' + fpEsc(t.status) + '</span></td>'
            + '</tr>';
    });
    html += '</tbody></table></div>';
    return html;
}

document.getElementById('fpModal').addEventListener('click', function(e){
    if(e.target === this) fpCloseModal();
});
</script>

</body>
</html>
