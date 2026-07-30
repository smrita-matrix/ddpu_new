<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Http;
// Backend controller
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\home\BannerDetailsController;
use App\Http\Controllers\Backend\home\AimDetailsController;
use App\Http\Controllers\Backend\home\WhychooseDetailsController;
use App\Http\Controllers\Backend\home\TestimonialsDetailsController;
use App\Http\Controllers\Backend\home\JoinmembershipDetailsController;
use App\Http\Controllers\Backend\home\CustomerDetailsController;
use App\Http\Controllers\Backend\home\CustomerPhysicalDetailsController;

use App\Http\Controllers\Backend\aboutus\WhatDDPUDetailsController;
use App\Http\Controllers\Backend\aboutus\StaffPersonnelDetailsController;
use App\Http\Controllers\Backend\aboutus\OurExperienceDetailsController;
use App\Http\Controllers\Backend\aboutus\OurmethodDetailsController;
use App\Http\Controllers\Backend\aboutus\SomeOfOurPastCasesDetailsController;
use App\Http\Controllers\Backend\aboutus\AboutusTestimonialsDetailsController;



use App\Http\Controllers\Backend\services\membershipController;
use App\Http\Controllers\Backend\services\DentistsController;
use App\Http\Controllers\Backend\services\GeneralPracticeController;
use App\Http\Controllers\Backend\services\ConsultantsDetailsController;
use App\Http\Controllers\Backend\services\SasDoctorsNonTrainingGradesController;
use App\Http\Controllers\Backend\services\TraineesController;
use App\Http\Controllers\Backend\services\TrustGradeHocAppointeesNHSController;
use App\Http\Controllers\Backend\services\PrivateSectorController;
use App\Http\Controllers\Backend\services\CompareUsController;
use App\Http\Controllers\Backend\services\ForNonMembershipController;


use App\Http\Controllers\Backend\membership\MembershipbenefitsController;
use App\Http\Controllers\Backend\membership\MembershipRatesOptionController;


use App\Http\Controllers\Backend\home\FooterDetailsController;
use App\Http\Controllers\Backend\home\MembershipDetailsController;
use App\Http\Controllers\Backend\home\FilesDetailsController;
use App\Http\Controllers\Backend\home\PaperlessDetailsController;
use App\Http\Controllers\Backend\home\SubmissionsRemindersController;
use App\Http\Controllers\Backend\home\TransactionDetailsController;
use App\Http\Controllers\FastPayController;



//frontend controller
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\JoinmembershipApplicationController;
use App\Http\Controllers\Frontend\SignupController;
use App\Http\Controllers\BankValidationController;


// Aboutus frontend controller
use App\Http\Controllers\Frontend\AboutusController;

// services frontend controller
use App\Http\Controllers\Frontend\ServicesController;

//membership frontend controller
use App\Http\Controllers\Frontend\MembershipdeatilpageController;

//contact frontend controller
use App\Http\Controllers\Frontend\ContactController;
use Illuminate\Support\Facades\Mail;

Route::get('/test-mail', function () {
    try {
        Mail::raw('Test email from DDPU site — if you got this, mail works.', function ($m) {
            $m->to('smrita@matrixbricks.com')->subject('DDPU mail test');
        });
        return 'SENT — check the inbox (and spam folder).';
    } catch (\Throwable $e) {
        return 'FAILED: ' . $e->getMessage();
    }
});

// Backend
Route::get('/admin-login', [LoginController::class, 'login'])->name('admin.login');
Route::post('/admin-login', [LoginController::class, 'authenticate'])->name('admin.authenticate');
Route::get('/admin-logout', [LoginController::class, 'logout'])->name('admin.logout');
Route::get('/change-password', [LoginController::class, 'change_password'])->name('admin.changepassword');
Route::post('/update-password', [LoginController::class, 'updatePassword'])->name('admin.updatepassword');
Route::get('/admin-register', [LoginController::class, 'register'])->name('admin.register');
Route::post('/register', [LoginController::class, 'authenticate_register'])->name('admin.register.authenticate');
Route::get('/download-pdf/{id}', 'PDFController@generatePDF');

//Backend home pages 
Route::resource('banner-details', BannerDetailsController::class);
Route::resource('aim-details', AimDetailsController::class);
Route::resource('whychoose-details', WhychooseDetailsController::class);
Route::resource('testimonials-details', TestimonialsDetailsController::class);
Route::resource('joinmembership-details', JoinmembershipDetailsController::class);


//Backend About us pages
Route::resource('what-is-ddpu-details', WhatDDPUDetailsController::class);
Route::resource('staff-personnel', StaffPersonnelDetailsController::class);
Route::resource('our-experienced', OurExperienceDetailsController::class);
Route::resource('our-methods', OurmethodDetailsController::class);
Route::resource('some-of-our-past-cases', SomeOfOurPastCasesDetailsController::class);
Route::resource('aboutus-testimonials-details', AboutusTestimonialsDetailsController::class);
Route::get('/mail-check', function () {
    return [
        'mailer' => config('mail.default'),
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
    ];
});
//Backend membership us pages
Route::resource('membership-details', membershipController::class);
Route::resource('dentists-details', DentistsController::class);
Route::resource('general-practice-details', GeneralPracticeController::class);
Route::resource('consultants-details', ConsultantsDetailsController::class);
Route::resource('sas-doctors-grades-details', SasDoctorsNonTrainingGradesController::class);
Route::resource('trainees-details', TraineesController::class);
Route::resource('trust-grade-details', TrustGradeHocAppointeesNHSController::class);
Route::resource('private-sectoracademic-details', PrivateSectorController::class);
Route::resource('compare-us-details', CompareUsController::class);
Route::resource('for-non-members-details', ForNonMembershipController::class);

//Backend mEMBERSHIP PAGES
Route::resource('membership-benefits-details', MembershipbenefitsController::class);
Route::resource('membership-rates-details', MembershipRatesOptionController::class);

Route::resource('footer-details', FooterDetailsController::class);

//Backend Customer
Route::get('/membership-detail', [MembershipDetailsController::class, 'index'])->name('membership.details');
Route::get('/admin/membership/{id}', [MembershipDetailsController::class, 'show'])->name('membership.show');
Route::get('/join-membership-details', [CustomerDetailsController::class, 'index'])
    ->name('customer-elctronic.details');
 
// NEW: Update start date
Route::post('/membership/start-date/update', [CustomerDetailsController::class, 'updateStartDate'])
    ->name('membership.startdate.update');
 
// NEW: Send mail (active = with PDF, inactive = without PDF)
Route::post('/membership/send-mail', [CustomerDetailsController::class, 'sendMail'])
    ->name('membership.sendmail');
 
// OPTIONAL: Auto-trigger route (call via scheduler or cron)
Route::get('/membership/auto-trigger-mails', [CustomerDetailsController::class, 'autoTriggerMails'])
    ->name('membership.autotrigger');
    
    Route::post('/membership/schedulemail', [CustomerDetailsController::class, 'scheduleMail'])
    ->name('membership.schedulemail');
    
    Route::post('/membership/renewal/schedulemail', [CustomerDetailsController::class, 'scheduleRenewalMail'])
    ->name('membership.renewal.schedulemail');
 
Route::post('/membership/renewal/sendmail', [CustomerDetailsController::class, 'sendRenewalMail'])
    ->name('membership.renewal.sendmail');
    
Route::get('/customer-details-physical', [CustomerPhysicalDetailsController::class, 'index'])->name('customer-physical.details');
Route::post('/customer-electronic/upload', [CustomerDetailsController::class, 'upload'])
    ->name('customer-electronic.upload');
Route::get('/admin/member-pdf/{id}', [CustomerDetailsController::class, 'downloadPdf'])
    ->name('admin.member.pdf');
Route::post('/admin/membership/update-renewal', [CustomerDetailsController::class, 'updateRenewal'])
     ->name('membership.renewal.update');
Route::post('admin/membership/update-price-renewal', [CustomerDetailsController::class, 'updatePriceRenewal'])->name('admin.membership.updatePriceRenewal');

Route::post('/admin/member/update-payment', [CustomerDetailsController::class, 'updatePayment'])
    ->name('admin.member.updatePayment');
    
Route::get('/customer-electronic/export/{type}', [CustomerDetailsController::class, 'export'])
    ->name('customer-electronic.export');

Route::get('/customer-physical/export/{type}', [CustomerPhysicalDetailsController::class, 'export'])
    ->name('customer-physical.export');
    
Route::post('membership/enddate/update', [CustomerDetailsController::class, 'updateEndDate'])
     ->name('membership.enddate.update');
     
Route::post('/admin/membership/status-update', 
    [CustomerDetailsController::class, 'updateStatus']
)->name('membership.status.update');

Route::post('/admin/member/delete', [CustomerDetailsController::class, 'deleteMember'])
    ->name('admin.member.delete');
 
//Backend Files
Route::get('/files-detail', [FilesDetailsController::class, 'index'])->name('files.details');
Route::post('/files/import',   [FilesDetailsController::class, 'import'])->name('files.import');
Route::get('/files/export/{id}', [FilesDetailsController::class, 'export'])->name('files.export');

Route::get('/download-pdf', [FilesDetailsController::class, 'generatePDF']);

//Backend FastPay Customers (live data from FastPay portal API)
Route::get('/fastpay/customers', [\App\Http\Controllers\Backend\home\FastpayCustomerController::class, 'index'])
    ->name('fastpay.customers');
Route::get('/fastpay/customers/{ddReference}', [\App\Http\Controllers\Backend\home\FastpayCustomerController::class, 'show'])
    ->where('ddReference', '.*')
    ->name('fastpay.customers.show');

//Backend Paperless
Route::get('/paperless-detail', [PaperlessDetailsController::class,'index'])->name('direct_debit.index');
Route::get('/create', [PaperlessDetailsController::class,'create'])->name('direct_debit.create');
Route::post('/store', [PaperlessDetailsController::class,'store'])->name('direct_debit.store');
Route::post('/download', [PaperlessDetailsController::class,'download'])->name('direct_debit.download');
Route::post('/process', [PaperlessDetailsController::class,'process'])->name('direct_debit.process');
Route::post('/mark-processed', [PaperlessDetailsController::class,'markProcessed'])->name('direct_debit.markProcessed');
Route::get('/edit/{id}', [PaperlessDetailsController::class, 'edit'])->name('direct_debit.edit');
Route::post('/update/{id}', [PaperlessDetailsController::class, 'update'])->name('direct_debit.update');


//Backend SubmissionsReminders
Route::get('/Submissions-schedules', [SubmissionsRemindersController::class,'index'])->name('Submissionsschedules.index');

//Backend Transaction
Route::get('/transaction-detail', [TransactionDetailsController::class,'index'])->name('transaction.details');

Route::get('/file-details/export', [TransactionDetailsController::class, 'exportCsv'])
    ->name('file.details.export');

// // Admin Routes with Middleware
Route::group(['middleware' => ['auth:web', \App\Http\Middleware\PreventBackHistoryMiddleware::class]], function () {
    Route::get('/dashboard', function () {
            return view('backend.dashboard'); 
        })->name('admin.dashboard');
});


// API locate

// Route::get('/login', function() {
//     return 'Login route placeholder';
// });


// Route::get('/loqate', function () {
//     return view('loqate-test');
// });

// Route::post('/loqate/lookup', function (Request $request) {
//     $key = env('LOQATE_API_KEY');
//     $address = $request->input('address');

//     $response = Http::get("https://api.addressy.com/Capture/Interactive/Find/v1.10/json3.ws", [
//         'Key' => $key,
//         'Text' => $address,
//         'IsMiddleware' => false
//     ]);

//     return response()->json($response->json());
// });



// Route::get('/', function () {return view('welcome');});
Route::get('/', [HomeController::class, 'home'])->name('frontend.index');
Route::get('/join-membership', [JoinmembershipApplicationController::class, 'index'])->name('joinmembership.form');

Route::get('/application-form', [JoinmembershipApplicationController::class, 'create'])->name('application.create');

Route::post('/application/save-step', [JoinmembershipApplicationController::class, 'saveStep'])
    ->name('application.saveStep');
Route::get('/application/get-last-step', [JoinmembershipApplicationController::class, 'getLastStep']);
Route::post('/application/submit', [JoinmembershipApplicationController::class, 'submitApplication'])
    ->name('application.submit');

Route::get('/application/saved', [JoinmembershipApplicationController::class, 'getSavedApplication'])
    ->name('application.saved');

Route::get('/Signup-form/{id}', [SignupController::class, 'index'])->name('signup.form');
Route::post('/signup/get-progress', [SignupController::class, 'getSignupProgress']);

Route::post('/update-address', [SignupController::class, 'updateAddress'])
        ->name('signup.updateAddress');

Route::get('/application/getLastStep', [JoinmembershipApplicationController::class, 'getLastStep'])
    ->name('application.getLastStep');

Route::post('/signup/step1-save', [SignupController::class, 'saveStep1']);
Route::post('/signup/final-submit', [SignupController::class, 'finalSubmit']);
Route::get('/thank-you', [SignupController::class, 'thankyou'])->name('thankyou');

// Route::get('/direct-debit-pdf-form', [SignupController::class, 'direct_debit'])->name('direct_debit.form');
Route::get('/direct-debit/{id}', [SignupController::class, 'direct_debit'])->name('direct_debit.pdf');
Route::get('/proxy-bank-validation', [BankValidationController::class, 'validateBank']);
Route::get('/signup/step2/pdf/{userId}', [SignupController::class, 'generatePdf']);

//fastpay testing

// Route::get('/upload', [FastPayController::class, 'index']);
// Route::post('/upload', [FastPayController::class, 'upload']);
// routes/web.php


Route::get('/fastpay', [FastPayController::class, 'form']);
Route::post('/fastpay-upload', [FastPayController::class, 'upload'])->name('fastpay.upload');

//About us al the pages frontend
Route::get('/what-is-ddpu', [AboutusController::class, 'index'])->name('frontend.whatisddpu');
Route::get('/staffs-personnel', [AboutusController::class, 'staffpersonnel'])->name('frontend.staffpersonnel');
Route::get('staff-personnel/{slug}', [AboutusController::class, 'show'])->name('staff.show');
Route::get('/our-experience', [AboutusController::class, 'ourexperience'])->name('frontend.ourexperience');
Route::get('/our-method', [AboutusController::class, 'ourmethod'])->name('frontend.ourmethod');
Route::get('/some-our-past-cases', [AboutusController::class, 'pastcases'])->name('frontend.pastcases');
Route::get('/testimonials', [AboutusController::class, 'testimonials'])->name('frontend.testimonials');


//frontend fetching services page
Route::get('/services-membership', [ServicesController::class, 'membership'])->name('frontend.services-membership');
Route::get('/services-dentists', [ServicesController::class, 'dentists'])->name('frontend.services-dentists');
Route::get('/general-practice', [ServicesController::class, 'general_practice'])->name('frontend.general-practice');
Route::get('/consultants-services', [ServicesController::class, 'consultants'])->name('frontend.consultants');
Route::get('/sas-doctors-and-other-non-training-grades', [ServicesController::class, 'sas_doctors'])->name('frontend.sas-doctors');
Route::get('/services-trainees', [ServicesController::class, 'trainees'])->name('frontend.trainees');
Route::get('/trainees-specialist-non-training-grades', [ServicesController::class, 'trustgradeDetails'])->name('frontend.trustgradeDetails');
Route::get('/private-sector-academic-specialities', [ServicesController::class, 'private_sector_academic_specialities'])->name('frontend.private_sector_academic_specialities');
Route::get('/compare-us', [ServicesController::class, 'compare_us'])->name('frontend.compare_us');
Route::get('/for-non-members', [ServicesController::class, 'for_non_members'])->name('frontend.for_non_members');


//fetching Membership tab
Route::get('/membership-benefits', [MembershipdeatilpageController::class, 'membership_benefits'])->name('frontend.membership_benefits');
Route::get('/membership-rates-and-options', [MembershipdeatilpageController::class, 'membership_rates'])->name('frontend.membership_rates');


//contact us fetching
Route::get('/contact', [ContactController::class, 'index'])->name('frontend.contact');
Route::post('/contact-send', [ContactController::class, 'send'])->name('contact.send');



// Cache clear via browser (no terminal needed). Visit: /clear-cache-9f3x7q
// Remove this route after go-live for security.
Route::get('/clear-cache-9f3x7q', function () {
    $out = [];
    foreach (['config:clear', 'route:clear', 'view:clear', 'cache:clear'] as $cmd) {
        try {
            \Illuminate\Support\Facades\Artisan::call($cmd);
            $out[] = "OK  {$cmd} -> " . trim(\Illuminate\Support\Facades\Artisan::output());
        } catch (\Throwable $e) {
            $out[] = "ERR {$cmd}: " . $e->getMessage();
        }
    }
    return '<pre>' . implode("\n", $out) . '</pre>';
});
