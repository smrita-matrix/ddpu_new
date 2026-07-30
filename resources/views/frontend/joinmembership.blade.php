<!DOCTYPE html>
<html lang="en">
<head>
 @include('components.frontend.head')
</head>
<style>
    input[type="email"] {
    text-transform: lowercase !important;
}
/* PDF Layout */
.preview-section-title {
    font-weight: bold;
    font-size: 18px;
    margin: 25px 0 10px;
    border-bottom: 2px solid #333;
    padding-bottom: 5px;
    color: #222;
}
#address-results {
    position: absolute;
    width: 100%;
    z-index: 9999;
    max-height: 250px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #ddd;
    display: none;
}

.preview-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    background: #fff;
}
.year-only-picker .flatpickr-months .flatpickr-prev-month,
.year-only-picker .flatpickr-months .flatpickr-next-month,
.year-only-picker .flatpickr-current-month .flatpickr-monthDropdown-months,
.year-only-picker .flatpickr-weekdays,
.year-only-picker .flatpickr-days {
    display: none !important;
}

.preview-table td {
    border: 1px solid #ccc;
    padding: 10px 12px;
    vertical-align: top;
    font-size: 14px;
}

.preview-label {
    width: 30%;
    background: #f2f2f2;
    font-weight: bold;
}

.preview-value {
    width: 70%;
    color: #333;
}

/* Scrollable preview box */
#previewContainer {
    max-height: 420px;
    overflow-y: auto;
    padding-right: 10px;
    border: 1px solid #ccc;
    background: #fff;
    border-radius: 6px;
}

#previewContainer::-webkit-scrollbar {
    width: 8px;
}
#previewContainer::-webkit-scrollbar-thumb {
    background: #b4b4b4;
    border-radius: 10px;
}
#previewContainer::-webkit-scrollbar-track {
    background: #eee;
}
</style>

<style>
    .employed-field {
        display: none;
    }
    .description-field {
        display: none;
    }
</style>

<!-- SAVE DRAFT MODAL STYLES -->
<style>
#draftModalOverlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.48);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
}
#draftModalOverlay.open {
    display: flex;
}
#draftModal {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 14px;
    width: 100%;
    max-width: 640px;
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 8px 40px rgba(0,0,0,0.18);
}
#draftModalHeader {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px 14px;
    border-bottom: 1px solid #e8e8e8;
    flex-shrink: 0;
}
#draftModalHeader h2 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #222;
}
#draftExpiryBadge {
    font-size: 12px;
    color: #666;
    background: #f5f5f5;
    border-radius: 6px;
    padding: 4px 10px;
    display: flex;
    align-items: center;
    gap: 5px;
    border: 1px solid #e8e8e8;
}
#draftModalCloseBtn {
    width: 32px;
    height: 32px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: transparent;
    cursor: pointer;
    font-size: 18px;
    color: #888;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
#draftModalCloseBtn:hover { background: #f5f5f5; }
#draftModalBanner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 22px;
    background: #f0faf4;
    font-size: 13px;
    color: #2e7d50;
    border-bottom: 1px solid #c8ebd8;
    flex-shrink: 0;
}
#draftModalBody {
    overflow-y: auto;
    padding: 18px 22px 22px;
    flex: 1;
}
#draftModalBody::-webkit-scrollbar { width: 6px; }
#draftModalBody::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
.dm-step-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #aaa;
    margin: 0 0 12px;
    padding-bottom: 6px;
    border-bottom: 1px solid #f0f0f0;
}
.dm-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 16px;
    margin-bottom: 20px;
}
.dm-field {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.dm-field.full {
    grid-column: 1 / -1;
}
.dm-field-label {
    font-size: 11px;
    color: #aaa;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dm-field-value {
    font-size: 13px;
    color: #333;
    background: #f7f7f7;
    border-radius: 6px;
    padding: 6px 10px;
    min-height: 30px;
    word-break: break-word;
    border: 1px solid #ececec;
}
.dm-field-value.ta {
    white-space: pre-wrap;
    max-height: 80px;
    overflow-y: auto;
    font-size: 12px;
}
.dm-empty {
    text-align: center;
    padding: 32px 0;
    color: #bbb;
    font-size: 14px;
}
#draftModalFooter {
    padding: 12px 22px;
    border-top: 1px solid #e8e8e8;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    gap: 10px;
}
#draftClearBtn {
    font-size: 13px;
    color: #c0392b;
    background: transparent;
    border: 1px solid #e0b0b0;
    border-radius: 6px;
    padding: 7px 16px;
    cursor: pointer;
}
#draftClearBtn:hover { background: #fff0ee; }
#draftCloseFooterBtn {
    font-size: 13px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 7px 22px;
    cursor: pointer;
    color: #333;
}
#draftCloseFooterBtn:hover { background: #f5f5f5; }
</style>

<body class="index-page">
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

  <header id="header" class="header sticky-top">
        @include('components.frontend.header')
  </header>

  <main class="main">
    <section class="ddpu-breadcrumb-sec">
      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <h1>Join Membership</h1>
            <ul class="bread-list">
              <li><a href="{{ route('frontend.index') }}">Home<i class="fa fa-angle-right"></i></a></li>
              <li class="active"><a href="javascript:void(0)">Join Membership</a></li>
            </ul>
          </div>
        </div>
      </div>
    </section>


   <section class="application-form-one-sec">
    <div class="section-title aos-init aos-animate" data-aos="fade-up">
        <h2>Application for Membership of <br>Doctors and Dentists Protection Union</h2>
    </div>
    <div class="container">
        <div class="custom-top-sec"></div>
        
        <div id="progressWrapper" class="creative-progress">
            <div class="progress-line">
                <div class="progress-fill"></div>
            </div>
            <ul class="progress-steps">
                <li class="active" data-step="1"><span>Step 1</span></li>
                <li data-step="2"><span>Step 2</span></li>
                <li data-step="3"><span>Step 3</span></li>
                <li data-step="4"><span>Step 4</span></li>
                <li data-step="5"><span>Step 5</span></li>
                <li data-step="6"><span>Step 6</span></li>
                </ul>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div id="form-step-wrap" class="application-form-step-sec">

                    <!-- STEP 1 -->
                    <div id="step1box" class="slider-step first-step af-border-sec" data-next-step="step2Box" data-step="1">
                        <div class="application-form-title-sec creative-med-title">
                            <div class="title-icon"><i class="fa-solid fa-user"></i></div>
                            <h3>Step 1</h3>
                        </div>

                        <div class="app-form-title-subsec"><h4>Your Details</h4></div>

                        <form id="step1Form" class="row g-3">
                            <div class="col-md-6">
                                <select id="gmc-gdc-select-sec" class="form-select" name="gmc_gdc_type">
                                    <option disabled selected>Are your registered with GMC or GDC</option>
                                    <option value="state-gmc">GMC</option>
                                    <option value="gdc">GDC</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="GMC/GDC Registration No."
                                    id="gmc-gdc-registration-number" name="gmc_gdc_number">
                            </div>

                            <div class="col-md-6">
                                <select class="form-select" id="af-registration-year-picker" name="registration_year">
                                    <option value="" disabled selected>Year of Above Registration (YYYY)</option>
                                </select>
                            
                            </div>
                            
                            
                            <div class="col-md-6">
                                <select class="form-select" id="af-qualification-year-picker" name="qualification_year">
                                    <option value="" disabled selected>Year of Qualification (YYYY)</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <input type="text" class="form-control" id="app-form-specialty" placeholder="Enter the Specialty"
                                    name="specialty">
                            </div>

                            <div class="col-12">
                                <input type="text" class="form-control" id="app-form-professional-qualification"
                                    placeholder="Professional Qualification" name="professional_qualification">
                            </div>

                            <hr>

                            <div class="app-form-title-subsec">
                                <h4>Please provide following details as they appear in GMC or GDC record</h4>
                            </div>
                            <div class="col-md-2">
                                    <select class="form-select" name="title" id="app-form-title">
                                        <option value="" disabled selected>Title</option>
                                        <option value="Dr.">Dr</option>
                                        <option value="Mr.">Mr</option>
                                        <option value="Ms.">Mrs</option>
                                        <option value="Miss.">Miss</option>
                                        <option value="Prof.">Prof</option>
                                    </select>
                                </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Enter the First name"
                                    id="app-form-first-name" name="first_name">
                            </div>

                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="Enter the Middle name"
                                    id="app-form-middle-name" name="middle_name">
                            </div>

                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Enter the Last name"
                                    id="app-form-last-name" name="last_name">
                            </div>

                            <div class="col-md-6">
                                <input type="text" class="form-control"
                                    id="app-form-date-of-birth"
                                    placeholder="Date of Birth (DD/MM/YYYY)"
                                    name="date_of_birth">
                                <small id="dobError" class="text-danger" style="display:none;">You must be at least 18 years old.</small>
                            </div>

                            <div class="col-md-6">
                                <select id="gender-select" class="form-select" name="gender">
                                    <option disabled selected>Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 position-relative">
                                <input type="text"
                                       class="form-control"
                                       id="app-form-postal-code"
                                       placeholder="Enter the Postal Code"
                                       maxlength="10"
                                       name="postal-code"
                                       autocomplete="off"
                                       oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9\s-]/g,'')">
                            
                                <ul id="address-results"
                                    class="list-group address-dropdown"
                                    style="position:absolute; z-index:1000; width:100%; max-height:200px; overflow-y:auto; display:none;">
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="app-form-address-line-one"
                                       placeholder="Address Line 1" name="address_line_1">
                            </div>
                            
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="app-form-address-line-two"
                                       placeholder="Address Line 2" name="address_line_2">
                            </div>
                            
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="app-form-city" placeholder="Enter the City"
                                       name="city">
                            </div>
                            
                            <div class="col-md-6">
                                <select class="form-select" id="app-form-country" name="country">
                                    <option value="" disabled selected>Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->name }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" style="color: red; display: none;">
                                    Please select a country
                                </div>
                            </div>
                            <hr>


                            <div class="app-form-title-subsec">
                                <h4>Address on which you would like to be contacted if different from above</h4>
                            </div>

                            <div class="col-12">
                                <div class="form-check app-form-checkbox-custom-sec">
                                    <input class="form-check-input" type="checkbox" id="same-address-checkbox"
                                        name="same_as_main_address">
                                    <label class="form-check-label" for="same-address-checkbox">
                                        Same As Above Address
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 position-relative">
                                <input type="text" class="form-control" id="contact-postal-code"
                                    placeholder="Enter the Postal Code" maxlength="10" name="contact_postal_code"
                                    autocomplete="off"
                                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9\s-]/g,'')">
                            
                                <ul id="contact-address-results"
                                    class="list-group address-dropdown"
                                    style="position:absolute; z-index:1000; width:100%; max-height:200px; overflow-y:auto; display:none;">
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="contact-address-line-one"
                                    placeholder="Address Line 1" name="contact_address_line_1">
                            </div>

                            <div class="col-md-6">
                                <input type="text" class="form-control" id="contact-address-line-two"
                                    placeholder="Address Line 2" name="contact_address_line_2">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="contact-city"
                                placeholder="City" name="contact_city">

                            </div>
                            <div class="col-md-12">
                                <select class="form-select" id="contact-country" name="contact_country">
                                    <option disabled selected>Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->name }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            

                            <div class="col-6">
                                <button type="button" value="save-draft" class="form-control app-form-btn">Save Draft</button>
                            </div>

                            <div class="col-6">
                                <button type="button" value="Continue" class="btn-next btn-success form-control app-form-btn">Continue</button>
                            </div>

                        </form>
                    </div>

                        <!-- STEP 2 -->
                        <div id="step2Box" class="slider-step af-border-sec" data-next-step="step3Box" data-back-to="step1Box" data-step="2">
                            <div class="application-form-title-sec creative-med-title">
                                <div class="title-icon"><i class="fa-solid fa-user"></i></div>
                                <h3>Step 2</h3>
                            </div>

                            <div class="app-form-title-subsec"><h4>Telephone Contact Details</h4></div>

                            <form id="step2Form" class="row g-3">

                                <!-- Telephone Day -->
                                <div class="col-md-4">
                                    <input type="tel" class="form-control" id="app-form-telephone-day"
                                        placeholder="Telephone (Day)" minlength="10" maxlength="15"
                                        name="telephone_day"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>

                                <!-- Telephone Evening -->
                                <div class="col-md-4">
                                    <input type="tel" class="form-control" id="app-form-telephone-evening"
                                        placeholder="Enter the Telephone (Evening)" minlength="10" maxlength="15"
                                        name="telephone_evening"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"> 
                                </div>

                                <!-- Mobile Number -->
                                <div class="col-md-4">
                                    <input type="tel" class="form-control" id="app-form-mobile-number"
                                        placeholder="Enter the Mobile Number" minlength="10" maxlength="15"
                                        name="mobile_number"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                </div>

                                <div class="app-form-title-subsec"><h4>Security</h4></div>

                                <!-- Primary Email -->
                                <div class="col-md-6">
                                    <input type="email" class="form-control" id="app-form-primary-email"
                                        placeholder="Enter the Primary Email" name="primary_email" required>
                                </div>

                                <!-- Secondary Email (Optional) -->
                                <div class="col-md-6">
                                    <input type="email" class="form-control" id="app-form-secondary-email-optional"
                                        placeholder="Enter the Secondary Email (Optional)" name="secondary_email" required>
                                </div>

                                <!-- Username -->
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="app-form-select-username"
                                        placeholder="Enter the Enter a Username" name="username" required>
                                </div>

                                <!-- Password -->
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <input type="password" class="form-control" id="app-form-select-password"
                                            placeholder="Enter the Enter a Password" name="password" required>
                                        <i class="fa-solid fa-eye toggle-password" data-target="#app-form-select-password" style="cursor:pointer;"></i>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <input type="password" class="form-control" id="app-form-verify-password"
                                            placeholder="Enter the Verify Password" name="confirm_password" required>
                                        <i class="fa-solid fa-eye toggle-password" data-target="#app-form-verify-password" style="cursor:pointer;"></i>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-4">
                                    <button type="button" class="form-control btn-back app-form-btn">Previous</button>
                                </div>

                                <div class="col-4">
                                    <button type="button" value="save-draft" class="form-control app-form-btn">Save Draft</button>
                                </div>

                                <div class="col-4">
                                    <button type="button" value="Continue" class="btn-next btn-success form-control app-form-btn">Continue</button>
                                </div>

                            </form>
                        </div>


                    <!-- STEP 3 -->
                    <div id="step3Box" class="slider-step af-border-sec" data-next-step="step4Box" data-back-to="step2Box" data-step="3">

                        <div class="application-form-title-sec creative-med-title">
                            <div class="title-icon"><i class="fa-solid fa-user"></i></div>
                            <h3>Step 3</h3>
                        </div>


                        <div class="app-form-title-subsec">
                            <h4>Job Title and Grade</h4>
                        </div>
                        
                        <form id="step3Form" class="row g-3">
                        
                            <!-- Employment Status -->
                            <div class="col-md-6">
                                <select class="form-select" id="emp-status" name="employment_status">
                                    <option value="" disabled selected>Are you employed or self employed ?</option>
                                    <option value="employed">Employed</option>
                                    <option value="self-employed">Self Employed</option>
                                </select>
                            </div>
                        
                            <!-- EMPLOYED FIELDS -->
                            <div class="col-md-6 employed-field">
                                <input type="text" class="form-control"
                                    id="current-employer"
                                    placeholder="Enter Current Employer"
                                    name="current_employer">
                            </div>
                        
                            <div class="col-md-6 employed-field">
                                <input type="text" class="form-control"
                                    id="employment-grade"
                                    placeholder="Enter Grade in which employed"
                                    name="employment_grade">
                            </div>
                        
                            <div class="col-md-6 employed-field">
                                <input type="text" class="form-control"
                                    id="lead-employer"
                                    placeholder="Main place of work"
                                    name="lead_employer">
                            </div>
                        
                            <!-- DESCRIPTION (always visible when option selected) -->
                            <div class="col-md-12 description-field">
                                <textarea class="form-control app-form-textarea-custom-sec"
                                    id="app-form-desc-of-current-role"
                                    placeholder="Description of Current Role"
                                    rows="4"
                                    name="current_role_description"></textarea>
                            </div>
                        
                            <!-- BUTTONS -->
                            <div class="col-md-12">
                                <div class="row app-form-custom-btn-row">
                        
                                    <div class="col-4">
                                        <button type="button" class="form-control btn-back app-form-btn">Previous</button>
                                    </div>
                        
                                    <div class="col-4">
                                        <button type="button" value="save-draft" class="form-control app-form-btn">Save Draft</button>
                                    </div>
                        
                                    <div class="col-4">
                                        <button type="button" class="btn-next btn-success form-control app-form-btn">Continue</button>
                                    </div>
                        
                                </div>
                            </div>
                        
                        </form>
                    </div>

                    <!-- STEP 4 -->
                    <div id="step4Box" class="slider-step af-border-sec" data-next-step="step5Box" data-back-to="step3Box" data-step="4">

                        <div class="application-form-title-sec creative-med-title">
                            <div class="title-icon"><i class="fa-solid fa-user"></i></div>
                            <h3>Step 4</h3>
                        </div>

                        <div class="app-form-title-subsec">
                            <h4>Professional Negligence Indemnity</h4>
                            <p>It is a legal and regulatory requirement that medical and dental professionals are fully insured or indemnified in respect of any civil or compensation claims that arises from their professional work. This is called Professional Negligence indemnity.</p>
<br>
                            <p>The responsibility to ensure that you have adequate Professional Negligence indemnity rests solely with you. Our cover is strictly limited to providing professional defence. Our standard membership does not provide Professional Negligence indemnity for any work.</p>
<br>
                            <p>Professional Negligence indemnity for NHS work is usually provided by NHS's own indemnity scheme. Therefore, you would usually require own Professional Negligence indemnity only for work outside the NHS (private practice) or for work in the NHS that is done outside of the employment contract (Category 2 or Fee-Paying work). If you are unsure if you have adequate Professional Negligence indemnity for the work you do or are not sure whether you need professional negligence indemnity, please write to us and seek advice.</p>
<br>
                            <p>If you do require Professional Negligence indemnity for any work, we may be able to refer you to specialist insurance brokers from who you may separately purchase this cover.</p>
                        </div>

                        <hr>

                        <form id="step4Form" class="row g-3">

                            <div class="col-md-12">
                                <label class="form-label d-block">Do you require Professional Negligence indemnity?</label>

                                <div class="d-flex gap-4 align-items-center">
                                    <div class="form-check app-form-checkbox-custom-sec">
                                        <input class="form-check-input pni-option" type="checkbox" id="pni-yes"
                                            name="pni_required_yes" value="yes">
                                        <label class="form-check-label" for="pni-yes">Yes</label>
                                    </div>

                                    <div class="form-check app-form-checkbox-custom-sec">
                                        <input class="form-check-input pni-option" type="checkbox" id="pni-no"
                                            name="pni_required_no" value="no">
                                        <label class="form-check-label" for="pni-no">No</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="row app-form-custom-btn-row">

                                    <div class="col-4">
                                        <button type="button" value="save-draft"
                                            class="form-control btn-back app-form-btn">Previous</button>
                                    </div>

                                    <div class="col-4">
                                        <button type="button" value="save-draft"
                                            class="form-control app-form-btn">Save Draft</button>
                                    </div>

                                    <div class="col-4">
                                        <button type="button" value="Continue"
                                            class="btn-next btn-success form-control app-form-btn">Continue</button>
                                    </div>

                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- STEP 5 -->
                    <div id="step5Box" class="slider-step af-border-sec" data-next-step="step6Box" data-back-to="step4Box" data-step="5">

                        <div class="application-form-title-sec creative-med-title">
                            <div class="title-icon"><i class="fa-solid fa-user"></i></div>
                            <h3>Step 5</h3>
                        </div>

                        <div class="app-form-title-subsec">
                            <h4>Declaration of Pre-existing professional Issues</h4>
                            <p>We do not provide cover for professional issues that have existed before commencement of membership. Please make a full declaration of existing or previous issues:</p>
                        </div>

                        <hr>

                        <form id="step5Form" class="row g-3">

                            <div class="col-12">
                                <label class="form-label"><strong>Q.</strong> Please provide details of any concerns raised about your conduct, capability or health in the past five (5) years. This should include any formal and/or disciplinary investigation by your contracting body, your employer or those who hold your performer's list registration.</label>
                                <textarea class="form-control app-form-textarea-custom-sec" id="pre-issue-q31"
                                    rows="4" placeholder="Enter details here..." name="issue_q31"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label"><strong>Q.</strong> Are you aware of any matters that may result in or have resulted in a claim or complaint being made against you? Please provide full details. Not disclosing information that we consider relevant may invalidate your membership. Therefore, if you are unsure if certain information you have would qualify to be stated then please do state that here:</label>
                                <textarea class="form-control app-form-textarea-custom-sec" id="pre-issue-q32"
                                    rows="4" placeholder="Enter details here..." name="issue_q32"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label"><strong>Q.</strong> Have you been subject to any Employer's disciplinary investigation, inquiry or other proceedings, GMC/GDC investigation, inquiry or other proceedings, Coroners' Inquest or Fatal Accident Inquiry and/or criminal prosecution in the past ten (10) years?</label>
                                <textarea class="form-control app-form-textarea-custom-sec" id="pre-issue-q33"
                                    rows="4" placeholder="Enter details here..." name="issue_q33"></textarea>
                            </div>

                            <div class="col-md-12">
                                <div class="row app-form-custom-btn-row">

                                    <div class="col-4">
                                        <button type="button" value="save-draft"
                                            class="form-control btn-back app-form-btn">Previous</button>
                                    </div>

                                    <div class="col-4">
                                        <button type="button" value="save-draft"
                                            class="form-control app-form-btn">Save Draft</button>
                                    </div>

                                    <div class="col-4">
                                        <button type="button" value="Continue"
                                            class="btn-next btn-success form-control app-form-btn">Continue</button>
                                    </div>

                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- STEP 6 -->
                    <div id="step6Box" class="slider-step af-border-sec" data-next-step="step7Box" data-back-to="step5Box" data-step="6">

                        <div class="application-form-title-sec creative-med-title">
                            <div class="title-icon"><i class="fa-solid fa-user"></i></div>
                            <h3>Step 6</h3>
                        </div>

                        <div class="app-form-title-subsec"><h4>Claims Information</h4></div>

                        <form id="step6Form" class="row g-3">

                            <div class="col-12">
                                <label class="form-label"><strong>Q.</strong> Have any claims or complaints relating to your professional work been made or threatened against you in the past three (3) years? If so, please provide details:</label>
                                <textarea class="form-control app-form-textarea-custom-sec" id="claims-q1"
                                    rows="4" placeholder="Enter details here..." name="claims_q1" required></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label"><strong>Q.</strong> Are you aware of any acts, errors, omissions, incidents, events or circumstances which may give rise to a claim, investigation or complaint against you? If so, please provide details:</label>
                                <textarea class="form-control app-form-textarea-custom-sec" id="claims-q2"
                                    rows="4" placeholder="Enter details here..." name="claims_q2" required></textarea>
                            </div>

                            <hr>

                            <div class="app-form-title-subsec"><h4>Previous Union or Defence Organisation Membership</h4></div>

                            <div class="col-12">
                                <label class="form-label"><strong>Q.</strong> Have you ever had membership or cover cancelled, declined or refused to be renewed by a professional membership organisation or provider of professional indemnity? If so, please provide details:</label>
                                <textarea class="form-control app-form-textarea-custom-sec"
                                    rows="4" placeholder="Enter details here..." name="membership_cancelled"></textarea>
                            </div>

                            <div class="col-12"><p><strong>Q.</strong> Please provide details of the following in respect of any policy or membership that you have or which is expiring at the time of joining our membership:</p></div>

                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" id="prev-membership-name"
                                    placeholder="Name of the membership or defence organisation"
                                    name="previous_membership_name">
                            </div>

                            <div class="col-md-6 mb-3">
                            <input type="text"
                                class="form-control"
                                id="prev-membership-expiry"
                                placeholder="Expiration Date of membership/policy"
                                name="previous_membership_expiry">
                        </div>


                            <div class="col-md-12">
                                <div class="row app-form-custom-btn-row">

                                    <div class="col-4">
                                        <button type="button" value="save-draft"
                                            class="form-control btn-back app-form-btn">Previous</button>
                                    </div>

                                    <div class="col-4">
                                        <button type="button" value="save-draft"
                                            class="form-control app-form-btn">Save Draft</button>
                                    </div>

                                    <div class="col-4">
                                        <button type="button" value="Continue"
                                            class="btn-next btn-success form-control app-form-btn">Continue</button>
                                    </div>

                                </div>
                            </div>

                        </form>
                    </div>
                   <!-- STEP 7 (PREVIEW - FINAL) -->
                 <div id="step7Box" class="slider-step af-border-sec" data-back-to="step6Box" data-step="7">

                        <div class="application-form-title-sec creative-med-title">
                            <div class="title-icon"><i class="fa-solid fa-user"></i></div>
                            <h3>Step 7</h3>
                        </div>

                        <!-- Preview Section -->
                        <div class="app-form-title-subsec">
                            <h4>Preview Your Application</h4>
                            <p>Please review all information you entered in Steps 1 to 6.</p>
                        </div>

                        <div id="previewContainer" class="preview-summary-box" style="padding:15px; background:#f9f9f9; border-radius:6px;">
                            <!-- Filled by JS -->
                        </div>

                        <hr>

                        <!-- Terms -->
                        <div class="col-12 mb-3">
                            <div class="form-check app-form-checkbox-custom-sec">
                                <input class="form-check-input" type="checkbox" id="terms_checkbox">
                                <label class="form-check-label" for="terms_checkbox">
                                    I agree that the above information is correct.
                                </label>
                            </div>
                            <small id="termsError" style="color:red; display:none;">You must accept the terms before submitting.</small>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-12">
                            <div class="row app-form-custom-btn-row">

                                <div class="col-4">
                                    <button type="button" class="form-control btn-back app-form-btn">Previous</button>
                                </div>

                                <div class="col-4">
                                    <button type="button" value="save-draft" class="form-control app-form-btn">Save Draft</button>
                                </div>

                                <div class="col-4">
                                    <button type="button" value="submit" id="finalSubmitBtn" class="btn-success form-control app-form-btn">
                                        Submit
                                    </button>
                                </div>

                            </div>
                        </div>

                </div>
            </div>
        </div>

    </div>
    </div>
</section>

  </main>
        @include('components.frontend.footer')

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!--<div id="preloader"></div>-->

      @include('components.frontend.main-js')  

<!-- ============================================================
     SAVE DRAFT MODAL HTML
============================================================ -->
<div id="draftModalOverlay" role="dialog" aria-modal="true" aria-labelledby="draftModalTitle">
  <div id="draftModal">

    <div id="draftModalHeader">
      <h2 id="draftModalTitle">
        <i class="fa-solid fa-floppy-disk"></i>
        Draft Saved
      </h2>
      <div style="display:flex;align-items:center;gap:10px;">
        <div id="draftExpiryBadge">
          <i class="fa-solid fa-clock"></i>
          <span id="draftExpiryText">Expires in 24h</span>
        </div>
        <button id="draftModalCloseBtn" aria-label="Close">&times;</button>
      </div>
    </div>

    <div id="draftModalBanner">
      <i class="fa-solid fa-circle-check"></i>
      Your progress has been saved. You can return anytime within 24 hours to continue.
    </div>

    <div id="draftModalBody"></div>

    <div id="draftModalFooter">
      <button id="draftClearBtn">
        <i class="fa-solid fa-trash" style="margin-right:5px;"></i>Clear Draft
      </button>
      <button id="draftCloseFooterBtn">Close</button>
    </div>

  </div>
</div>
<!-- END SAVE DRAFT MODAL HTML -->

      <script>
          document.addEventListener("click", function (e) {
    if (e.target.classList.contains("btn-next")) {
        setTimeout(function () {
            const target = document.querySelector(".custom-top-sec");
            if (target) {
                target.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        }, 200);
    }
});
      </script>
      
<script>
document.addEventListener("DOMContentLoaded", function () {

    const apiKey = "PP19-FH99-ZC91-NN35";
    const postalInput = document.getElementById("app-form-postal-code");
    const resultBox  = document.getElementById("address-results");

    let typingTimer;
    const delay = 400;

    function formatUKPostcode(postcode) {
        postcode = postcode.toUpperCase().replace(/\s+/g, '');
        if (postcode.length > 3) {
            postcode = postcode.slice(0, -3) + " " + postcode.slice(-3);
        }
        return postcode;
    }

    postalInput.addEventListener("input", function () {
        clearTimeout(typingTimer);
        let text = this.value.trim();
        text = formatUKPostcode(text);
        typingTimer = setTimeout(() => {
            if (text.length >= 3) {
                showSearching();
                findAddress(text);
            } else {
                resultBox.style.display = "none";
            }
        }, delay);
    });

    function showSearching() {
        resultBox.innerHTML = `<li class="list-group-item text-muted">🔍 Searching address...</li>`;
        resultBox.style.display = "block";
    }

    function findAddress(searchText, containerId = "") {
        const urlBase = "https://api.addressy.com/Capture/Interactive/Find/v1.10/json3.ws";
        let url = `${urlBase}?Key=${apiKey}&Text=${encodeURIComponent(searchText)}&IsMiddleware=true`;
        if (containerId) url += `&Container=${containerId}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                resultBox.innerHTML = "";
                if (!data.Items || data.Items.length === 0) {
                    resultBox.innerHTML = `<li class="list-group-item text-danger">❌ No address found</li>`;
                    resultBox.style.display = "block";
                    return;
                }
                resultBox.style.display = "block";
                data.Items.forEach(item => {
                    const li = document.createElement("li");
                    li.className = "list-group-item list-group-item-action";
                    li.innerText = item.Text + (item.Description ? " - " + item.Description : "");
                    li.addEventListener("click", () => {
                        if (item.Type !== "Address") {
                            showSearching();
                            findAddress(searchText, item.Id);
                        } else {
                            postalInput.value = item.Text;
                            retrieveAddress(item.Id);
                            resultBox.innerHTML = "";
                            resultBox.style.display = "none";
                        }
                    });
                    resultBox.appendChild(li);
                });
            })
            .catch(err => {
                console.error("Find API Error:", err);
                resultBox.innerHTML = `<li class="list-group-item text-danger">⚠️ Error fetching address</li>`;
                resultBox.style.display = "block";
            });
    }

    function retrieveAddress(id) {
        const url = `https://api.addressy.com/Capture/Interactive/Retrieve/v1.00/json3.ws?Key=${apiKey}&Id=${id}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data.Items || data.Items.length === 0) { alert("No address found"); return; }
                const addr = data.Items[0];
                document.getElementById("app-form-address-line-one").value = addr.Line1 || '';
                document.getElementById("app-form-address-line-two").value = addr.Line2 || '';
                document.getElementById("app-form-city").value = addr.City || '';
                postalInput.value = addr.PostalCode || postalInput.value;
                const countryName = addr.CountryName || '';
                const select = document.getElementById("app-form-country");
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].text.toLowerCase() === countryName.toLowerCase()) { select.selectedIndex = i; break; }
                }
            })
            .catch(err => console.log("Retrieve Error:", err));
    }

    document.addEventListener("click", function (e) {
        if (!resultBox.contains(e.target) && e.target !== postalInput) {
            resultBox.style.display = "none";
        }
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const apiKey = "PP19-FH99-ZC91-NN35";
    const contactPostalInput = document.getElementById("contact-postal-code");
    const contactResultBox   = document.getElementById("contact-address-results");

    let contactTypingTimer;
    const delay = 400;

    function formatUKPostcode(postcode) {
        postcode = postcode.toUpperCase().replace(/\s+/g, '');
        if (postcode.length > 3) { postcode = postcode.slice(0, -3) + " " + postcode.slice(-3); }
        return postcode;
    }

    contactPostalInput.addEventListener("input", function () {
        clearTimeout(contactTypingTimer);
        let text = this.value.trim();
        text = formatUKPostcode(text);
        contactTypingTimer = setTimeout(() => {
            if (text.length >= 3) { showContactSearching(); findContactAddress(text); }
            else { contactResultBox.style.display = "none"; }
        }, delay);
    });

    function showContactSearching() {
        contactResultBox.innerHTML = `<li class="list-group-item text-muted">🔍 Searching address...</li>`;
        contactResultBox.style.display = "block";
    }

    function findContactAddress(searchText, containerId = "") {
        const urlBase = "https://api.addressy.com/Capture/Interactive/Find/v1.10/json3.ws";
        let url = `${urlBase}?Key=${apiKey}&Text=${encodeURIComponent(searchText)}&IsMiddleware=true`;
        if (containerId) url += `&Container=${containerId}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                contactResultBox.innerHTML = "";
                if (!data.Items || data.Items.length === 0) {
                    contactResultBox.innerHTML = `<li class="list-group-item text-danger">❌ No address found</li>`;
                    contactResultBox.style.display = "block";
                    return;
                }
                contactResultBox.style.display = "block";
                data.Items.forEach(item => {
                    const li = document.createElement("li");
                    li.className = "list-group-item list-group-item-action";
                    li.innerText = item.Text + (item.Description ? " - " + item.Description : "");
                    li.addEventListener("click", () => {
                        if (item.Type !== "Address") { showContactSearching(); findContactAddress(searchText, item.Id); }
                        else { contactPostalInput.value = item.Text; retrieveContactAddress(item.Id); contactResultBox.innerHTML = ""; contactResultBox.style.display = "none"; }
                    });
                    contactResultBox.appendChild(li);
                });
            })
            .catch(err => {
                console.error("Contact Find API Error:", err);
                contactResultBox.innerHTML = `<li class="list-group-item text-danger">⚠️ Error fetching address</li>`;
                contactResultBox.style.display = "block";
            });
    }

    function retrieveContactAddress(id) {
        const url = `https://api.addressy.com/Capture/Interactive/Retrieve/v1.00/json3.ws?Key=${apiKey}&Id=${id}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data.Items || data.Items.length === 0) { alert("No address found"); return; }
                const addr = data.Items[0];
                document.getElementById("contact-address-line-one").value = addr.Line1 || '';
                document.getElementById("contact-address-line-two").value = addr.Line2 || '';
                document.getElementById("contact-city").value             = addr.City  || '';
                contactPostalInput.value                                   = addr.PostalCode || contactPostalInput.value;
                const countryName    = addr.CountryName || '';
                const contactCountry = document.getElementById("contact-country");
                for (let i = 0; i < contactCountry.options.length; i++) {
                    if (contactCountry.options[i].text.toLowerCase() === countryName.toLowerCase()) { contactCountry.selectedIndex = i; break; }
                }
            })
            .catch(err => console.error("Contact Retrieve Error:", err));
    }

    document.addEventListener("click", function (e) {
        if (!contactResultBox.contains(e.target) && e.target !== contactPostalInput) {
            contactResultBox.style.display = "none";
        }
    });
});
</script>

<script>
/* ============================================================
=  STEP 7 - PREVIEW GENERATOR
============================================================ */
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".btn-next").forEach(btn => {
        btn.addEventListener("click", function () {
            const nextStep = this.dataset.nextStep || this.closest(".slider-step").dataset.nextStep;
            if (nextStep === "step7Box") { generatePreview(); }
        });
    });
});

function generatePreview() {
    const previewBox = document.getElementById("previewContainer");
    if (!previewBox) return;
    previewBox.innerHTML = "";
    const fields = document.querySelectorAll(
        "#step1box [name],#step2Box [name],#step3Box [name],#step4Box [name],#step5Box [name],#step6Box [name]"
    );
    let html = `<div class="preview-section-title">Application Summary</div>`;
    html += `<table class="preview-table">`;
    fields.forEach(field => {
        let labelText = "";
        if (field.id) { const lbl = document.querySelector(`label[for="${field.id}"]`); if (lbl) labelText = lbl.innerText.trim(); }
        if (!labelText) { const parentLabel = field.closest("label"); if (parentLabel) labelText = parentLabel.innerText.trim(); }
        if (!labelText) { const parentDiv = field.closest(".col-12, .col-md-6, .col-md-12"); if (parentDiv) { const lbl = parentDiv.querySelector("label"); if (lbl) labelText = lbl.innerText.trim(); } }
        if (!labelText && field.placeholder) labelText = field.placeholder;
        if (!labelText) { labelText = field.name.replace(/_/g, " ").toUpperCase(); }
        let value = "";
        if (field.name === "pni_required_no") { return; }
        if (field.type === "checkbox") {
            if (field.name === "pni_required_yes" || field.name === "pni_required_no") {
                const yes = document.querySelector('input[name="pni_required_yes"]')?.checked;
                const no  = document.querySelector('input[name="pni_required_no"]')?.checked;
                value = yes ? "Yes" : no ? "No" : "<em>NA</em>";
                labelText = "Do you require Professional Negligence indemnity?";
            } else {
                value = field.checked ? "Yes" : "No";
            }
        } else if (field.type === "file") {
            value = field.files.length ? field.files[0].name : "Not uploaded";
        } else if (field.tagName.toLowerCase() === "select") {
            value = field.selectedIndex > 0 ? field.options[field.selectedIndex].text.toUpperCase() : "<em>NA</em>";
        } else {
            value = field.value && field.value.trim() !== "" ? field.value : "<em>NA</em>";
        }
        html += `<tr><td class="preview-label">${labelText}</td><td class="preview-value">${value}</td></tr>`;
    });
    html += `</table>`;
    previewBox.innerHTML = html;
}

function fillFormFromDB(app) {
    if (!app) return;
    const steps = ["step1","step2","step3","step4","step5","step6"];
    steps.forEach(stepKey => {
        if (!app[stepKey]) return;
        let stepData = {};
        try { stepData = typeof app[stepKey] === "string" ? JSON.parse(app[stepKey]) : app[stepKey]; }
        catch (e) { console.error("Invalid JSON in " + stepKey); return; }
        Object.entries(stepData).forEach(([name, value]) => {
            const fields = document.querySelectorAll(`[name="${name}"]`);
            fields.forEach(field => {
                if (field.type === "checkbox") { field.checked = value == 1 || value === true; }
                else if (field.type === "radio") { if (field.value == value) field.checked = true; }
                else if (field.type !== "file") { field.value = value ?? ""; }
            });
        });
    });
}

document.getElementById("finalSubmitBtn").addEventListener("click", async function () {
    if (!document.getElementById("terms_checkbox").checked) {
        document.getElementById("termsError").style.display = "block";
        return;
    }
    document.getElementById("termsError").style.display = "none";
    Swal.fire({ title: "Submitting...", text: "Please wait", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        let fd = new FormData();
        const fields = document.querySelectorAll("#step1box [name],#step2Box [name],#step3Box [name],#step4Box [name],#step5Box [name],#step6Box [name]");
        fields.forEach(field => {
            let value = "";
            if (field.type === "checkbox") { value = field.checked ? 1 : 0; }
            else if (field.type === "file") { if (field.files.length > 0) { fd.append(`files[${field.name}]`, field.files[0]); } return; }
            else { value = field.value; }
            fd.append(`data[${field.name}]`, value);
        });
        const res = await fetch("{{ route('application.submit') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
            body: fd
        });
        const result = await res.json();
        if (result.status === "success") {
            Swal.fire({ icon: "success", title: "Application Submitted!", timer: 2000, showConfirmButton: false });
            setTimeout(() => { window.location.href = "/Signup-form/" + result.user_id; }, 1800);
        } else {
            Swal.fire("Error", result.message, "error");
        }
    } catch (e) {
        Swal.fire("Error", "Server error. Please try again.", "error");
    }
});
</script>

      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* =========================================================
=  GLOBAL CONFIG
========================================================= */
let currentStep = 1;
const totalSteps = 6;
const stepBoxes = { 1:"step1box",2:"step2Box",3:"step3Box",4:"step4Box",5:"step5Box",6:"step6Box",7:"step7Box" };

/* =========================================================
=  SHOW/HIDE STEPS
========================================================= */
function showStep(step, direction="forward"){
    const box = document.getElementById(stepBoxes[step]);
    if(!box) return;
    document.querySelectorAll(".slider-step").forEach(el=>{
        if(el!==box){ el.classList.remove("active"); el.setAttribute("data-anim", direction==="forward"?"hide-to--left":"hide-to--right"); }
    });
    box.classList.add("active");
    box.setAttribute("data-anim", direction==="forward"?"show-from--right":"show-from--left");
    updateStepUI(step);
    updateFormHeight();
}

/* =========================================================
=  PROGRESS BAR
========================================================= */
function updateStepUI(step){
    const wrap  = document.getElementById("progressWrapper");
    const fill  = document.querySelector(".progress-fill");
    const items = document.querySelectorAll(".progress-steps li");
    if(step===7){ wrap.style.display="none"; return; } else wrap.style.display="block";
    fill.style.width = ((step-1)/(totalSteps-1))*100+"%";
    items.forEach((li,i)=>{
        li.classList.remove("active","completed");
        if(i+1<step) li.classList.add("completed");
        if(i+1===step) li.classList.add("active");
    });
}

/* =========================================================
=  HEIGHT & SCROLL
========================================================= */
function updateFormHeight() {
    const active = document.querySelector(".slider-step.active");
    const wrap   = document.getElementById("form-step-wrap");
    if (active && wrap) {
        wrap.style.minHeight = (active.scrollHeight + 20) + "px";
        wrap.style.overflowY = "auto";
        wrap.style.overflowX = "hidden";
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const wrapper = document.getElementById("form-step-wrap");
    wrapper.addEventListener("click", function(e) {
        const target   = e.target;
        const btnNext  = target.closest(".btn-next");
        const btnBack  = target.closest(".btn-back");
        const btnDraft = target.closest(".btn-save-draft");
        if (btnNext || btnBack || btnDraft) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            if (btnNext || btnBack) {
                const currentStepEl = (btnNext || btnBack).closest(".slider-step");
                wrapper.style.minHeight = wrapper.offsetHeight + "px";
                const targetId   = btnNext ? btnNext.getAttribute("data-next-step") : btnBack.getAttribute("data-back-to");
                const targetStep = document.getElementById(targetId);
                if (targetStep) { currentStepEl.classList.remove("active"); targetStep.classList.add("active"); setTimeout(updateFormHeight, 50); }
            }
        }
    });
    updateFormHeight();
    wrapper.style.display = "block";
});

/* =========================================================
=  ERROR HANDLING
========================================================= */
function showError(id, msg){
    const el = document.getElementById(id); if(!el) return;
    let err = el.nextElementSibling;
    if(!err || !err.classList.contains("error-msg")){
        err = document.createElement("div");
        err.classList.add("error-msg");
        err.style.color = "red"; err.style.fontSize = "13px";
        el.parentNode.appendChild(err);
    }
    err.innerText = msg; el.classList.add("is-invalid");
}
function clearErrors(){
    document.querySelectorAll(".error-msg").forEach(e=>e.remove());
    document.querySelectorAll(".form-control,.form-select,textarea").forEach(e=>e.classList.remove("is-invalid"));
}
const isEmail = e => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);

/* =========================================================
=  STEP VALIDATION
========================================================= */
function validateStep(step){
    clearErrors(); let valid = true;
    const val = id => document.getElementById(id)?.value.trim();
    if(step===1){
        if(!val("gmc-gdc-select-sec"))          { showError("gmc-gdc-select-sec","Required");          valid=false; }
        if(!val("gmc-gdc-registration-number")) { showError("gmc-gdc-registration-number","Required"); valid=false; }
        if(!val("af-registration-year-picker")) { showError("af-registration-year-picker","Required");  valid=false; }
        if(!val("app-form-title")) { showError("app-form-title","Required"); valid=false; }
        if(!val("af-qualification-year-picker")){ showError("af-qualification-year-picker","Required"); valid=false; }
        if(!val("app-form-first-name"))         { showError("app-form-first-name","Required");          valid=false; }
        if(!val("app-form-last-name"))          { showError("app-form-last-name","Required");           valid=false; }
        if(!val("app-form-date-of-birth"))      { showError("app-form-date-of-birth","Required");       valid=false; }
        if(!val("gender-select"))               { showError("gender-select","Required");                valid=false; }
        if(!val("app-form-address-line-one"))   { showError("app-form-address-line-one","Required");    valid=false; }
        if(!val("app-form-city"))               { showError("app-form-city","Required");                valid=false; }
        if(!val("app-form-country"))            { showError("app-form-country","Required");             valid=false; }
        if(!val("app-form-postal-code"))        { showError("app-form-postal-code","Required");         valid=false; }
        const sameAsAbove = document.getElementById("same-address-checkbox");
        if (sameAsAbove && !sameAsAbove.checked) {
            if (!val("contact-postal-code"))      { showError("contact-postal-code","Required");      valid=false; }
            if (!val("contact-address-line-one")) { showError("contact-address-line-one","Required"); valid=false; }
            if (!val("contact-city"))             { showError("contact-city","Required");             valid=false; }
            if (!val("contact-country"))          { showError("contact-country","Required");          valid=false; }
        }
    }
    if(step===2){
        if(!val("app-form-primary-email"))   { showError("app-form-primary-email","Required");   valid=false; }
        if(!val("app-form-mobile-number"))   { showError("app-form-mobile-number","Required");   valid=false; }
        else if(!isEmail(val("app-form-primary-email"))) { showError("app-form-primary-email","Required"); valid=false; }
        if(!val("app-form-select-username")) { showError("app-form-select-username","Required"); valid=false; }
        if(!val("app-form-select-password")) { showError("app-form-select-password","Required"); valid=false; }
        if(val("app-form-select-password")!==val("app-form-verify-password")) { showError("app-form-verify-password","Password Not Match"); valid=false; }
    }
    if(step===3){
        if(!val("app-form-desc-of-current-role")) { showError("app-form-desc-of-current-role","Required"); valid=false; }
        if(!val("emp-status")) { showError("emp-status","Required"); valid=false; }
        if(val("emp-status")==="employed"){
            if(!val("current-employer"))  { showError("current-employer","Required");  valid=false; }
            if(!val("employment-grade"))  { showError("employment-grade","Required");  valid=false; }
        }
    }
    if(step===4 && !document.querySelector(".pni-option:checked")) {
        document.querySelectorAll(".pni-option").forEach(el=>{ showError(el.id,"Required"); });
        valid=false;
    }
    if(step===5){
        ["pre-issue-q31","pre-issue-q32","pre-issue-q33"].forEach(id=>{ if(!val(id)){ showError(id,"Required"); valid=false; } });
    }
    if(step===6){
        if(!val("claims-q1")) { showError("claims-q1","Required"); valid=false; }
        if(!val("claims-q2")) { showError("claims-q2","Required"); valid=false; }
    }
    return valid;
}

/* =========================================================
=  NEXT BUTTON
========================================================= */
document.querySelectorAll(".btn-next").forEach(function (btn) {
    btn.addEventListener("click", async function () {
        if (!validateStep(currentStep)) return;
        var box  = document.querySelector(".slider-step[data-step='" + currentStep + "']");
        var form = box.querySelector("form");
        var isFinal = (btn.value === "submit" && currentStep === 6);
        var fd = new FormData();
        fd.append("step", currentStep);
        form.querySelectorAll("[name]").forEach(function (inp) {
            if (inp.type === "file") { if (inp.files.length > 0) { fd.append("data[" + inp.name + "]", inp.files[0]); } }
            else if (inp.type === "checkbox") { fd.append("data[" + inp.name + "]", inp.checked ? 1 : 0); }
            else { fd.append("data[" + inp.name + "]", inp.value); }
        });
        try {
            var save = await fetch("{{ route('application.saveStep') }}", {
                method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }, body: fd
            });
            var res = await save.json();
            if (res.status !== "success") { alert("Error saving step"); return; }
            if (isFinal) {
                var fin = await fetch("{{ route('application.submit') }}", {
                    method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
                });
                var ok = await fin.json();
                if (ok.status === "success") { alert("Application submitted successfully"); showStep(7, "forward"); }
                return;
            }
            currentStep++;
            showStep(currentStep, "forward");
        } catch (e) { console.error("Error:", e); alert("Something went wrong. Check console."); }
    });
});

/* =========================================================
=  BACK BUTTON
========================================================= */
document.querySelectorAll(".btn-back").forEach(btn=>{
    btn.addEventListener("click", ()=>{ if(currentStep>1){ currentStep--; showStep(currentStep,"backward"); } });
});

/* =========================================================
=  TOAST
========================================================= */
function showToast(msg){
    const c = document.getElementById("toast-container");
    const t = document.createElement("div");
    t.classList.add("toast"); t.innerText = msg; c.appendChild(t);
    setTimeout(()=>t.classList.add("show"),100);
    setTimeout(()=>{ t.classList.remove("show"); setTimeout(()=>t.remove(),500); },3000);
}

/* =========================================================
=  PASSWORD TOGGLE
========================================================= */
document.querySelectorAll(".toggle-password").forEach(i=>{
    i.addEventListener("click",()=>{
        const inp = document.querySelector(i.dataset.target);
        inp.type = inp.type==="password"?"text":"password";
        i.classList.toggle("fa-eye-slash");
    });
});

/* =========================================================
=  COPY ADDRESS (SAME AS ABOVE) — single listener
========================================================= */
document.getElementById("same-address-checkbox")?.addEventListener("change", function () {
    if (this.checked) {
        document.getElementById("contact-postal-code").value       = document.getElementById("app-form-postal-code").value;
        document.getElementById("contact-address-line-one").value  = document.getElementById("app-form-address-line-one").value;
        document.getElementById("contact-address-line-two").value  = document.getElementById("app-form-address-line-two").value;
        document.getElementById("contact-city").value              = document.getElementById("app-form-city").value;
        const mainCountry    = document.getElementById("app-form-country").value;
        const contactCountry = document.getElementById("contact-country");
        contactCountry.value = mainCountry;
        contactCountry.dispatchEvent(new Event("change"));
    } else {
        document.getElementById("contact-postal-code").value      = "";
        document.getElementById("contact-address-line-one").value = "";
        document.getElementById("contact-address-line-two").value = "";
        document.getElementById("contact-city").value             = "";
        document.getElementById("contact-country").value          = "";
    }
});

/* =========================================================
=  EMPLOYMENT TOGGLE
========================================================= */
document.getElementById("emp-status")?.addEventListener("change", function(){
    document.querySelectorAll(".employed-field").forEach(e=>e.classList.toggle("d-none", this.value!=="employed"));
});

/* =========================================================
=  PNI CONTROL
========================================================= */
document.querySelectorAll(".pni-option").forEach(o=>{
    o.addEventListener("change",()=>document.querySelectorAll(".pni-option").forEach(x=>{ if(x!==o) x.checked=false; }));
});

/* =========================================================
=  DATE PICKERS
========================================================= */
function setPicker(className,type){
    const i = document.querySelector(`.${className}`);
    if(i){ i.addEventListener("focus",()=>i.type=type); i.addEventListener("blur",()=>{ if(!i.value) i.type="text"; }); }
}
setPicker("app-date-picker","date");

(function(){
    const currentYear = new Date().getFullYear();
    const startYear   = 1950;
    ["af-registration-year-picker","af-qualification-year-picker"].forEach(id => {
        const sel = document.getElementById(id);
        if(!sel) return;
        for(let y = currentYear; y >= startYear; y--){
            const opt = document.createElement("option");
            opt.value = y; opt.textContent = y;
            sel.appendChild(opt);
        }
    });
})();

window.addEventListener("DOMContentLoaded", async () => {
    try {
        const res  = await fetch("{{ url('/application/get-last-step') }}");
        const data = await res.json();
        currentStep = data.step ? data.step : 1;
        if (data.data) { fillFormFromDB(data.data); }
        showStep(currentStep);
        if (currentStep == 7) { generatePreview(); }
    } catch (e) { console.error("Error loading step", e); showStep(1); }
});

/* =========================================================
=  SAVE DRAFT — localStorage + MODAL (24h TTL)
========================================================= */
(function () {
    const DRAFT_KEY = "applicationDraft";
    const TTL       = 24 * 60 * 60 * 1000;

    const STEP_LABELS = {
        1: "Step 1 — Your Details",
        2: "Step 2 — Contact & Security",
        3: "Step 3 — Job Title & Grade",
        4: "Step 4 — Professional Negligence",
        5: "Step 5 — Pre-existing Issues",
        6: "Step 6 — Claims Information",
        7: "Step 7 — Preview"
    };

    const TEXTAREA_FIELDS = new Set([
        "current_role_description","issue_q31","issue_q32","issue_q33",
        "claims_q1","claims_q2","membership_cancelled"
    ]);

    const SENSITIVE_FIELDS = new Set(["password","confirm_password"]);

    const FIELD_LABELS = {
        gmc_gdc_type:"GMC / GDC", gmc_gdc_number:"Registration Number",
        registration_year:"Registration Year", qualification_year:"Qualification Year",
        specialty:"Specialty", professional_qualification:"Professional Qualification",
        title:"Title", first_name:"First Name", middle_name:"Middle Name",
        last_name:"Last Name", date_of_birth:"Date of Birth", gender:"Gender",
        "postal-code":"Postal Code", address_line_1:"Address Line 1",
        address_line_2:"Address Line 2", city:"City", country:"Country",
        same_as_main_address:"Same As Above Address",
        contact_postal_code:"Contact Postal Code",
        contact_address_line_1:"Contact Address 1", contact_address_line_2:"Contact Address 2",
        contact_city:"Contact City", contact_country:"Contact Country",
        telephone_day:"Telephone (Day)", telephone_evening:"Telephone (Evening)",
        mobile_number:"Mobile Number", primary_email:"Primary Email",
        secondary_email:"Secondary Email", username:"Username",
        password:"Password", confirm_password:"Confirm Password",
        employment_status:"Employment Status", current_employer:"Current Employer",
        employment_grade:"Employment Grade", lead_employer:"Main Place of Work",
        current_role_description:"Description of Current Role",
        pni_required_yes:"PNI Required",
        issue_q31:"Conduct / Capability Concerns (5 yrs)",
        issue_q32:"Potential Claims or Complaints",
        issue_q33:"Disciplinary / GMC / GDC Proceedings (10 yrs)",
        claims_q1:"Claims or Complaints (3 yrs)",
        claims_q2:"Potential Acts / Errors / Incidents",
        membership_cancelled:"Membership Cancelled / Refused",
        previous_membership_name:"Previous Membership Name",
        previous_membership_expiry:"Previous Membership Expiry"
    };

    /* --- helpers --- */
    function saveToDraft(step, data) {
        localStorage.setItem(DRAFT_KEY, JSON.stringify({ step, data, savedAt: Date.now() }));
    }

    window.loadDraft = function () {
        try {
            const raw = localStorage.getItem(DRAFT_KEY);
            if (!raw) return null;
            const draft = JSON.parse(raw);
            if (!draft.savedAt || Date.now() - draft.savedAt > TTL) {
                localStorage.removeItem(DRAFT_KEY); return null;
            }
            return draft;
        } catch { return null; }
    };

    function formatExpiry(savedAt) {
        const rem  = TTL - (Date.now() - savedAt);
        const hrs  = Math.floor(rem / 3600000);
        const mins = Math.floor((rem % 3600000) / 60000);
        if (hrs  > 0) return `Expires in ${hrs}h ${mins}m`;
        if (mins > 0) return `Expires in ${mins}m`;
        return "Expires soon";
    }

    /* --- modal body builder --- */
    function buildModalBody(draft) {
        const body = document.getElementById("draftModalBody");
        body.innerHTML = "";

        if (!draft || !draft.data || !Object.keys(draft.data).length) {
            body.innerHTML = `<div class="dm-empty"><i class="fa-solid fa-inbox" style="font-size:26px;display:block;margin-bottom:8px;color:#ccc;"></i>No data saved yet.</div>`;
            return;
        }

        const stepNum = draft.step || 1;
        let html = `<div class="dm-step-title">${STEP_LABELS[stepNum] || 'Step ' + stepNum}</div>`;
        html += `<div class="dm-grid">`;

        Object.entries(draft.data).forEach(([name, raw]) => {
            const value = String(raw ?? "").trim();
            if (!value || value === "0") return;
            const label   = FIELD_LABELS[name] || name.replace(/_/g, " ");
            const isTA    = TEXTAREA_FIELDS.has(name);
            const display = SENSITIVE_FIELDS.has(name) ? "••••••••" : value;
            html += `
                <div class="dm-field${isTA ? ' full' : ''}">
                    <div class="dm-field-label">${label}</div>
                    <div class="dm-field-value${isTA ? ' ta' : ''}">${display}</div>
                </div>`;
        });

        html += `</div>`;
        body.innerHTML = html;
    }

    /* --- open / close --- */
    function openModal(draft) {
        document.getElementById("draftExpiryText").textContent = draft ? formatExpiry(draft.savedAt) : "Expires in 24h";
        buildModalBody(draft);
        document.getElementById("draftModalOverlay").classList.add("open");
    }

    function closeModal() {
        document.getElementById("draftModalOverlay").classList.remove("open");
    }

    document.getElementById("draftModalCloseBtn").addEventListener("click", closeModal);
    document.getElementById("draftCloseFooterBtn").addEventListener("click", closeModal);
    document.getElementById("draftModalOverlay").addEventListener("click", function (e) {
        if (e.target === this) closeModal();
    });
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeModal();
    });
    document.getElementById("draftClearBtn").addEventListener("click", function () {
        localStorage.removeItem(DRAFT_KEY);
        closeModal();
    });

    /* --- hook every Save Draft button --- */
    document.querySelectorAll('button[value="save-draft"]').forEach(btn => {
        btn.addEventListener("click", function () {
            const stepBox = btn.closest(".slider-step");
            if (!stepBox) return;
            const step = parseInt(stepBox.dataset.step);
            const form = stepBox.querySelector("form");
            if (!form) return;

            const formData = {};
            form.querySelectorAll("[name]").forEach(input => {
                if (input.type === "checkbox") formData[input.name] = input.checked ? 1 : 0;
                else formData[input.name] = input.value;
            });

            saveToDraft(step, formData);
            openModal({ step, data: formData, savedAt: Date.now() });
        });
    });

    /* --- restore draft on page load (silent, no modal) --- */
    window.addEventListener("DOMContentLoaded", function () {
        const draft = loadDraft();
        if (!draft) return;
        const stepBox = document.querySelector(`.slider-step[data-step="${draft.step}"]`);
        if (!stepBox) return;
        const form = stepBox.querySelector("form");
        if (!form) return;
        Object.keys(draft.data).forEach(name => {
            const input = form.querySelector(`[name="${name}"]`);
            if (!input) return;
            if (input.type === "checkbox") input.checked = draft.data[name] == 1;
            else input.value = draft.data[name];
        });
    });

})();
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const today           = new Date();
    const eighteenYearsAgo = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
    const dobInput        = document.getElementById("app-form-date-of-birth");
    const dobError        = document.getElementById("dobError");

    flatpickr(dobInput, { dateFormat: "d/m/Y", allowInput: true, clickOpens: true, maxDate: eighteenYearsAgo });

    dobInput.addEventListener("blur", function () {
        const val = dobInput.value; if (!val) return;
        const parts = val.split("/"); if (parts.length !== 3) return;
        const dob = new Date(parseInt(parts[2],10), parseInt(parts[1],10)-1, parseInt(parts[0],10));
        if (dob > eighteenYearsAgo) { dobError.style.display = "block"; dobInput.value = ""; }
        else { dobError.style.display = "none"; }
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const empStatus      = document.getElementById("emp-status");
    const employedFields = document.querySelectorAll(".employed-field");
    const descField      = document.querySelector(".description-field");

    function toggleEmploymentFields() {
        const selected = empStatus.value;
        employedFields.forEach(el => el.style.display = "none");
        descField.style.display = "none";
        if (selected === "employed")      { employedFields.forEach(el => el.style.display = "block"); descField.style.display = "block"; }
        else if (selected === "self-employed") { descField.style.display = "block"; }
    }

    empStatus.addEventListener("change", toggleEmploymentFields);
    toggleEmploymentFields();
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function(){
    flatpickr("#prev-membership-expiry", { dateFormat: "d/m/Y", allowInput: true, clickOpens: true });
});
</script>

</body>
</html>