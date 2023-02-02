<?php

use App\Http\Controllers\AnesthetistController;
use App\Http\Controllers\AppointmentDraftController;
use App\Http\Controllers\DeallocateAppointmentController;
use App\Http\Controllers\HrmEmployeeLeaveController;
use App\Http\Controllers\HrmWeeklyScheduleController;
use App\Models\AppointmentDetail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\CodingController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BulletinController;
use App\Http\Controllers\BirthCodeController;
use App\Http\Controllers\HealthLinkController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ScheduleFeeController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PatientAlertController;
use App\Http\Controllers\PreAdmissionController;
use App\Http\Controllers\ScheduleItemController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\PatientRecallController;
use App\Http\Controllers\PatientAllergyController;
use App\Http\Controllers\PatientBillingController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\AppointmentTypeController;
use App\Http\Controllers\PatientDocumentController;
use App\Http\Controllers\NotificationTestController;
use App\Http\Controllers\DoctorAddressBookController;
use App\Http\Controllers\OrganizationAdminController;
use App\Http\Controllers\AnestheticQuestionController;
use App\Http\Controllers\OutgoingMessageLogController;
use App\Http\Controllers\PatientAlsoKnownAsController;
use App\Http\Controllers\UserAuthenticationController;
use App\Http\Controllers\AppointmentReferralController;
use App\Http\Controllers\HrmScheduleTimeslotController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\OrganizationSettingsController;
use App\Http\Controllers\UserProfileSignatureController;
use App\Http\Controllers\AppointmentSpecialistController;
use App\Http\Controllers\AppointmentPreAdmissionController;
use App\Http\Controllers\AppointmentSearchAvailableController;
use App\Http\Controllers\AppointmentTimeRequirementController;
use App\Http\Controllers\AppointmentAttendanceStatusController;
use App\Http\Controllers\AppointmentDetailController;
use App\Http\Controllers\AppointmentCollectingPersonController;
use App\Http\Controllers\AppointmentProcedureApprovalController;
use App\Http\Controllers\DocumentHeaderFooterTemplateController;
use App\Http\Controllers\AppointmentConfirmationStatusController;
use App\Http\Controllers\AppointmentInvoiceController;
use App\Http\Controllers\UserAuthorizationPinController;
use App\Http\Controllers\PaymentInvoiceController;
use App\Http\Controllers\CodingReportController;
use App\Http\Controllers\PatientMedicationController;
use App\Http\Controllers\AutoTextController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login',             [UserAuthenticationController::class, 'login']);
Route::post('/two-factor/resend', [UserAuthenticationController::class, 'resend_otac_code']);

////////////////////////////////////////////////////////////////////////////////////
// Appointment Pre Admission Routes (that don't require auth)
Route::prefix('appointments/pre-admissions')->group(function () {
    Route::get('/show/{token}',         [AppointmentPreAdmissionController::class, 'show',]);
    Route::post('/validate/{token}',    [AppointmentPreAdmissionController::class, 'validatePreAdmission']);
    Route::post('/store/{token}',       [AppointmentPreAdmissionController::class, 'store']);
});

Route::middleware(['auth'])->group(function () {

    ////////////////////////////////////////////////////////////////////////////////////
    // Account & Auth Routes
    Route::post('/verify_token',    [UserAuthenticationController::class, 'verify_token']);
    Route::post('/logout',          [UserAuthenticationController::class, 'logout']);
    Route::post('/refresh',         [UserAuthenticationController::class, 'refresh']);


    Route::post('/update-profile',  [UserProfileController::class, 'update']);
    Route::get('/profile',          [UserProfileController::class, 'show']);

    Route::post('/change-password',  [UserPasswordController::class, 'update']);

    ////////////////////////////////////////////////////////////////////////////////////
    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/',                        [UserProfileController::class, 'show']);
        Route::post('/signature',              [UserProfileSignatureController::class, 'update']);
    });




    ////////////////////////////////////////////////////////////////////////////////////
    // Appointment Routes
    Route::prefix('appointments')->group(function () {
        Route::put('/wait-listed/{appointment}',              [AppointmentController::class, 'waitListed']);

        Route::get('/byDate',                                 [AppointmentConfirmationStatusController::class, 'index']);
        Route::get('/confirmation-status',                    [AppointmentConfirmationStatusController::class, 'index']);
        Route::put('/confirmation-status/{appointment}',      [AppointmentConfirmationStatusController::class, 'update']);

        Route::post('/check-in/{appointment}',                 [AppointmentAttendanceStatusController::class, 'checkIn']);
        Route::post('/check-out/{appointment}',                [AppointmentAttendanceStatusController::class, 'checkOut']);

        Route::put('/procedure-approval-status/{appointment}',  [AppointmentProcedureApprovalController::class, 'update']);
        Route::post('/referral/{appointment}',                [AppointmentReferralController::class, 'update']);
        Route::get('/specialists',                            [AppointmentSpecialistController::class, 'index']);
        Route::put('/collecting-person/{appointment}',        [AppointmentCollectingPersonController::class, 'update']);

        Route::post('/{appointment}/detail',                  [AppointmentDetailController::class, 'update']);

        Route::post('/{appointment}/invoice',                 [AppointmentInvoiceController::class, 'show']);
        Route::post('/{appointment}/invoice/send',            [AppointmentInvoiceController::class, 'send']);
        Route::post('/draft',                                 [AppointmentDraftController::class, 'store']);
        Route::delete('/draft/{appointment}',                 [AppointmentDraftController::class, 'destroy']);
        Route::put('/bulk',                                   [AppointmentController::class, 'bulkUpdate']);
        Route::get('/{appointment}/resend-message', [AppointmentController::class, 'resendMessage']);
    });

    ////////////////////////////////////////////////////////////////////////////////////
    // Appointment Pre Admission Routes (that do require auth)
    Route::post('/appointments/pre-admissions/upload/{appointment}', [AppointmentPreAdmissionController::class, 'upload']);
    Route::post('update-pre-admission-consent', [PreAdmissionController::class, 'updateConsent']);
    Route::get('get-pre-admission-consent',     [PreAdmissionController::class, 'getConsent']);

    ////////////////////////////////////////////////////////////////////////////////////
    // Internal Mail Routes
    Route::prefix('mails')->group(function () {
        Route::apiResource('/',             MailController::class, ['except' => ['update']]);
        Route::post('/send',                [MailController::class, 'send']);
        Route::post('/send-draft',          [MailController::class, 'sendDraft']);
        Route::post('/update-draft',        [MailController::class, 'updateDraft']);
        Route::put('/bookmark/{id}',        [MailController::class, 'bookmark']);
        Route::put('/delete/{id}',          [MailController::class, 'delete']);
        Route::put('/restore/{id}',         [MailController::class, 'restore']);
    });

    ////////////////////////////////////////////////////////////////////////////////////
    // Patient Routes
    Route::prefix('patients')->group(function () {
        Route::get('/appointments/{patient}',                 [PatientController::class, 'appointments']);

        Route::put('/billing',                                [PatientBillingController::class, 'store']);
        Route::put('/billing/{patientBilling}',               [PatientBillingController::class, 'update']);
        Route::delete('/billing/{patientBilling}',            [PatientBillingController::class, 'delete']);

        Route::put('/also-known-as',                          [PatientAlsoKnownAsController::class, 'store']);
        Route::put('/also-known-as/{patientAlsoKnownAs}',     [PatientAlsoKnownAsController::class, 'update']);
        Route::put('/also-known-as/bulk/{patient}',           [PatientAlsoKnownAsController::class, 'bulk']);
        Route::delete('/also-known-as/{patientAlsoKnownAs}',  [PatientAlsoKnownAsController::class, 'delete']);

        Route::apiResource('/recalls',                        PatientRecallController::class);

        Route::prefix('documents')->group(function () {
            Route::post('/{patient}',                         [PatientDocumentController::class, 'store']);
            Route::post('/save/{patient}',                    [PatientDocumentController::class, 'save']);
            Route::post('/preview/{patient}',                 [PatientDocumentController::class, 'preview']);
            Route::post('/send/email',                        [PatientDocumentController::class, 'email']);
            Route::post('/send/health-link',                  [HealthLinkController::class, 'store']);
        });

        Route::post('/alerts',                                [PatientAlertController::class, 'store']);
        Route::put('/alerts/{patient_alert}',                 [PatientAlertController::class, 'update']);

        Route::apiResource('/allergies', PatientAllergyController::class)->parameters(['allergies' => 'patient_allergy']);
        Route::apiResource('/medications', PatientMedicationController::class)->parameters(['medications' => 'patient_medication']);
    });

    Route::prefix('hrm')->group(function () {
        Route::apiResource('/schedule-template', HrmScheduleTimeslotController::class);
        Route::apiResource('/weekly-schedule', HrmWeeklyScheduleController::class);
        Route::apiResource('/employee-leaves', HrmEmployeeLeaveController::class, ['except' => ['show']])->parameters(['employee-leaves' => 'HrmEmployeeLeave']);
        Route::get('/anesthetists', [AnesthetistController::class, 'index']);
        Route::get('/deallocate-appointments', [DeallocateAppointmentController::class, 'index']);
    });

    Route::prefix('communication')->group(function () {
        Route::get('/outgoing-log', [OutgoingMessageLogController::class, 'index']);
    });

    ////////////////////////////////////////////////////////////////////////////////////
    // Payment Routes
    Route::prefix('payments')->group(function () {
        Route::get('/',                           [PaymentController::class, 'index']);
        Route::post('/',                          [PaymentController::class, 'store']);
        Route::get('/{appointment}',              [PaymentController::class, 'show']);
        Route::post('/{appointmentPayment}',      [PaymentInvoiceController::class, 'show']);
        Route::post('/{appointmentPayment}/send', [PaymentInvoiceController::class, 'send']);
    });

    ////////////////////////////////////////////////////////////////////////////////////
    // Coding Routes
    Route::prefix('coding')->group(function () {
        Route::get('/',                           [CodingController::class, 'index']);
        Route::put('/{appointment}',              [AppointmentDetail::class, 'update']);
        Route::post('/check-appointments-complete', [AppointmentDetailController::class, 'checkAppointmentsComplete']);
    });
    Route::post('/generate-coding-report',        [CodingReportController::class, 'store']);

    ////////////////////////////////////////////////////////////////////////////////////
    // Organization Pin Routes
    Route::prefix('pin')->group(function () {
        Route::get('/show',     [UserAuthorizationPinController::class, 'show']);
        Route::post('/verify',  [UserAuthorizationPinController::class, 'verify']);
        Route::put('/set',      [UserAuthorizationPinController::class, 'set']);
    });

    ////////////////////////////////////////////////////////////////////////////////////
    // Doctor Address Book Routes
    Route::post('/doctor-address-book/find-by-provider-no', [DoctorAddressBookController::class, 'findByProviderNo']);

    ////////////////////////////////////////////////////////////////////////////////////
    // API Resources
    Route::apiResource('/admins',                        AdminController::class, ['except' => ['show']])->parameters(['admins' => 'user']);
    Route::apiResource('/auto-texts',                    AutoTextController::class, ['except' => ['show', 'edit']]);
    Route::apiResource('/anesthetic-questions',          AnestheticQuestionController::class, ['except' => ['show']]);
    Route::apiResource('/appointments',                  AppointmentController::class, ['except' => ['destroy']]);
    Route::apiResource('/appointment-time-requirements', AppointmentTimeRequirementController::class, ['except' => ['show']]);
    Route::apiResource('/appointment-types',             AppointmentTypeController::class, ['except' => ['show']]);
    Route::apiResource('/birth-codes',                   BirthCodeController::class, ['except' => ['show']]);
    Route::apiResource('/clinics',                       ClinicController::class, ['except' => ['show']]);
    Route::apiResource('/clinics/{clinic}/rooms',        RoomController::class, ['except' => ['show']]);
    Route::apiResource('/notification-templates',        NotificationTemplateController::class, ['except' => ['show']]);
    Route::apiResource('/organizations',                 OrganizationController::class);
    Route::apiResource('/patients',                      PatientController::class);
    Route::apiResource('/pre-admission-sections',        PreAdmissionController::class, ['except' => ['show']]);
    Route::apiResource('/doctor-address-book',           DoctorAddressBookController::class, ['except' => ['show']]);
    Route::apiResource('/document-templates',            DocumentTemplateController::class, ['except' => ['show']]);
    Route::apiResource('/schedule-fees',                 ScheduleFeeController::class, ['except' => ['index', 'show']]);
    Route::apiResource('/schedule-items',                ScheduleItemController::class, ['except' => ['show']]);
    Route::apiResource('/users',                         UserController::class);
    Route::apiResource('/bulletins',                     BulletinController::class);

    Route::apiResource('/document-header-footer-templates', DocumentHeaderFooterTemplateController::class, ['except' => ['show']]);


    ////////////////////////////////////////////////////////////////////////////////////
    // Other Routes
    Route::post('/organizations/settings',              [OrganizationSettingsController::class, 'update']);
    Route::get('/available-timeslots',                  [AppointmentSearchAvailableController::class, 'index']);
    Route::get('/appointment-month-availabilities',     [AppointmentSearchAvailableController::class, 'appointmentCount']);
    Route::post('/file',                                [FileController::class, 'show']);

    // Patient Document Routes
    Route::prefix('documents')->group(function () {
        Route::get('/', [PatientDocumentController::class, 'index']);
    });


    Route::get('/procedure-approvals',             [AppointmentProcedureApprovalController::class, 'index']);
    Route::put('appointment/procedure-approvals/{appointment}',    [AppointmentProcedureApprovalController::class, 'update']);

    Route::post('/notification-test',              [NotificationTestController::class, 'testSendNotification']);

    // User Routes
    Route::prefix('users')->group(function () {
        Route::post('/change-password',             [UserPasswordController::class, 'update']);
    });
});
