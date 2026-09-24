<!doctype html>
<html lang="en">
<head>@include('components.backend.head')</head>
@include('components.backend.header')
@include('components.backend.sidebar')

<style>
*{box-sizing:border-box;}
.table td,.table th{vertical-align:middle;}
.table-responsive{overflow-x:auto;border-radius:8px;}
.table{margin-bottom:0;font-size:13px;}
.table thead th{font-size:11px;font-weight:700;letter-spacing:.4px;white-space:nowrap;padding:10px 12px;}
.table tbody td{padding:10px 12px;vertical-align:top;}
.table tbody tr:hover{background:#f8faff;}
.card{border:none;box-shadow:0 1px 4px rgba(0,0,0,.08);border-radius:12px;}
.card-body{padding:24px;}
.th-ob{background:#eff6ff !important;color:#1e40af !important;border-bottom:2px solid #bfdbfe !important;}
.th-rn{background:#f0fdf4 !important;color:#15803d !important;border-bottom:2px solid #bbf7d0 !important;}
.phase-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;white-space:nowrap;}
.phase-badge .dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.phase-badge.onboarding{background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;}
.phase-badge.onboarding .dot{background:#2563eb;}
.phase-badge.renewal{background:#dcfce7;color:#166534;border:1px solid #86efac;}
.phase-badge.renewal .dot{background:#16a34a;}
.info-pill{display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:600;padding:2px 7px;border-radius:10px;margin-top:4px;white-space:nowrap;}
.info-pill.green{background:#dcfce7;color:#166534;border:1px solid #86efac;}
.info-pill.amber{background:#fef3c7;color:#92400e;border:1px solid #fcd34d;}
.info-pill.red{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
.info-pill.blue{background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;}
select.status-change,select.renewal-change{font-weight:700;font-size:12px;padding:5px 10px;border-radius:6px;border:1.5px solid #e5e7eb;width:100% !important;cursor:pointer;}
select.status-change:focus,select.renewal-change:focus{outline:none;box-shadow:0 0 0 2px rgba(59,130,246,.25);}
select option{color:#111;background:#fff;font-weight:400;}
select.status-change.active{background:#16a34a;color:#fff;border-color:#15803d;}
select.status-change.inactive{background:#dc2626;color:#fff;border-color:#b91c1c;}
select.renewal-change.renewal_due{background:#d97706;color:#fff;border-color:#b45309;}
select.renewal-change.due{background:#15803d;color:#fff;border-color:#166534;}
.editable-wrapper{display:flex;align-items:center;border:1.5px solid #e5e7eb;border-radius:6px;overflow:hidden;background:#fff;transition:border-color .15s;}
.editable-wrapper:hover{border-color:#3b82f6;}
.editable-wrapper .igt{border:none;background:#f9fafb;padding:4px 7px;font-size:11px;color:#6b7280;}
.editable-field{border:none!important;width:100%;padding:4px 7px;font-size:12px;background:transparent;}
.editable-field:focus{outline:none;}
.editable-field.changed{background:#fefce8;}
.edit-icon{cursor:pointer;padding:0 6px;color:#d1d5db;background:#f9fafb;}
.editable-wrapper:hover .edit-icon{color:#3b82f6;}
.lock-note{font-size:10px;color:#9ca3af;margin-top:3px;display:flex;align-items:center;gap:2px;white-space:nowrap;}
.date-input{width:100%;font-size:12px;padding:5px 8px;border:1.5px solid #e5e7eb;border-radius:6px;background:#fff;}
.date-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 2px rgba(59,130,246,.15);}
.mail-cell{display:flex;flex-direction:column;gap:5px;}
.mail-btn{width:100%;font-size:11px;font-weight:600;padding:6px 10px;border-radius:6px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;transition:opacity .15s;}
.mail-btn:disabled{opacity:.5;cursor:not-allowed;}
.mail-btn:hover:not(:disabled){opacity:.88;}
.mail-btn.green{background:#16a34a;color:#fff;}
.mail-btn.amber{background:#d97706;color:#fff;}
.mail-btn.red{background:#dc2626;color:#fff;}
.mail-btn.gray{background:#6b7280;color:#fff;}
.mail-sent-box{display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#15803d;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:5px 8px;}
.mail-locked-box{display:flex;align-items:center;gap:4px;font-size:10px;color:#9ca3af;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:5px 8px;justify-content:center;font-weight:500;}
.mail-done-box{display:flex;align-items:center;gap:4px;font-size:11px;color:#15803d;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:5px 8px;justify-content:center;font-weight:600;}
.renewal-info{border-radius:6px;padding:7px 10px;margin-top:5px;font-size:10px;line-height:1.5;}
.renewal-info.overdue{background:#fff1f2;border:1px solid #fca5a5;color:#7f1d1d;}
.renewal-info.due-soon{background:#fffbeb;border:1px solid #fcd34d;color:#78350f;}
.renewal-info.done{background:#f0fdf4;border:1px solid #86efac;color:#14532d;}
.lifecycle-lock{display:flex;align-items:center;gap:5px;background:#fffbeb;border:1px solid #fcd34d;border-radius:6px;padding:4px 8px;font-size:10px;font-weight:600;color:#78350f;margin-top:4px;}
.summary-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;}
.s-pill{border:1.5px solid #e5e7eb;border-radius:20px;padding:5px 14px;font-size:12px;font-weight:600;color:#374151;cursor:pointer;transition:all .15s;user-select:none;background:#fff;display:flex;align-items:center;gap:5px;}
.s-pill:hover{border-color:#6b7280;background:#f9fafb;}
.s-pill .n{font-size:14px;font-weight:800;}
.s-pill.c-blue .n{color:#1d4ed8;}.s-pill.c-green .n{color:#15803d;}
.s-pill.c-red .n{color:#dc2626;}.s-pill.c-yellow .n{color:#d97706;}
.s-pill.c-gray .n{color:#374151;}
.filter-bar{background:#f8faff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:20px;}
.filter-bar .form-label{font-size:11px;font-weight:600;color:#374151;margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px;}
.filter-bar .form-select,.filter-bar .form-control{font-size:12px;border:1.5px solid #e5e7eb;border-radius:6px;}
.legend-strip{display:flex;gap:16px;flex-wrap:wrap;padding:10px 16px;background:#f8faff;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:16px;font-size:11px;color:#374151;}
.legend-strip span{display:flex;align-items:center;gap:5px;font-weight:500;}
.price-wrap{display:flex;align-items:center;border:1.5px solid #e5e7eb;border-radius:6px;overflow:hidden;background:#fff;}
.price-prefix{padding:4px 8px;background:#f9fafb;font-size:12px;color:#6b7280;font-weight:600;border-right:1px solid #e5e7eb;}
.price-input{border:none!important;width:70px;padding:4px 7px;font-size:12px;}
.price-input:focus{outline:none;}
.end-date-label{font-size:10px;color:#6b7280;font-weight:600;margin-bottom:2px;display:block;}
.inactive-info-box{background:#fff1f2;border:1px solid #fca5a5;border-radius:6px;padding:6px 9px;font-size:11px;color:#991b1b;font-weight:600;line-height:1.6;margin-top:6px;}
.inactive-info-box .i-since{font-size:10px;color:#6b7280;font-weight:500;margin-top:2px;}
.inactive-info-box .i-since strong{color:#b91c1c;}
.col-phase{min-width:150px;max-width:170px;}
.col-status{min-width:200px;max-width:230px;}
.col-date{min-width:175px;max-width:200px;}
.col-mail{min-width:170px;max-width:200px;}
.col-rn-status{min-width:200px;max-width:230px;}
.col-price{min-width:110px;}
.col-delete{min-width:120px;max-width:140px;}
.btn-delete-member{font-size:11px;font-weight:600;padding:6px 10px;border-radius:6px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;width:100%;transition:opacity .15s;background:#dc2626;color:#fff;}
.btn-delete-member:hover:not(:disabled){opacity:.85;}
.btn-delete-member:disabled{background:#e5e7eb;color:#9ca3af;cursor:not-allowed;opacity:1;}
.delete-lock-note{font-size:10px;color:#9ca3af;margin-top:4px;display:flex;align-items:center;gap:3px;flex-wrap:wrap;}
.delete-active-note{font-size:10px;color:#d1d5db;margin-top:2px;text-align:center;}
.btn { padding: 0.3rem 0.2rem; }
select.plan-change{border:1.5px solid #e5e7eb;border-radius:6px;padding:5px 10px;font-size:12px;font-weight:600;cursor:pointer;width:100%;transition:all .15s;}
select.plan-change:focus{outline:none;box-shadow:0 0 0 2px rgba(59,130,246,.25);}
select.plan-change.Monthly{background:#dbeafe;color:#1e40af;border-color:#93c5fd;}
select.plan-change.Yearly{background:#ede9fe;color:#5b21b6;border-color:#c4b5fd;}
</style>

<div class="page-body">
 <div class="container-fluid">
  <div class="page-title">
   <div class="row"><div class="col-6"></div>
    <div class="col-6">
     <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.html"><svg class="stroke-icon"><use href="../assets/svg/icon-sprite.svg#stroke-home"></use></svg></a></li>
     </ol>
    </div>
   </div>
  </div>
 </div>

 <div class="container-fluid">
  <div class="row"><div class="col-sm-12"><div class="card"><div class="card-body">

   <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
     <h5 class="mb-0 fw-bold" style="font-size:18px;">Membership Management</h5>
     <p class="text-muted mb-0 mt-1" style="font-size:12px;">Manage member onboarding, renewal and mail communications</p>
    </div>
    <nav aria-label="breadcrumb">
     <ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="{{ route('customer-elctronic.details') }}">Home</a></li>
      <li class="breadcrumb-item active">Members</li>
     </ol>
    </nav>
   </div>

   <div class="legend-strip">
    <span><span style="width:10px;height:10px;border-radius:50%;background:#2563eb;display:inline-block;"></span> Onboarding — Set Active → Send mail with PDF</span>
    <span><span style="width:10px;height:10px;border-radius:50%;background:#fcd34d;display:inline-block;"></span> Active — Admin can update status and end_date anytime</span>
    <span><span style="width:10px;height:10px;border-radius:50%;background:#16a34a;display:inline-block;"></span> Renewal Phase — Auto-detected; past-due rows flagged visually</span>
    <span><span style="width:10px;height:10px;border-radius:50%;background:#dc2626;display:inline-block;"></span> Delete — Admin can manually delete any member at any time</span>
   </div>

   @php
    $cActive     = $memberships->where('status','active')->count();
    $cInactive   = $memberships->where('status','inactive')->count();
    $cRenewDue   = $memberships->where('renewal_status','renewal_due')->count();
    $cOnboarding = $memberships->filter(fn($m)=>!$m->start_date||\Carbon\Carbon::parse($m->start_date)->addYear()->isFuture())->count();
    $cRenewal    = $memberships->count()-$cOnboarding;
   @endphp

   <div class="summary-bar">
    <div class="s-pill c-blue"   onclick="quickFilter('phase','onboarding')"><span class="n">{{ $cOnboarding }}</span> Onboarding</div>
    <div class="s-pill c-green"  onclick="quickFilter('phase','renewal')"><span class="n">{{ $cRenewal }}</span> Renewal Phase</div>
    <div class="s-pill c-green"  onclick="quickFilter('status','active')"><span class="n">{{ $cActive }}</span> Active</div>
    <div class="s-pill c-red"    onclick="quickFilter('status','inactive')"><span class="n">{{ $cInactive }}</span> Inactive</div>
    <div class="s-pill c-yellow" onclick="quickFilter('renewal','renewal_due')"><span class="n">{{ $cRenewDue }}</span> Renewal Due</div>
    <div class="s-pill c-gray"   onclick="resetFilters()"><span class="n">{{ $memberships->count() }}</span> All Members</div>
   </div>

   <div class="filter-bar">
    <div class="row g-3 align-items-end">
     <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
      <label class="form-label">Renewal Status</label>
      <select id="filter-renewal-status" class="form-select form-select-sm">
       <option value="">All Statuses</option>
       <option value="renewal_due">Send Renewal</option>
       <option value="due">Completed</option>
      </select>
     </div>
     <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
      <label class="form-label">Renewal From</label>
      <input type="date" id="filter-renewal-from" class="form-control form-control-sm">
     </div>
     <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
      <label class="form-label">Renewal To</label>
      <input type="date" id="filter-renewal-to" class="form-control form-control-sm">
     </div>
     <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
      <label class="form-label">Member Status</label>
      <select id="filter-status" class="form-select form-select-sm">
       <option value="">All Statuses</option>
       <option value="active">Active</option>
       <option value="inactive">Inactive</option>
      </select>
     </div>
     <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
      <label class="form-label">Payment Plan</label>
      <select id="filter-payment-plan" class="form-select form-select-sm">
       <option value="">All Plans</option>
       <option value="Monthly">Monthly</option>
       <option value="Yearly">Annual / Yearly</option>
      </select>
     </div>
     <div class="col-xl-10 col-lg-9 col-md-8">
      <label class="form-label" style="visibility:hidden;">Actions</label>
      <div class="d-flex gap-2">
       <button id="apply-filter" class="btn btn-primary btn-sm flex-fill" style="font-size:12px;font-weight:600;">Apply Filter</button>
       <button id="reset-filter" class="btn btn-outline-secondary btn-sm flex-fill" onclick="resetFilters()" style="font-size:12px;">Reset</button>
       <a href="#" id="export-csv"    class="btn btn-outline-dark btn-sm flex-fill"    style="font-size:10px;">Export CSV</a>
       <a href="#" id="export-report" class="btn btn-outline-success btn-sm flex-fill" style="font-size:10px;">Report</a>
      </div>
     </div>
    </div>
   </div>

   <div class="table-responsive">
    <table class="table table-bordered" id="basic-1">
     <thead>
      <tr style="background:#f8faff;">
       <th rowspan="2" style="vertical-align:middle;min-width:36px;">#</th>
       <th rowspan="2" style="vertical-align:middle;min-width:110px;">DD Ref</th>
       <th rowspan="2" style="vertical-align:middle;min-width:80px;">DD Form</th>
       <th rowspan="2" style="vertical-align:middle;min-width:140px;">Name</th>
       <th rowspan="2" style="vertical-align:middle;min-width:150px;">Account Name</th>
       <th rowspan="2" style="vertical-align:middle;min-width:110px;">Sort Code</th>
       <th rowspan="2" style="vertical-align:middle;min-width:130px;">Account No.</th>
       <th rowspan="2" style="vertical-align:middle;min-width:90px;">Submitted</th>
       <th rowspan="2" style="vertical-align:middle;min-width:60px;">PDF</th>
       <th rowspan="2" class="col-phase" style="vertical-align:middle;">Phase</th>
       <th colspan="3" class="th-ob text-center">🔵 ONBOARDING PHASE</th>
       <th colspan="3" class="th-rn text-center">🟢 RENEWAL PHASE</th>
       <th rowspan="2" class="col-price" style="vertical-align:middle;">Price</th>
       <th rowspan="2" style="vertical-align:middle;min-width:120px;">Plan</th>
       <th rowspan="2" class="col-delete" style="vertical-align:middle;">Delete</th>
      </tr>
      <tr>
       <th class="th-ob col-status">Status</th>
       <th class="th-ob col-date">Start / End Date</th>
       <th class="th-ob col-mail">Status Mail</th>
       <th class="th-rn col-date">Renewal Date</th>
       <th class="th-rn col-rn-status">Renewal Status</th>
       <th class="th-rn col-mail">Renewal Mail</th>
      </tr>
     </thead>
     <tbody>

@forelse($memberships as $key => $member)
@php
 $step1   = is_array($member->step1)        ? $member->step1        : json_decode($member->step1,true);
 $step2   = is_array($member->step2)        ? $member->step2        : json_decode($member->step2,true);
 $payment = is_array($member->step1_signup) ? $member->step1_signup : json_decode($member->step1_signup,true);

 $fullName = trim(implode(' ',array_filter([
     data_get($step1,'title'),data_get($step1,'first_name'),
     data_get($step1,'middle_name'),data_get($step1,'last_name'),
 ])));

 $expiry         = $member->start_date ? \Carbon\Carbon::parse($member->start_date)->addYear() : null;
 $isRenewalPhase = $expiry && $expiry->isPast();
 $startFmt       = $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('d M Y') : null;

 // ── END DATE ─────────────────────────────────────────────────────────────
 // Driven by the RENEWAL DATE (the imported next_renewal_date), NOT by
 // start + 1 year: for imported members start_date is the original join date
 // (e.g. 2013 / 2018), so start+1y showed a long-expired end date while the
 // real cover runs up to the next renewal. Cover ends the day BEFORE the
 // next renewal date — next renewal 26-11-2026 → end date 25-11-2026.
 // Only when there is no renewal date at all do we fall back to start + 1 year − 1 day.
 $endDate       = $member->end_date;
 $renewalAnchor = $member->renewal_date ? \Carbon\Carbon::parse($member->renewal_date) : null;

 if ($renewalAnchor) {
     $endDateAuto = $renewalAnchor->copy()->subDay();
 } elseif ($member->start_date) {
     $endDateAuto = \Carbon\Carbon::parse($member->start_date)->addYear()->subDay();
 } elseif ($endDate) {
     $endDateAuto = \Carbon\Carbon::parse($endDate);
 } else {
     $endDateAuto = null;
 }

 // A stored end_date only wins when it is a real admin override, i.e. it
 // matches neither the renewal-derived end nor the start+1y variants.
 $endDateDisplay = $endDateAuto;

 if ($endDate && $endDateAuto) {
     $storedEnd  = \Carbon\Carbon::parse($endDate);
     $autoValues = [$endDateAuto];
     if ($member->start_date) {
         $autoValues[] = \Carbon\Carbon::parse($member->start_date)->addYear();
         $autoValues[] = \Carbon\Carbon::parse($member->start_date)->addYear()->subDay();
     }
     if ($renewalAnchor) {
         $autoValues[] = $renewalAnchor->copy();
     }

     $isAutoValue = false;
     foreach ($autoValues as $candidate) {
         if ($storedEnd->isSameDay($candidate)) { $isAutoValue = true; break; }
     }
     if (!$isAutoValue) $endDateDisplay = $storedEnd;
 }

 $endDateFmt = $endDateDisplay ? $endDateDisplay->format('d M Y') : null;
 $endDateVal = $endDateDisplay ? $endDateDisplay->format('Y-m-d') : '';
 // ─────────────────────────────────────────────────────────────────────────

 $showDuration = ($startFmt && $endDateFmt);

 $isInactive = ($member->status === 'inactive');

 $mailState = 'pending';
 if ($isInactive)               { $mailState = 'inactive_nomail'; }
 elseif ($member->mail_sent_at) { $mailState = 'active_sent'; }

 $statusMailLocked = ($isRenewalPhase && !$isInactive);
 $rMailState = $member->renewal_mail_sent_at ? 'sent' : 'pending';
 $displayRenewalStatus = $member->renewal_status === 'overdue' ? 'renewal_due' : $member->renewal_status;

 $daysUntilRenewal = null; $renewalDateFmt = null;
 if($isRenewalPhase && $member->renewal_date){
     $daysUntilRenewal = (int)now()->diffInDays(\Carbon\Carbon::parse($member->renewal_date),false);
     $renewalDateFmt   = \Carbon\Carbon::parse($member->renewal_date)->format('d M Y');
 }
 $isPastDue = ($displayRenewalStatus === 'renewal_due' && $daysUntilRenewal !== null && $daysUntilRenewal < 0);

 $inactiveAtFmt = $member->inactive_at
     ? \Carbon\Carbon::parse($member->inactive_at)->format('d M Y') : null;

 $inactiveYears = ($isInactive && $member->inactive_at)
     ? (int)\Carbon\Carbon::parse($member->inactive_at)->diffInYears(now()) : null;
 $canDelete = $isInactive && $member->inactive_at && $inactiveYears >= 3;

 $rawPlan = strtolower(trim((string) data_get($payment, 'payment_plan', '')));
 if (str_contains($rawPlan, 'monthly')) {
     $planValue = 'Monthly';
 } elseif (str_contains($rawPlan, 'yearly') || str_contains($rawPlan, 'annual')) {
     $planValue = 'Yearly';
 } else {
     $planValue = '';
 }
@endphp

<tr
 data-id="{{ $member->id }}"
 data-phase="{{ $isRenewalPhase ? 'renewal' : 'onboarding' }}"
 data-status="{{ $member->status }}"
 data-mail-sent="{{ $member->mail_sent_at ? '1' : '0' }}"
 data-expiry-fmt="{{ $endDateFmt }}"
 data-renewal-status="{{ $displayRenewalStatus }}"
 data-payment-plan="{{ $planValue }}"
 data-renewal-date="{{ $member->renewal_date ? \Carbon\Carbon::parse($member->renewal_date)->format('Y-m-d') : '' }}"
 data-past-due="{{ $isPastDue ? '1' : '0' }}"
>
 <td class="text-muted sno" style="font-size:11px;font-weight:600;">{{ $key+1 }}</td>
 <td><span class="fw-bold text-primary" style="font-size:12px;">{{ $member->dd_reference ?? '—' }}</span></td>
 <td>
  @if(!empty($payment['file_name']))
   <a href="{{ asset('direct-debit/'.$payment['file_name']) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:11px;">📄 View</a>
  @else <span class="text-muted" style="font-size:12px;">—</span>
  @endif
 </td>
 <td><strong style="font-size:13px;">{{ $fullName ?: '—' }}</strong></td>
 <td>
  <div class="editable-wrapper">
   <span class="igt"><i class="fa fa-user" style="font-size:11px;color:#6b7280;"></i></span>
   <input type="text" class="editable-field" data-id="{{ $member->id }}" data-field="account_holder" value="{{ data_get($payment,'account_holder','') }}" placeholder="Account holder">
   <span class="edit-icon"><i class="fa fa-edit" style="font-size:11px;"></i></span>
  </div>
 </td>
 <td>
  <div class="editable-wrapper">
   <span class="igt"><i class="fa fa-university" style="font-size:11px;color:#d97706;"></i></span>
   <input type="text" class="editable-field" data-id="{{ $member->id }}" data-field="sort_code" value="{{ data_get($payment,'sort_code','') }}" placeholder="00-00-00">
   <span class="edit-icon"><i class="fa fa-edit" style="font-size:11px;"></i></span>
  </div>
 </td>
 <td>
  <div class="editable-wrapper">
   <span class="igt"><i class="fa fa-credit-card" style="font-size:11px;color:#16a34a;"></i></span>
   <input type="text" class="editable-field" data-id="{{ $member->id }}" data-field="account_number" value="{{ data_get($payment,'account_number','') }}" placeholder="Account number">
   <span class="edit-icon"><i class="fa fa-edit" style="font-size:11px;"></i></span>
  </div>
 </td>
 <td style="font-size:11px;color:#374151;white-space:nowrap;">
  {{ $member->submitted_at ? $member->submitted_at->format('d M Y') : $member->created_at->format('d M Y') }}
 </td>
 <td>
  <a href="{{ route('admin.member.pdf',$member->id) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;">📄 PDF</a>
 </td>

 {{-- PHASE --}}
 <td class="col-phase">
  @if($isRenewalPhase)
   <span class="phase-badge renewal"><span class="dot"></span>Renewal</span>
   @if($endDateFmt)<div style="font-size:10px;color:#991b1b;margin-top:4px;font-weight:600;">Expired: {{ $endDateFmt }}</div>@endif
   @if($isPastDue)
    <span class="info-pill red mt-1">🔴 Past due</span>
   @elseif($displayRenewalStatus==='renewal_due' && $daysUntilRenewal!==null)
    <span class="info-pill amber mt-1">⚠ {{ $daysUntilRenewal }}d left</span>
   @elseif($displayRenewalStatus==='due')
    <span class="info-pill green mt-1">✅ Renewed</span>
   @endif
  @else
   @if($member->mail_sent_at && $member->status==='active')
    <span class="phase-badge onboarding" style="background:#dcfce7;color:#166534;border-color:#86efac;"><span class="dot" style="background:#16a34a;"></span>Onboarded</span>
   @else
    <span class="phase-badge onboarding"><span class="dot"></span>Onboarding</span>
   @endif
   @if($endDateFmt)<div class="lifecycle-lock mt-1">🔒 Until {{ $endDateFmt }}</div>@endif
  @endif
 </td>

 {{-- STATUS --}}
 <td class="col-status">
  <select class="form-select form-select-sm status-change member-status" data-id="{{ $member->id }}" style="font-size:12px;">
   <option value="">— Select —</option>
   <option value="active"   {{ $member->status==='active'   ? 'selected':'' }}>Active</option>
   <option value="inactive" {{ $member->status==='inactive' ? 'selected':'' }}>Inactive</option>
  </select>
  @if($member->status==='active' && $endDateFmt)
   <div class="lock-note mt-1" style="color:#15803d;">✅ Active until {{ $endDateFmt }}</div>
  @endif
  <div class="inactive-info-box" id="inactive-info-{{ $member->id }}"
   style="{{ !$isInactive ? 'display:none;':'' }}">
   ⚠ Your membership is inactive
   <div class="i-since">📅 Inactive since:
    <strong id="inactive-date-{{ $member->id }}">{{ $inactiveAtFmt ?? '—' }}</strong>
   </div>
  </div>
 </td>

 {{-- START / END DATE --}}
 <td class="col-date">
  <input type="date" class="date-input start-date-input" data-id="{{ $member->id }}"
   value="{{ $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('Y-m-d') : '' }}">
  <div class="mt-2">
   <span class="end-date-label">🔒 End Date</span>
   <input type="date" class="date-input end-date-input" data-id="{{ $member->id }}" value="{{ $endDateVal }}">
  </div>
  @if($showDuration)
   <div class="info-pill blue mt-1" style="font-size:9px;">{{ $startFmt }} → {{ $endDateFmt }}</div>
  @endif
 </td>

 {{-- STATUS MAIL --}}
 <td class="col-mail">
  <div class="mail-cell" id="mail-cell-{{ $member->id }}">
   @if($statusMailLocked)
    <div class="mail-locked-box">🔒 Mail Locked — renewal phase</div>
   @elseif($mailState === 'inactive_nomail')
    <div class="mail-locked-box">— No mail for inactive —</div>
   @elseif($mailState === 'active_sent')
    <div class="mail-sent-box" id="mail-sent-text-{{ $member->id }}">✅ Mail sent</div>
   @else
    <button class="mail-btn gray btn-send-mail"
     data-id="{{ $member->id }}" id="send-mail-btn-{{ $member->id }}">
     📧 Send Mail
    </button>
    <span class="d-none mail-sent-box" id="mail-sent-text-{{ $member->id }}">✅ Mail sent</span>
   @endif
  </div>
 </td>

 {{-- RENEWAL DATE --}}
 <td class="col-date">
  <input type="date" class="date-input renewal-date" data-id="{{ $member->id }}"
   value="{{ $member->renewal_date ? \Carbon\Carbon::parse($member->renewal_date)->format('Y-m-d') : '' }}">
  @if($daysUntilRenewal!==null)
   @if($daysUntilRenewal<0)       <span class="info-pill red mt-1">🔴 Past due by {{ abs($daysUntilRenewal) }}d</span>
   @elseif($daysUntilRenewal===0) <span class="info-pill red mt-1">⚠ Due today!</span>
   @elseif($daysUntilRenewal<=21) <span class="info-pill amber mt-1">⚠ {{ $daysUntilRenewal }}d left</span>
   @else                          <span class="info-pill green mt-1">✓ {{ $daysUntilRenewal }}d left</span>
   @endif
  @endif
 </td>

 {{-- RENEWAL STATUS --}}
 <td class="col-rn-status">
  <select class="form-select form-select-sm renewal-change renewal-status" data-id="{{ $member->id }}" style="font-size:12px;">
   <option value="">— Select —</option>
   <option value="renewal_due" {{ $displayRenewalStatus==='renewal_due' ? 'selected':'' }}>Send Renewal</option>
   <option value="due"         {{ $displayRenewalStatus==='due'         ? 'selected':'' }}>Renewal Completed</option>
  </select>
  @if($displayRenewalStatus==='renewal_due')
   @if($isPastDue)
    <div class="renewal-info overdue mt-1">
     <strong>🔴 Past due by {{ abs($daysUntilRenewal) }}d</strong> — was due {{ $renewalDateFmt }}
    </div>
   @else
    <div class="renewal-info due-soon mt-1">
     <strong>⚠ Due in {{ $daysUntilRenewal }}d</strong> — {{ $renewalDateFmt }}
    </div>
   @endif
  @elseif($displayRenewalStatus==='due')
   <span class="info-pill green mt-1">✅ Renewal completed</span>
  @endif
 </td>

 {{-- RENEWAL MAIL --}}
 <td class="col-mail">
  <div class="mail-cell" id="renewal-mail-cell-{{ $member->id }}">
   @if($displayRenewalStatus==='due')
    <div class="mail-locked-box">— Renewal completed —</div>
   @elseif($rMailState==='sent')
    <div class="mail-done-box" id="renewal-mail-sent-text-{{ $member->id }}">✅ Renewal mail sent</div>
   @else
    @if($displayRenewalStatus==='renewal_due' && $isPastDue)
     <button class="mail-btn red btn-renewal-mail" data-id="{{ $member->id }}" id="renewal-mail-btn-{{ $member->id }}">🔴 Send Renewal Mail</button>
    @elseif($displayRenewalStatus==='renewal_due')
     <button class="mail-btn amber btn-renewal-mail" data-id="{{ $member->id }}" id="renewal-mail-btn-{{ $member->id }}">📧 Send Renewal Mail</button>
    @else
     <button class="mail-btn gray btn-renewal-mail" data-id="{{ $member->id }}" id="renewal-mail-btn-{{ $member->id }}" disabled>📧 Send Renewal Mail</button>
    @endif
    <span class="d-none mail-done-box" id="renewal-mail-sent-text-{{ $member->id }}">✅ Renewal mail sent</span>
   @endif
  </div>
 </td>

 {{-- PRICE --}}
 <td class="col-price">
  <div class="price-wrap">
   <span class="price-prefix">£</span>
   <input type="number" min="0" step="0.01" class="price-input" data-id="{{ $member->id }}" value="{{ $member->price ?? 0 }}">
  </div>
 </td>

 {{-- PLAN --}}
 <td style="min-width:120px;">
  <select class="form-select form-select-sm plan-change"
          data-id="{{ $member->id }}"
          data-prev="{{ $planValue }}">
   <option value="">— Select —</option>
   <option value="Monthly" {{ $planValue === 'Monthly' ? 'selected' : '' }}>Monthly</option>
   <option value="Yearly"  {{ $planValue === 'Yearly'  ? 'selected' : '' }}>Yearly</option>
  </select>
 </td>

 {{-- DELETE (manual — admin can delete any member at any time) --}}
 <td class="col-delete">
  <button class="btn-delete-member" data-id="{{ $member->id }}" data-name="{{ addslashes($fullName) }}">
   🗑 Delete
  </button>
 </td>

</tr>
@empty
<tr><td colspan="21" class="text-center py-5 text-muted">No membership records found.</td></tr>
@endforelse
     </tbody>
    </table>
   </div>

  </div></div></div></div>
 </div>
</div>

@include('components.backend.footer')
@include('components.backend.main-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

 var Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:3000, timerProgressBar:true });
 var todayStr = new Date().toISOString().split('T')[0];

 function rowOf(id)      { return document.querySelector("tr[data-id='"+id+"']"); }
 function getPhase(id)   { var r=rowOf(id); return r?r.dataset.phase:'onboarding'; }
 function getStatus(id)  { var r=rowOf(id); return r?r.dataset.status:''; }
 function isMailSent(id) { var r=rowOf(id); return r?r.dataset.mailSent==='1':false; }
 function isPastDue(id)  { var r=rowOf(id); return r?r.dataset.pastDue==='1':false; }

 function formatDate(str){
  if(!str) return '';
  var m=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  var d=new Date(str+'T00:00:00');
  return d.getDate()+' '+m[d.getMonth()]+' '+d.getFullYear();
 }

 function isCompleteDate(val){
  if(!val) return true;
  var parts = val.split('-');
  if(parts.length !== 3) return false;
  if(parts[0].length !== 4) return false;
  var year  = parseInt(parts[0], 10);
  var month = parseInt(parts[1], 10);
  var day   = parseInt(parts[2], 10);
  if(!year || !month || !day) return false;
  if(year < 1000) return false;
  return true;
 }

 // End date = start + 1 year − 1 day
 function calcEndDate(startStr){
  if(!startStr) return '';
  var parts = startStr.split('-');
  if(parts.length !== 3) return '';
  var y = parseInt(parts[0],10);
  var m = parseInt(parts[1],10);
  var d = parseInt(parts[2],10);
  if(!y||!m||!d) return '';
  var dt = new Date(Date.UTC(y+1, m-1, d));
  dt.setUTCDate(dt.getUTCDate() - 1);
  var yy = dt.getUTCFullYear();
  var mm = String(dt.getUTCMonth()+1).padStart(2,'0');
  var dd = String(dt.getUTCDate()).padStart(2,'0');
  return yy+'-'+mm+'-'+dd;
 }

 function recomputePastDue(id){
  var r=rowOf(id); if(!r) return false;
  var inp=document.querySelector(".renewal-date[data-id='"+id+"']");
  var rs =document.querySelector(".renewal-status[data-id='"+id+"']");
  if(!inp||!inp.value){ r.dataset.pastDue='0'; return false; }
  if(rs && rs.value!=='renewal_due'){ r.dataset.pastDue='0'; return false; }
  var today=new Date(); today.setHours(0,0,0,0);
  var d=new Date(inp.value+'T00:00:00');
  var past=d<today;
  r.dataset.pastDue=past?'1':'0';
  return past;
 }

 function handleFetch(url,payload){
  return fetch(url,{
   method:'POST',
   headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
   body:JSON.stringify(payload)
  })
  .then(r=>r.json())
  .then(function(data){
   Toast.fire({icon:data.success?'success':'error',title:data.message||(data.success?'Updated':'Failed')});
   return data;
  })
  .catch(function(){ Toast.fire({icon:'error',title:'Request failed'}); });
 }

 function showInactiveNoMail(id){
  var mc=document.getElementById('mail-cell-'+id);
  if(!mc) return;
  mc.innerHTML='<div class="mail-locked-box">— No mail for inactive —</div>';
 }

 function evaluateSendMailBtn(id){
  var btn=document.getElementById('send-mail-btn-'+id);
  if(!btn) return;
  var sentBox=document.getElementById('mail-sent-text-'+id);
  if(sentBox && !sentBox.classList.contains('d-none')) return;
  var ss=document.querySelector(".member-status[data-id='"+id+"']");
  var si=document.querySelector(".start-date-input[data-id='"+id+"']");
  var status=ss?ss.value:'', sd=si?si.value:'';
  if(status==='active' && sd){
   btn.disabled=false; btn.className='mail-btn green btn-send-mail'; btn.textContent='📧 Send Mail'; btn.onclick=null;
  } else if(status==='active' && !sd){
   btn.disabled=false; btn.className='mail-btn green btn-send-mail'; btn.textContent='📧 Send Mail';
   btn.onclick=function(){ Swal.fire({icon:'warning',title:'Start Date Required',text:'Please set a start date before sending mail.'}); };
  } else {
   btn.disabled=true; btn.className='mail-btn gray btn-send-mail'; btn.textContent='📧 Send Mail'; btn.onclick=null;
  }
 }

 function evaluateRenewalMailBtn(id){
  var btn=document.getElementById('renewal-mail-btn-'+id);
  var sent=document.getElementById('renewal-mail-sent-text-'+id);
  if(!btn) return;
  if(sent && !sent.classList.contains('d-none')) return;
  var rs=document.querySelector(".renewal-status[data-id='"+id+"']");
  var ri=document.querySelector(".renewal-date[data-id='"+id+"']");
  var rStatus=rs?rs.value:'', rDate=ri?ri.value:'';
  recomputePastDue(id);
  if(rStatus==='due') return;
  if(rStatus==='renewal_due' && rDate){
   btn.disabled=false;
   if(isPastDue(id)){ btn.className='mail-btn red btn-renewal-mail'; btn.textContent='🔴 Send Renewal Mail'; }
   else             { btn.className='mail-btn amber btn-renewal-mail'; btn.textContent='📧 Send Renewal Mail'; }
  } else {
   btn.disabled=true; btn.className='mail-btn gray btn-renewal-mail'; btn.textContent='📧 Send Renewal Mail';
  }
 }

 function updateColor(el,type){
  el.classList.remove('active','inactive','renewal_due','due');
  if(el.value) el.classList.add(el.value);
 }

 function showInactiveBox(id,dateStr){
  var box=document.getElementById('inactive-info-'+id);
  var dateEl=document.getElementById('inactive-date-'+id);
  if(box) box.style.display='block';
  if(dateEl&&dateStr) dateEl.textContent=dateStr;
 }

 function hideInactiveBox(id){
  var box=document.getElementById('inactive-info-'+id);
  if(box) box.style.display='none';
 }

 // ── sendMailFlow ──────────────────────────────────────────────────────────
 function sendMailFlow(type,id){
  var isStatus=(type==='status');
  var statusSel=isStatus
   ? document.querySelector(".member-status[data-id='"+id+"']")
   : document.querySelector(".renewal-status[data-id='"+id+"']");
  var dateInput=isStatus
   ? document.querySelector(".start-date-input[data-id='"+id+"']")
   : document.querySelector(".renewal-date[data-id='"+id+"']");
  var currentStatus=statusSel?statusSel.value:'';
  var dateVal=dateInput?dateInput.value:'';
  var pastDue=!isStatus && isPastDue(id);
  if(!currentStatus){ Toast.fire({icon:'warning',title:isStatus?'Please select a status first':'Please select a renewal status first'}); return; }
  if(isStatus && currentStatus==='inactive'){ Toast.fire({icon:'info',title:'Inactive members do not receive mail'}); return; }
  if(!isStatus && currentStatus==='due'){ Toast.fire({icon:'info',title:'No mail sent for Renewal Completed'}); return; }
  if(isStatus && currentStatus==='active' && !dateVal){ Toast.fire({icon:'warning',title:'Please set a start date first'}); return; }
  if(!isStatus && !dateVal){ Toast.fire({icon:'warning',title:'Please set a renewal date first'}); return; }

  var title,html,btnText='Yes, Send Now',icon;
  if(isStatus){
   icon='question'; title='📧 Send Onboarding Mail';
   html='Send welcome email with <b>PDF certificate</b> now?<br><small>Start date: <b>'+formatDate(dateVal)+'</b></small>';
  } else {
   var p=document.querySelector(".price-input[data-id='"+id+"']");
   var pv=p?parseFloat(p.value)||0:0;
   if(pastDue){ icon='error'; title='🔴 Send Renewal Mail (Overdue)'; html='This renewal is past due. Send renewal mail now?<br><small>Amount: <b>£'+pv.toFixed(2)+'</b></small>'; }
   else        { icon='question'; title='📧 Send Renewal Mail'; html='Send renewal mail now?<br><small>Amount: <b>£'+pv.toFixed(2)+'</b></small>'; }
  }

  Swal.fire({ title:title, html:html, icon:icon, showCancelButton:true, confirmButtonColor:'#0d6efd', cancelButtonColor:'#6c757d', confirmButtonText:btnText, cancelButtonText:'Cancel' })
  .then(function(r){
   if(!r.isConfirmed) return;
   var realBtn=document.getElementById(isStatus?'send-mail-btn-'+id:'renewal-mail-btn-'+id);
   var realSent=document.getElementById(isStatus?'mail-sent-text-'+id:'renewal-mail-sent-text-'+id);
   if(!realBtn) return;
   realBtn.disabled=true; realBtn.textContent='⏳ Sending…';
   var sendUrl=isStatus?"{{ route('membership.sendmail') }}":"{{ route('membership.renewal.sendmail') }}";
   var sendBody=isStatus?{id:id,status:'active'}:{id:id,mail_type:'reminder'};
   fetch(sendUrl,{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify(sendBody) })
   .then(res=>res.json())
   .then(function(d){
    if(d.success){
     Toast.fire({icon:'success',title:'Mail sent successfully!'});
     var row=rowOf(id); realBtn.classList.add('d-none');
     if(realSent) realSent.classList.remove('d-none');
     if(row && isStatus) row.dataset.mailSent='1';
    } else {
     realBtn.disabled=false; realBtn.textContent=isStatus?'📧 Send Mail':'📧 Send Renewal Mail';
     Swal.fire({icon:'error',title:'Action Failed',text:d.message||'Something went wrong'});
    }
   })
   .catch(function(){ realBtn.disabled=false; realBtn.textContent=isStatus?'📧 Send Mail':'📧 Send Renewal Mail'; Toast.fire({icon:'error',title:'Request failed'}); });
  });
 }

 // ── Status change ─────────────────────────────────────────────────────────
 document.querySelectorAll('.status-change').forEach(function(sel){
  sel.addEventListener('change',function(){
   var id=this.dataset.id, status=this.value, phase=getPhase(id);
   hideInactiveBox(id);
   if(status==='active' && phase==='renewal'){ Swal.fire({icon:'warning',title:'Not allowed',text:'Use the renewal workflow to extend membership.'}); this.value=getStatus(id); updateColor(this,'status'); return; }
   if(status==='active' && isMailSent(id) && getStatus(id)==='active'){ Swal.fire({icon:'warning',title:'Already activated',text:'Active status can only be set once.'}); this.value=getStatus(id); updateColor(this,'status'); return; }
   if(status==='active'){
    var si=document.querySelector(".start-date-input[data-id='"+id+"']");
    if(si&&!si.value){ si.value=todayStr; handleFetch("{{ route('membership.startdate.update') }}",{id:id,start_date:todayStr}); }
   }
   handleFetch("{{ route('membership.status.update') }}",{id:id,status:status}).then(function(data){
    if(!data.success){ var sel2=document.querySelector(".member-status[data-id='"+id+"']"); if(sel2){sel2.value=getStatus(id); updateColor(sel2,'status');} Swal.fire({icon:'error',title:'Action Blocked',text:data.message}); return; }
    var row=rowOf(id); if(row) row.dataset.status=status;
    if(status==='inactive'){
     var inactiveDate=data.inactive_at||formatDate(todayStr);
     showInactiveBox(id,inactiveDate);
     if(row){ row.dataset.mailSent='0'; }
     showInactiveNoMail(id); refreshDeleteCell(id, false, 0);
    } else if(status==='active'){
     if(row){ row.dataset.mailSent='0'; }
     hideInactiveBox(id);
     var mc=document.getElementById('mail-cell-'+id);
     if(mc){
      mc.innerHTML='<button class="mail-btn gray btn-send-mail" data-id="'+id+'" id="send-mail-btn-'+id+'">📧 Send Mail</button><span class="d-none mail-sent-box" id="mail-sent-text-'+id+'">✅ Mail sent</span>';
      var nb=mc.querySelector('.btn-send-mail');
      if(nb) nb.addEventListener('click',function(){ sendMailFlow('status',this.dataset.id); });
     }
     evaluateSendMailBtn(id); refreshDeleteCell(id, null, null);
    }
   });
   updateColor(this,'status'); evaluateSendMailBtn(id);
  });
 });

 // ── Renewal status change ─────────────────────────────────────────────────
 document.querySelectorAll('.renewal-change').forEach(function(sel){
  sel.addEventListener('change',function(){
   var id=this.dataset.id, rStatus=this.value;
   var ri=document.querySelector(".renewal-date[data-id='"+id+"']");
   handleFetch("{{ route('membership.renewal.update') }}",{id:id,renewal_status:rStatus,renewal_date:ri?ri.value:null})
   .then(function(data){
    if(!data.success) return;
    var row=rowOf(id); if(row) row.dataset.renewalStatus=rStatus;
    // Renewal Completed rolls the cycle forward one year (2026 → 2027) —
    // show the new renewal / end date straight away instead of waiting for a reload.
    if(data.renewal_date){
     if(ri) ri.value=data.renewal_date;
     if(row) row.dataset.renewalDate=data.renewal_date;
    }
    if(data.end_date){
     var ei=document.querySelector(".end-date-input[data-id='"+id+"']");
     if(ei) ei.value=data.end_date;
     var efmt=formatDate(data.end_date);
     if(row){
      row.querySelectorAll('.lifecycle-lock').forEach(function(el){ el.textContent='🔒 Until '+efmt; });
      row.querySelectorAll('.lock-note').forEach(function(el){ if(el.textContent.indexOf('Active until')!==-1) el.textContent='✅ Active until '+efmt; });
     }
    }
    recomputePastDue(id); var past=isPastDue(id);
    var rmc=document.getElementById('renewal-mail-cell-'+id); if(!rmc) return;
    if(rStatus==='due'){ rmc.innerHTML='<div class="mail-locked-box">— Renewal completed —</div>'; return; }
    var bc,bt;
    if(rStatus==='renewal_due' && past){ bc='mail-btn red btn-renewal-mail'; bt='🔴 Send Renewal Mail'; }
    else if(rStatus==='renewal_due')   { bc='mail-btn amber btn-renewal-mail'; bt='📧 Send Renewal Mail'; }
    else                                { bc='mail-btn gray btn-renewal-mail'; bt='📧 Send Renewal Mail'; }
    rmc.innerHTML='<button class="'+bc+'" data-id="'+id+'" id="renewal-mail-btn-'+id+'">'+bt+'</button><span class="d-none mail-done-box" id="renewal-mail-sent-text-'+id+'">✅ Renewal mail sent</span>';
    var nb=rmc.querySelector('.btn-renewal-mail');
    if(nb) nb.addEventListener('click',function(){ sendMailFlow('renewal',this.dataset.id); });
    evaluateRenewalMailBtn(id);
   });
   updateColor(this,'renewal');
  });
 });

 // ── Renewal date change ───────────────────────────────────────────────────
 document.querySelectorAll('.renewal-date').forEach(function(input){
  input.addEventListener('change', function(){
   var id=this.dataset.id, rDate=this.value||null;
   if(rDate && !isCompleteDate(rDate)) return;
   fetch("{{ route('membership.renewal.update') }}",{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({id:id,renewal_date:rDate}) })
   .then(r=>r.json())
   .then(function(data){
    if(data.success){ var s=document.querySelector(".renewal-status[data-id='"+id+"']"); if(s){s.value=data.renewal_status; updateColor(s,'renewal');} Toast.fire({icon:'success',title:'Renewal date updated'}); }
    evaluateRenewalMailBtn(id);
   })
   .catch(function(){ Toast.fire({icon:'error',title:'Request failed'}); });
  });
 });

 // ── Start date change — auto-fills end date as start + 1 year − 1 day ────
 document.querySelectorAll('.start-date-input').forEach(function(input){
  input.addEventListener('change', function(){
   var id=this.dataset.id, start_date=this.value||null;
   if(start_date && !isCompleteDate(start_date)) return;

   var ei=document.querySelector(".end-date-input[data-id='"+id+"']");
   if(start_date && ei){ ei.value=calcEndDate(start_date); }
   else if(!start_date && ei){ ei.value=''; }

   fetch("{{ route('membership.startdate.update') }}",{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({id:id,start_date:start_date}) })
   .then(r=>r.json())
   .then(function(data){
    if(!data.success){ Toast.fire({icon:'error',title:data.message||'Update failed'}); return; }
    Toast.fire({icon:'success',title:'Start date updated'});
    if(data.end_date && ei){ ei.value=data.end_date; }
    evaluateSendMailBtn(id);
   })
   .catch(function(){ Toast.fire({icon:'error',title:'Request failed'}); });
  });
 });

 // ── End date change ───────────────────────────────────────────────────────
 document.querySelectorAll('.end-date-input').forEach(function(input){
  input.addEventListener('change', function(){
   var id=this.dataset.id, end_date=this.value||null;
   if(end_date && !isCompleteDate(end_date)) return;
   handleFetch("{{ route('membership.enddate.update') }}",{id:id,end_date:end_date})
   .then(function(data){
    if(data && data.success && end_date){
     var fmt=formatDate(end_date), row=rowOf(id);
     if(row){
      row.querySelectorAll('.lifecycle-lock').forEach(function(el){ el.textContent='🔒 Until '+fmt; });
      row.querySelectorAll('.lock-note').forEach(function(el){ if(el.textContent.indexOf('Active until')!==-1) el.textContent='✅ Active until '+fmt; });
     }
    }
   });
  });
 });

 // ── Price change ──────────────────────────────────────────────────────────
 document.querySelectorAll('.price-input').forEach(function(input){
  input.addEventListener('change',function(){
   var id=this.dataset.id;
   var ri=document.querySelector(".renewal-date[data-id='"+id+"']");
   handleFetch("{{ route('admin.membership.updatePriceRenewal') }}",{ id:id, price:parseFloat(this.value)||0, renewal_date:ri?ri.value:null });
  });
 });

 // ── Plan change ───────────────────────────────────────────────────────────
 function paintPlan(sel){ sel.classList.remove('Monthly','Yearly'); if(sel.value) sel.classList.add(sel.value); }
 document.querySelectorAll('.plan-change').forEach(function(sel){
  paintPlan(sel);
  sel.addEventListener('change', function(){
   var id=this.dataset.id, newPlan=this.value, prevPlan=this.dataset.prev||'', selEl=this;
   if(!newPlan){ paintPlan(selEl); return; }
   Swal.fire({ title:'Change Payment Plan?', html:'Update this member\'s plan to <b>'+newPlan+'</b>?<br><small style="color:#6b7280;">This affects how monthly/annual amounts are calculated in mails.</small>', icon:'warning', showCancelButton:true, confirmButtonColor:'#0d6efd', cancelButtonColor:'#6c757d', confirmButtonText:'Yes, update', cancelButtonText:'Cancel' })
   .then(function(r){
    if(!r.isConfirmed){ selEl.value=prevPlan; paintPlan(selEl); return; }
    fetch("{{ route('admin.member.updatePayment') }}",{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({id:id,field:'payment_plan',value:newPlan}) })
    .then(r=>r.json())
    .then(function(data){
     if(data && data.success){ Toast.fire({icon:'success',title:'Plan updated to '+newPlan}); selEl.dataset.prev=newPlan; paintPlan(selEl); var tr=rowOf(id); if(tr) tr.dataset.paymentPlan=newPlan; }
     else{ selEl.value=prevPlan; paintPlan(selEl); Swal.fire({icon:'error',title:'Update Failed',text:(data&&data.message)||'Could not update plan'}); }
    })
    .catch(function(){ selEl.value=prevPlan; paintPlan(selEl); Toast.fire({icon:'error',title:'Request failed'}); });
   });
  });
 });

 // ── Button bindings ───────────────────────────────────────────────────────
 document.querySelectorAll('.btn-send-mail').forEach(function(btn){
  btn.addEventListener('click',function(){ sendMailFlow('status',this.dataset.id); });
 });
 document.querySelectorAll('.btn-renewal-mail').forEach(function(btn){
  btn.addEventListener('click',function(){ sendMailFlow('renewal',this.dataset.id); });
 });

 // ── Delete member ─────────────────────────────────────────────────────────
 function bindDeleteButtons(){
  document.querySelectorAll('.btn-delete-member:not([disabled])').forEach(function(btn){
   btn.addEventListener('click', function(){
    var id=this.dataset.id, name=this.dataset.name||'this member';
    Swal.fire({ title:'⚠ Permanently Delete Member?', html:'You are about to permanently delete <b>'+name+'</b>.<br><span style="color:#dc2626;font-size:12px;font-weight:600;">This action cannot be undone.</span>', icon:'error', showCancelButton:true, confirmButtonColor:'#dc2626', cancelButtonColor:'#6c757d', confirmButtonText:'🗑 Yes, Delete Permanently', cancelButtonText:'Cancel' })
    .then(function(result){
     if(!result.isConfirmed) return;
     fetch("{{ route('admin.member.delete') }}",{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({id:id}) })
     .then(r=>r.json())
     .then(function(data){
      if(data.success){ Toast.fire({icon:'success',title:name+' permanently deleted.'}); var row=document.querySelector("tr[data-id='"+id+"']"); if(row){row.style.transition='opacity .4s';row.style.opacity='0';setTimeout(function(){row.remove();},420);} }
      else{ Swal.fire({icon:'error',title:'Delete Failed',text:data.message||'Something went wrong.'}); }
     })
     .catch(function(){ Toast.fire({icon:'error',title:'Request failed'}); });
    });
   });
  });
 }
 bindDeleteButtons();

 // Delete is a manual admin action available at any time, so the button
 // stays enabled regardless of status. Kept for callers after status changes.
 function refreshDeleteCell(id, isEligible, inactiveYears){
  var row=rowOf(id); if(!row) return;
  var cells=row.querySelectorAll('td');
  var deleteTd=cells[cells.length-1]; if(!deleteTd) return;
  var name=row.querySelector('strong')?row.querySelector('strong').textContent.trim():'';
  deleteTd.innerHTML='<button class="btn-delete-member" data-id="'+id+'" data-name="'+name+'">🗑 Delete</button>';
  bindDeleteButtons();
 }

 // ── Boot ──────────────────────────────────────────────────────────────────
 document.querySelectorAll('.renewal-change').forEach(function(el){ updateColor(el,'renewal'); });
 document.querySelectorAll('.status-change').forEach(function(el){ updateColor(el,'status'); });
 document.querySelectorAll('.btn-send-mail').forEach(function(btn){ evaluateSendMailBtn(btn.dataset.id); });
 document.querySelectorAll('.btn-renewal-mail').forEach(function(btn){ evaluateRenewalMailBtn(btn.dataset.id); });

 // ── Filters ───────────────────────────────────────────────────────────────
 document.getElementById('apply-filter').addEventListener('click', applyFilter);

 function applyFilter(){
  var rs=document.getElementById('filter-renewal-status').value;
  var rf=document.getElementById('filter-renewal-from').value;
  var rt=document.getElementById('filter-renewal-to').value;
  var st=document.getElementById('filter-status').value;
  var pp=document.getElementById('filter-payment-plan').value;
  document.querySelectorAll('table tbody tr').forEach(function(row){
   if(row.cells.length<=1) return;
   var show=true;
   if(st && row.dataset.status!==st) show=false;
   if(pp && row.dataset.paymentPlan!==pp) show=false;
   if(rs && row.dataset.renewalStatus!==rs) show=false;
   var rd=row.dataset.renewalDate||'';
   if(rf && rd && rd<rf) show=false;
   if(rt && rd && rd>rt) show=false;
   row.style.display=show?'':'none';
  });
  renumberRows();
 }

 function quickFilter(type,value){
  document.querySelectorAll('table tbody tr').forEach(function(row){
   if(row.cells.length<=1) return;
   var match=false;
   if(type==='phase')   match=(row.dataset.phase===value);
   if(type==='status')  match=(row.dataset.status===value);
   if(type==='renewal') match=(row.dataset.renewalStatus===value);
   row.style.display=match?'':'none';
  });
  renumberRows();
 }

 // S.No counts only the rows currently shown, so it restarts at 1 after a filter.
 function renumberRows(){
  var n=0;
  document.querySelectorAll('table tbody tr').forEach(function(row){
   var cell=row.querySelector('td.sno');
   if(cell && row.style.display!=='none') cell.textContent=++n;
  });
 }

 function resetFilters(){
  document.getElementById('filter-renewal-status').value='';
  document.getElementById('filter-renewal-from').value='';
  document.getElementById('filter-renewal-to').value='';
  document.getElementById('filter-status').value='';
  document.getElementById('filter-payment-plan').value='';
  document.querySelectorAll('table tbody tr').forEach(function(row){ row.style.display=''; });
  renumberRows();
 }

 // These are called from inline onclick="" handlers (Reset button + status
 // pills), which only resolve GLOBAL functions. This whole script runs inside a
 // DOMContentLoaded closure, so expose them on window or the clicks do nothing.
 window.applyFilter  = applyFilter;
 window.quickFilter  = quickFilter;
 window.resetFilters = resetFilters;
// ── Editable payment fields (account holder / sort code / account number) ──
 document.querySelectorAll('.editable-field').forEach(function(input){
  input.addEventListener('change', function(){
   var id    = this.dataset.id;
   var field = this.dataset.field;
   var value = this.value;
   var el    = this;

   fetch("{{ route('admin.member.updatePayment') }}", {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
    body:JSON.stringify({ id:id, field:field, value:value })
   })
   .then(r=>r.json())
   .then(function(data){
    if(data && data.success){
     el.classList.add('changed');
     Toast.fire({icon:'success', title:'Updated'});
    } else {
     Toast.fire({icon:'error', title:(data&&data.message)||'Update failed'});
    }
   })
   .catch(function(){ Toast.fire({icon:'error', title:'Request failed'}); });
  });
 });
 // ── Export CSV ────────────────────────────────────────────────────────────
// ── Server-side Export (CSV + Report) ─────────────────────────────────────
 function buildExportUrl(type){
  var params = new URLSearchParams();
  var rs = document.getElementById('filter-renewal-status').value;
  var rf = document.getElementById('filter-renewal-from').value;
  var rt = document.getElementById('filter-renewal-to').value;
  var st = document.getElementById('filter-status').value;
  var pp = document.getElementById('filter-payment-plan').value;
  if(pp) params.append('payment_plan',   pp);
  if(rs) params.append('renewal_status', rs);
  if(rf) params.append('renewal_from',   rf);
  if(rt) params.append('renewal_to',     rt);
  if(st) params.append('status',         st);

  var base = "{{ route('customer-electronic.export', ['type' => '__T__']) }}".replace('__T__', type);
  var qs = params.toString();
  return qs ? base + '?' + qs : base;
 }

 document.getElementById('export-report').addEventListener('click', function(e){
  e.preventDefault();
  window.location.href = buildExportUrl('report');
 });

 document.getElementById('export-csv').addEventListener('click', function(e){
  e.preventDefault();
  window.location.href = buildExportUrl('csv');
 });

});
</script>
