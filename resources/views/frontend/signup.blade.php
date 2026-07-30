<!DOCTYPE html>
<html lang="en">

<head>
   @include('components.frontend.head')
</head>

<body class="index-page">
  <header id="header" class="header sticky-top">
   @include('components.frontend.header')
  </header>

<main class="main">
    <section class="ddpu-breadcrumb-sec">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <h1>Signup Form</h1>
            <ul class="bread-list">
              <li><a href="./">Home<i class="fa fa-angle-right"></i></a></li>
              <li class="active"><a href="javascript:void(0)">Signup Form</a></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <input type="hidden" id="userId" value="{{ $id }}">

    <section class="application-form-one-sec py-5">
      <div class="container">

        <!-- PROGRESS -->
        <div class="creative-progress mb-4 position-relative">
          <div class="progress bg-light rounded-pill" style="height: 8px;">
            <div class="progress-fill bg-primary rounded-pill" style="width: 0%; height: 100%; transition: width 0.5s;"></div>
          </div>
          <ul class="progress-steps d-flex justify-content-between mt-2 list-unstyled p-0">
            <li class="text-center flex-fill active"><span class="fw-bold">Step 1</span></li>
            <li class="text-center flex-fill"><span class="fw-bold">Step 2</span></li>
          </ul>
        </div>

        <!-- STEP 1 -->
        <div class="form-step" data-step="1">
          <h3 class="mb-4">Step 1: Bank Details Submission</h3>
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label">Payment Plan:</label>
              <select id="paymentPlan" class="form-select">
                <option value="">Select Payment Plan</option>
                <option value="monthly">Monthly Installment (Monthly Paid)</option>
                <option value="yearly">Yearly Paid (One Time)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">I would prefer to complete the Direct Debit form:</label>
              <select id="submissionType" class="form-select">
                <option value="">Select</option>
                <option value="electronic">Electronic</option>
                <option value="physical">Physical</option>
              </select>
            </div>

            <!-- ELECTRONIC FIELDS -->
            <div id="electronicFields" class="row g-3 mt-3 d-none">

              <div class="col-md-6">
                <label class="form-label">Account Holder Name</label>
                <input id="accountHolder"
                       class="form-control"
                       placeholder="Account Holder Name"
                       maxlength="50"
                       autocomplete="off">
                <div class="invalid-feedback" id="accountHolderError"></div>
                <small class="text-muted">Letters only — no numbers or symbols</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Account Number</label>
                <input id="accountNumber"
                       class="form-control"
                       placeholder="8-digit Account Number"
                       maxlength="8"
                       inputmode="numeric"
                       autocomplete="off">
                <div class="invalid-feedback" id="accountNumberError"></div>
                <small class="text-muted">Must be exactly 8 digits</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Sort Code</label>
                <input id="sortCode"
                       class="form-control"
                       placeholder="XX-XX-XX"
                       maxlength="8"
                       autocomplete="off">
                <div class="invalid-feedback" id="sortCodeError"></div>
                <small class="text-muted">Format: 12-34-56</small>
              </div>

              <!-- Bank info auto-filled after sort code validation -->
              <div class="col-md-6 d-none" id="bankInfoBox">
                <label class="form-label">Bank Name</label>
                <input id="bankNameDisplay" class="form-control bg-light" readonly placeholder="Auto-filled">
              </div>

              <div class="col-md-6 d-none" id="branchInfoBox">
                <label class="form-label">Branch Name</label>
                <input id="branchNameDisplay" class="form-control bg-light" readonly placeholder="Auto-filled">
              </div>

            </div>

            <!-- PHYSICAL FIELDS -->
            <div id="physicalFields" class="row g-3 mt-3 d-none">
              <div class="col-12">
                <div class="p-3 border rounded bg-light">
                  <p class="fw-bold mb-2">Follow these steps to complete the Direct Debit form:</p>
                  <ol class="mb-3 ps-3">
                    <li>Click the link below to <strong>download the Direct Debit form</strong>.</li>
                    <li><strong>Print the form</strong> and fill in the required details.</li>
                    <li>Upload the <strong>form</strong> using the file upload field below.</li>
                  </ol>
                  <a href="{{ route('direct_debit.pdf', $id) }}"
                     target="_blank"
                     class="btn-back btn btn-secondary btn-lg app-form-btn">
                     Download Direct Debit Form
                  </a>
                </div>
              </div>

              <!-- Hidden Company Name -->
              <div class="col-md-6">
                <input id="companyName" class="form-control" placeholder="Company Name" value="DDPU" style="display:none;">
              </div>

              <!-- Upload Field -->
              <div class="col-md-12">
                <small class="text-muted d-block mb-1">Upload the Direct Debit form (PDF only, max 5MB)</small>
                <input type="file" id="mandateFile" class="form-control" accept="application/pdf">
              </div>
            </div>

            <!-- Loader -->
            <div id="stepLoader" class="text-center d-none mt-3">
              <div class="spinner-border text-primary"></div>
              <p class="mt-2">Please wait, validating your bank details...</p>
            </div>

            <div class="col-12 mt-3">
              <div class="d-flex justify-content-between">
                <button type="button"
                        class="btn-back btn btn-secondary btn-lg app-form-btn"
                        onclick="window.history.back();">
                  Back
                </button>
                <button id="nextBtn"
                        type="button"
                        class="btn-next btn btn-lg app-form-btn"
                        style="background:#162f59; color:#fff;">
                  Next
                </button>
              </div>
            </div>

          </div>
        </div>

        <!-- STEP 2 -->
        <div class="form-step d-none" data-step="2">
          <h3 class="mb-4">Step 2: Confirm & Submit</h3>
          <div class="row">

            <!-- LEFT: Confirmation Details -->
            <div class="col-md-6">
              <div id="confirmBox" class="border p-4 bg-light rounded mb-3" style="min-height:200px;"></div>
              <div>
                <div id="addressEditBox" class="mt-3 d-none">
                  <div class="row g-2">
                    <div class="col-12">
                      <input type="text" id="editAddress1" class="form-control" placeholder="Address Line 1">
                    </div>
                    <div class="col-12">
                      <input type="text" id="editAddress2" class="form-control" placeholder="Address Line 2">
                    </div>
                    <div class="col-md-6">
                      <input type="text" id="editCity" class="form-control" placeholder="City">
                    </div>
                    <div class="col-md-6">
                      <input type="text" id="editPostal" class="form-control" placeholder="Postal Code">
                    </div>
                    <div class="col-12">
                      <input type="text" id="editCountry" class="form-control" placeholder="Country">
                    </div>
                  </div>
                  <button id="saveAddressBtn" class="btn btn-success btn-sm mt-2">Save Address</button>
                </div>
              </div>

              <div class="d-flex gap-2 mt-3">
                <button id="backBtn" class="btn btn-secondary btn-lg app-form-btn">Back</button>
                <button id="finalSubmitBtn" class="btn btn-success btn-lg app-form-btn">
                  <span class="btn-text">Submit</span>
                  <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
                <button id="printPdfBtn" class="btn btn-info btn-lg app-form-btn">Print / Download PDF</button>
              </div>
            </div>

            <!-- RIGHT: PDF Preview -->
            <div class="col-md-6">
              <div id="pdfPreview" class="border p-3 bg-white rounded">
                <div id="pdfContent" style="font-size:12px; line-height:1.4;"></div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </section>

    <section class="join-membership-sec">
      <div class="container">
        <div class="row">
          <div class="col-md-8">
            <div class="join-membership-content-sec">
              <h2 class="join-membership-title">Join Membership</h2>
              <p>Our Membership Service Gives The Opportunity To Get All Our Services As Benefits.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="join-membership-btn-sec">
              <a href="#" class="btn-dark btn-lg"><span>Join Now</span></a>
            </div>
          </div>
        </div>
      </div>
    </section>
</main>

@include('components.frontend.footer')

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
  <i class="bi bi-arrow-up-short"></i>
</a>

@include('components.frontend.main-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
/* ═══════════════════════════════════════════════════
 |  GLOBALS
 ═══════════════════════════════════════════════════ */
let currentStep      = 1;
let bankName         = '';
let branchName       = '';
let paymentPlanLabel = '';
let isSubmitting     = false;

const csrf = document.querySelector('meta[name="csrf-token"]').content;

/* ═══════════════════════════════════════════════════
 |  STEP NAVIGATION
 ═══════════════════════════════════════════════════ */
function showStep(step) {
    document.querySelectorAll('.form-step').forEach(s => {
        s.classList.toggle('d-none', parseInt(s.dataset.step) !== step);
    });

    const progressFill = document.querySelector('.progress-fill');
    progressFill.style.width = step === 2 ? '100%' : '50%';

    document.querySelectorAll('.progress-steps li').forEach((li, index) => {
        li.classList.toggle('active', index < step);
    });
}

/* ═══════════════════════════════════════════════════
 |  SUBMISSION TYPE TOGGLE
 ═══════════════════════════════════════════════════ */
document.getElementById('submissionType').addEventListener('change', function () {
    document.getElementById('electronicFields').classList.toggle('d-none', this.value !== 'electronic');
    document.getElementById('physicalFields').classList.toggle('d-none',   this.value !== 'physical');
});

/* ═══════════════════════════════════════════════════
 |  PAYMENT PLAN CHANGE — show note for monthly
 ═══════════════════════════════════════════════════ */
document.getElementById('paymentPlan').addEventListener('change', function () {
    if (this.value === 'monthly') {
        Swal.fire({
            icon: 'info',
            title: 'Important Note',
            html: 'At present, we only offer annual membership.<br><br>Therefore, even if you choose to pay by monthly installment, you will be committing to pay all 12 installments.',
            confirmButtonText: 'OK'
        });
    }
});

/* ═══════════════════════════════════════════════════
 |  REAL-TIME INPUT VALIDATION
 ═══════════════════════════════════════════════════ */

/* Account Holder — letters, spaces, hyphens, apostrophes only */
document.getElementById('accountHolder').addEventListener('input', function () {
    this.value = this.value.replace(/[^a-zA-Z\s\-'\.]/g, ''); // block numbers/symbols as they type
});

document.getElementById('accountHolder').addEventListener('blur', function () {
    const nameRegex = /^[a-zA-Z\s\-'\.]{2,50}$/;
    const errorEl   = document.getElementById('accountHolderError');

    if (this.value && !nameRegex.test(this.value.trim())) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
        errorEl.innerText = 'Only letters allowed — no numbers or special characters.';
    } else if (this.value) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        errorEl.innerText = '';
    }
});

/* Account Number — digits only, exactly 8 */
document.getElementById('accountNumber').addEventListener('keypress', function (e) {
    if (!/[0-9]/.test(e.key)) e.preventDefault(); // block non-numeric
});

document.getElementById('accountNumber').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 8); // strip non-digits
});

document.getElementById('accountNumber').addEventListener('blur', function () {
    const errorEl = document.getElementById('accountNumberError');

    if (this.value && !/^\d{8}$/.test(this.value.trim())) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
        errorEl.innerText = 'Account number must be exactly 8 digits.';
    } else if (this.value) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        errorEl.innerText = '';
    }
});

/* Sort Code — auto format XX-XX-XX */
document.getElementById('sortCode').addEventListener('input', function () {
    let val = this.value.replace(/\D/g, '').slice(0, 6);
    if (val.length > 4)      val = val.slice(0,2) + '-' + val.slice(2,4) + '-' + val.slice(4);
    else if (val.length > 2) val = val.slice(0,2) + '-' + val.slice(2);
    this.value = val;
});

document.getElementById('sortCode').addEventListener('blur', function () {
    const clean   = this.value.replace(/-/g, '').trim();
    const errorEl = document.getElementById('sortCodeError');

    if (this.value && !/^\d{6}$/.test(clean)) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
        errorEl.innerText = 'Sort code must be 6 digits (e.g. 12-34-56).';
    } else if (this.value) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        errorEl.innerText = '';
    }
});

/* Mandate File — PDF only, max 5MB */
document.getElementById('mandateFile').addEventListener('change', function () {
    const file    = this.files[0];
    const maxSize = 5 * 1024 * 1024;

    if (!file) return;

    if (file.type !== 'application/pdf') {
        Swal.fire({ icon: 'error', title: 'Invalid File', text: 'Only PDF files are allowed.' });
        this.value = '';
        return;
    }

    if (file.size > maxSize) {
        Swal.fire({ icon: 'error', title: 'File Too Large', text: 'Maximum file size allowed is 5MB.' });
        this.value = '';
        return;
    }
});

/* ═══════════════════════════════════════════════════
 |  NEXT BUTTON — full validation + bank API check
 ═══════════════════════════════════════════════════ */
document.getElementById('nextBtn').addEventListener('click', async () => {

    const FIXED_SERVICE_NUMBER = '275708';

    const type        = document.getElementById('submissionType').value;
    const loader      = document.getElementById('stepLoader');
    const userId      = document.getElementById('userId').value;
    const paymentPlan = document.getElementById('paymentPlan').value;

    /* ── Basic dropdown checks ── */
    if (!paymentPlan) {
        Swal.fire({ icon: 'warning', title: 'Select Payment Plan' });
        return;
    }

    paymentPlanLabel = paymentPlan === 'monthly'
        ? 'Monthly Installment (Monthly Paid)'
        : 'Yearly Paid (One Time)';

    if (!type) {
        Swal.fire({ icon: 'warning', title: 'Select Submission Type' });
        return;
    }

    const today         = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });
    const serviceNumber = FIXED_SERVICE_NUMBER;

    /* ══════════════════════════════════════════
     |  ELECTRONIC
     ══════════════════════════════════════════ */
    if (type === 'electronic') {

        const holderVal  = document.getElementById('accountHolder').value.trim();
        const accNumVal  = document.getElementById('accountNumber').value.trim();
        const sortVal    = document.getElementById('sortCode').value.trim();
        const sortClean  = sortVal.replace(/-/g, '');

        /* ── Field presence ── */
        if (!holderVal || !accNumVal || !sortVal) {
            Swal.fire({ icon: 'warning', title: 'All bank fields are required.' });
            return;
        }

        /* ── Account holder format ── */
        const nameRegex = /^[a-zA-Z\s\-'\.]{2,50}$/;
        if (!nameRegex.test(holderVal)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Account Holder Name',
                text: 'Name must contain letters only — no numbers or special characters.'
            });
            document.getElementById('accountHolder').classList.add('is-invalid');
            return;
        }

        /* ── Account number format ── */
        if (!/^\d{8}$/.test(accNumVal)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Account Number',
                text: 'Account number must be exactly 8 digits.'
            });
            document.getElementById('accountNumber').classList.add('is-invalid');
            return;
        }

        /* ── Sort code format ── */
        if (!/^\d{6}$/.test(sortClean)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Sort Code',
                text: 'Sort code must be 6 digits (e.g. 12-34-56).'
            });
            document.getElementById('sortCode').classList.add('is-invalid');
            return;
        }

        /* ── Show loader ── */
        loader.classList.remove('d-none');

        /* ── Call Loqate via proxy — validate sort code + account number together ── */
        try {
            const res = await fetch(
                `https://ddpu.co.uk/proxy-bank-validation` +
                `?sortCode=${encodeURIComponent(sortClean)}` +
                `&accountNumber=${encodeURIComponent(accNumVal)}` +
                `&accountHolder=${encodeURIComponent(holderVal)}`
            );

            const data = await res.json();

            loader.classList.add('d-none');

            /* Sort code invalid / not found */
            if (data.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Bank Validation Failed',
                    text: data.message || 'Invalid bank details. Please check and try again.'
                });
                document.getElementById('sortCode').classList.add('is-invalid');
                document.getElementById('sortCodeError').innerText = data.message || 'Invalid sort code.';
                return;
            }

            /* Account number does not match sort code */
            if (!data.accountValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Account Number Mismatch',
                    text: 'The account number does not match this sort code. Please check your details.'
                });
                document.getElementById('accountNumber').classList.add('is-invalid');
                document.getElementById('accountNumberError').innerText = 'Does not match this sort code.';
                return;
            }

            /* ✅ All valid — store and display bank info */
            bankName   = data.bankName;
            branchName = data.branchName;

            /* Show auto-filled bank/branch fields */
            document.getElementById('bankNameDisplay').value   = bankName;
            document.getElementById('branchNameDisplay').value = branchName;
            document.getElementById('bankInfoBox').classList.remove('d-none');
            document.getElementById('branchInfoBox').classList.remove('d-none');

            /* Mark all fields valid */
            document.getElementById('accountHolder').classList.remove('is-invalid');
            document.getElementById('accountHolder').classList.add('is-valid');
            document.getElementById('accountNumber').classList.remove('is-invalid');
            document.getElementById('accountNumber').classList.add('is-valid');
            document.getElementById('sortCode').classList.remove('is-invalid');
            document.getElementById('sortCode').classList.add('is-valid');

        } catch (e) {
            loader.classList.add('d-none');
            Swal.fire({
                icon: 'error',
                title: 'Bank Validation Failed',
                text: 'Unable to connect to bank validation service. Please try again later.'
            });
            return;
        }

        /* ── Save step 1 data ── */
        loader.classList.remove('d-none');

        await fetch('https://ddpu.co.uk/signup/step1-save', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                user_id:    userId,
                step1_data: {
                    type:           'electronic',
                    payment_plan:   paymentPlan,
                    account_holder: holderVal,
                    account_number: accNumVal,
                    sort_code:      sortClean,
                    bank_name:      bankName,
                    branch_name:    branchName,
                    service_number: serviceNumber,
                }
            })
        });

        loader.classList.add('d-none');
        showConfirmation('electronic', serviceNumber, today);

    /* ══════════════════════════════════════════
     |  PHYSICAL
     ══════════════════════════════════════════ */
    } else {

        const companyNameVal = document.getElementById('companyName').value;
        const mandateFile    = document.getElementById('mandateFile');

        if (!companyNameVal || mandateFile.files.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Please upload the signed Direct Debit form before continuing.' });
            return;
        }

        loader.classList.remove('d-none');

        const fd = new FormData();
        fd.append('user_id',                   userId);
        fd.append('mandate_file',              mandateFile.files[0]);
        fd.append('step1_data[type]',          'physical');
        fd.append('step1_data[company_name]',  companyNameVal);
        fd.append('step1_data[service_number]',serviceNumber);
        fd.append('step1_data[payment_plan]',  paymentPlan);

        await fetch('https://ddpu.co.uk/signup/step1-save', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body:    fd
        });

        loader.classList.add('d-none');
        showConfirmation('physical', serviceNumber, today);
    }

    await Swal.fire({
        icon:               'success',
        title:              `Step ${currentStep} Saved Successfully!`,
        text:               'You can continue to next step',
        timer:              1800,
        showConfirmButton:  false
    });

    currentStep = 2;
    showStep(2);
});

/* ═══════════════════════════════════════════════════
 |  BACK BUTTON
 ═══════════════════════════════════════════════════ */
document.getElementById('backBtn').addEventListener('click', () => {
    currentStep = 1;
    showStep(1);
});

/* ═══════════════════════════════════════════════════
 |  SHOW CONFIRMATION (STEP 2)
 ═══════════════════════════════════════════════════ */
function showConfirmation(type, serviceNumber, today, fileName = '') {

    let leftHtml = '';

    const instructionHtml = `
        <h4 style="text-align:center;">Instruction to your bank / building society to pay by Direct Debit</h4>
        <p><strong>Service User Number:</strong> ${serviceNumber}</p>
        <p>Please pay FastPay Ltd Re DDPU Ltd Direct Debits from the account detailed in this instruction subject to the safeguards assured by the Direct Debit Guarantee.</p>
        <p>I understand that this instruction may remain with FastPay Ltd Re DDPU Ltd and, if so, details will be passed electronically to my Bank/Building Society.</p>
        <p><strong>Company Name:</strong> DDPU Ltd</p>`;

    const monthlyNote = paymentPlanLabel && paymentPlanLabel.toLowerCase().includes('monthly')
        ? `<p style="color:red; font-size:12px;"><strong>Note:</strong> At present, we only offer annual membership. You are committing to all 12 installments.</p>`
        : '';

    if (type === 'electronic') {

        leftHtml = `
            <h5>Submission Type: Electronic</h5><br>
            <p><strong>Payment Plan:</strong> ${paymentPlanLabel}</p>
            ${monthlyNote}
            <p><strong>Service Number:</strong> ${serviceNumber}</p>
            <p><strong>Account Holder:</strong> ${document.getElementById('accountHolder').value}</p>
            <p><strong>Account Number:</strong> ${document.getElementById('accountNumber').value}</p>
            <p><strong>Sort Code:</strong> ${document.getElementById('sortCode').value}</p>
            <p><strong>Bank Name:</strong> ${bankName}</p>
            <p><strong>Branch Name:</strong> ${branchName}</p>
            <p><strong>Date:</strong> ${today}</p>`;

    } else {

        let uploadedFileName = 'No file uploaded';
        const mandateFile    = document.getElementById('mandateFile');

        if (mandateFile?.files?.length > 0) {
            uploadedFileName = mandateFile.files[0].name;
        } else if (fileName && fileName !== 'null' && fileName !== 'undefined' && fileName.trim() !== '') {
            uploadedFileName = fileName.trim();
        }

        const uploadedFilePath = uploadedFileName !== 'No file uploaded'
            ? `${window.location.origin}/direct-debit/${uploadedFileName}`
            : '';

        leftHtml = `
            <h5>Submission Type: Physical</h5><br>
            <p><strong>Payment Plan:</strong> ${paymentPlanLabel}</p>
            ${monthlyNote}
            <p><strong>Service Number:</strong> ${serviceNumber}</p>
            <p><strong>Uploaded File:</strong>
                ${uploadedFilePath
                    ? `<a href="${uploadedFilePath}" target="_blank" style="color:blue; text-decoration:underline;">${uploadedFileName}</a>`
                    : uploadedFileName}
            </p>
            <p><strong>Date:</strong> ${today}</p>`;
    }

    document.getElementById('confirmBox').innerHTML    = leftHtml;
    document.getElementById('pdfContent').innerHTML    = instructionHtml;
    document.getElementById('printPdfBtn').onclick     = () => generatePDF(serviceNumber);
}

/* ═══════════════════════════════════════════════════
 |  GENERATE PDF
 ═══════════════════════════════════════════════════ */
async function generatePDF(serviceNumber) {

    const { jsPDF } = window.jspdf;
    const doc       = new jsPDF('p', 'pt', 'a4');
    const type      = document.getElementById('submissionType').value;

    const today = new Date().toLocaleDateString('en-GB', {
        day: '2-digit', month: 'long', year: 'numeric'
    });

    let accountName      = '';
    let uploadedFileName = '';
    let uploadedFilePath = '';

    if (type === 'electronic') {
        accountName = document.getElementById('accountHolder')?.value || '';
    } else {
        accountName = document.getElementById('companyName')?.value || '';
        const mandateFile = document.getElementById('mandateFile');
        if (mandateFile?.files?.length > 0) {
            uploadedFileName = mandateFile.files[0].name;
            uploadedFilePath = `direct_debit_/${uploadedFileName}`;
        }
    }

    const margin    = 60;
    const lh        = 18;
    let   y         = 100;

    doc.setFontSize(14);
    doc.setFont(undefined, 'bold');
    doc.text("Direct Debit Instruction", margin, 50);

    doc.setFontSize(11);
    doc.setFont(undefined, 'normal');

    doc.text(`Payment Plan: ${paymentPlanLabel || ''}`, 40, y);
    y += lh;

    if (paymentPlanLabel && paymentPlanLabel.toLowerCase().includes('monthly')) {
        const pageWidth  = doc.internal.pageSize.getWidth();
        const noteText   = doc.splitTextToSize(
            'Note: At present, we only offer annual membership. You are committing to all 12 installments.',
            (pageWidth - 60) / 2
        );
        doc.setFontSize(9);
        doc.setTextColor(255, 0, 0);
        doc.text(noteText, 40, y);
        y += noteText.length * lh;
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
    }

    if (type === 'electronic') {
        doc.text(`Account Holder Name: ${accountName}`, 40, y);              y += lh;
        doc.text(`Account Number: ${document.getElementById('accountNumber')?.value || ''}`, 40, y); y += lh;
        doc.text(`Sort Code: ${document.getElementById('sortCode')?.value || ''}`, 40, y);           y += lh;
        doc.text(`Bank: ${bankName || ''}`, 40, y);                          y += lh;
        doc.text(`Branch: ${branchName || ''}`, 40, y);                      y += lh;
    }

    if (type === 'physical' && uploadedFileName) {
        doc.text("Uploaded File:", 40, y);
        doc.setTextColor(0, 0, 255);
        doc.textWithLink(uploadedFileName, 120, y, { url: uploadedFilePath });
        doc.setTextColor(0, 0, 0);
        y += lh;
    }

    doc.text(`Date: ${today}`, 40, y);           y += lh;
    doc.text(`Service User Number: ${serviceNumber}`, 40, y);

    /* Right column instruction */
    const instructionText = `Instruction to your bank / building society to pay by direct debit.
Service User Number: ${serviceNumber}

Please pay FastPay Ltd Re DDPU Ltd Direct Debits from the account detailed in this instruction subject to the safeguards assured by the Direct Debit Guarantee.

I understand that this instruction may remain with FastPay Ltd Re DDPU Ltd and, if so, details will be passed electronically to my Bank/Building Society.

Company Name: DDPU Ltd

DDPU Ltd will confirm the signing of this mandate by email within 3 days.`.trim();

    doc.text(instructionText, 300, 100, { maxWidth: 250 });

    /* Guarantee box */
    const boxX = 40, boxY = 380, boxW = 515, boxH = 170;
    doc.setLineWidth(1);
    doc.rect(boxX, boxY, boxW, boxH);

    doc.setFont(undefined, 'bold');
    doc.setFontSize(12);
    doc.text("Direct Debit Guarantee", boxX + 10, boxY + 20);

    doc.setFont(undefined, 'normal');
    doc.setFontSize(10);

    const guaranteeText = `This Guarantee is offered by all Banks and Building Societies that accept instructions to pay Direct Debits.

If there are any changes to the amount, date or frequency of your Direct Debit, FastPay Ltd Re DDPU Ltd will notify you five working days in advance.

If an error is made in the payment of your Direct Debit, you are entitled to a full and immediate refund from your bank.

You can cancel a Direct Debit at any time by contacting your Bank or Building Society.`.trim();

    const wrappedText = doc.splitTextToSize(guaranteeText, boxW - 20);
    doc.text(wrappedText, boxX + 10, boxY + 45, { lineHeightFactor: 1.5 });

    const blob = doc.output('blob');
    const url  = URL.createObjectURL(blob);
    window.open(url, '_blank');
}

/* ═══════════════════════════════════════════════════
 |  FINAL SUBMIT
 ═══════════════════════════════════════════════════ */
document.getElementById('finalSubmitBtn').addEventListener('click', async function () {

    if (isSubmitting) return;
    isSubmitting = true;

    const btn = this;
    btn.querySelector('.btn-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    btn.disabled = true;
    document.getElementById('backBtn').disabled     = true;
    document.getElementById('printPdfBtn').disabled = true;

    try {
        const res = await fetch("https://ddpu.co.uk/signup/final-submit", {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                user_id: document.getElementById('userId').value,
                updated_address: {
                    address_line_1: document.getElementById('newAddress1')?.value || '',
                    address_line_2: document.getElementById('newAddress2')?.value || '',
                    city:           document.getElementById('newCity')?.value     || '',
                    postal_code:    document.getElementById('newPostal')?.value   || '',
                    country:        document.getElementById('newCountry')?.value  || '',
                }
            })
        });

        const data = await res.json();

        if (data.already_submitted) {
            await Swal.fire({ icon: 'info', title: 'Already Submitted', text: 'Your application is already submitted.' });
            window.location.href = "{{ route('thankyou') }}";
            return;
        }

        if (data.success) {
            await Swal.fire({
                icon:              'success',
                title:             'Signup Completed!',
                text:              'Your application has been submitted successfully.',
                timer:             2000,
                showConfirmButton: false
            });
            window.location.href = "{{ route('thankyou') }}";
        } else {
            throw new Error('Submit failed');
        }

    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Something went wrong', text: err.message || 'Please try again.' });

        btn.querySelector('.btn-text').classList.remove('d-none');
        btn.querySelector('.spinner-border').classList.add('d-none');
        btn.disabled = false;
        document.getElementById('backBtn').disabled     = false;
        document.getElementById('printPdfBtn').disabled = false;
        isSubmitting = false;
    }
});

/* ═══════════════════════════════════════════════════
 |  ADDRESS EDIT
 ═══════════════════════════════════════════════════ */
const editBtn = document.getElementById('editAddressBtn');
const saveBtn = document.getElementById('saveAddressBtn');

if (editBtn) {
    editBtn.onclick = function () {
        document.getElementById('editAddress1').value = document.getElementById('hiddenAddress1')?.value || '';
        document.getElementById('editAddress2').value = document.getElementById('hiddenAddress2')?.value || '';
        document.getElementById('editCity').value     = document.getElementById('hiddenCity')?.value     || '';
        document.getElementById('editPostal').value   = document.getElementById('hiddenPostal')?.value   || '';
        document.getElementById('editCountry').value  = document.getElementById('hiddenCountry')?.value  || '';
        document.getElementById('addressEditBox').classList.remove('d-none');
    };
}

if (saveBtn) {
    saveBtn.onclick = function () {
        const a1 = document.getElementById('editAddress1').value;
        const a2 = document.getElementById('editAddress2').value;
        const ct = document.getElementById('editCity').value;
        const pc = document.getElementById('editPostal').value;
        const co = document.getElementById('editCountry').value;

        if (document.getElementById('newAddress1')) document.getElementById('newAddress1').value = a1;
        if (document.getElementById('newAddress2')) document.getElementById('newAddress2').value = a2;
        if (document.getElementById('newCity'))     document.getElementById('newCity').value     = ct;
        if (document.getElementById('newPostal'))   document.getElementById('newPostal').value   = pc;
        if (document.getElementById('newCountry'))  document.getElementById('newCountry').value  = co;

        if (document.getElementById('addressDisplay')) {
            document.getElementById('addressDisplay').innerHTML = `
                <div class="alert alert-warning mb-2"><strong>Updated Address (New)</strong></div>
                <p>${a1}</p><p>${a2}</p><p>${ct}</p><p>${pc}</p><p>${co}</p>`;
        }

        document.getElementById('addressEditBox').classList.add('d-none');
    };
}

/* ═══════════════════════════════════════════════════
 |  INIT — load progress on page load
 ═══════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', async function () {

    const userId = document.getElementById('userId').value;

    try {
        const res = await fetch("https://ddpu.co.uk/signup/get-progress", {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify({ user_id: userId })
        });

        const result = await res.json();

        if (result.step === 2 && result.data && result.data.step1) {
            fillStep1Data(result.data.step1);
            currentStep = 2;
            showStep(2);
        } else {
            currentStep = 1;
            showStep(1);
        }

    } catch (e) {
        console.error('Progress load failed', e);
        showStep(1);
    }
});

/* ═══════════════════════════════════════════════════
 |  FILL STEP 1 DATA (resume progress)
 ═══════════════════════════════════════════════════ */
function fillStep1Data(data) {

    if (!data) return;

    document.getElementById('paymentPlan').value    = data.payment_plan || '';
    document.getElementById('submissionType').value = data.type         || '';

    document.getElementById('submissionType').dispatchEvent(new Event('change'));

    paymentPlanLabel = data.payment_plan === 'monthly'
        ? 'Monthly Installment (Monthly Paid)'
        : 'Yearly Paid (One Time)';

    if (data.type === 'electronic') {

        document.getElementById('accountHolder').value = data.account_holder || '';
        document.getElementById('accountNumber').value = data.account_number || '';
        document.getElementById('sortCode').value      = data.sort_code      || '';
        bankName   = data.bank_name   || '';
        branchName = data.branch_name || '';

        /* Show auto-filled bank info */
        document.getElementById('bankNameDisplay').value   = bankName;
        document.getElementById('branchNameDisplay').value = branchName;
        document.getElementById('bankInfoBox').classList.remove('d-none');
        document.getElementById('branchInfoBox').classList.remove('d-none');

        showConfirmation('electronic', data.service_number, new Date().toLocaleDateString('en-GB'));

    } else if (data.type === 'physical') {

        document.getElementById('companyName').value = data.company_name || '';
        showConfirmation('physical', data.service_number, new Date().toLocaleDateString('en-GB'), data.file_name);
    }
}

/* Init first step */
showStep(1);
</script>

</body>
</html>