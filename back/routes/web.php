<?php

use App\Http\Controllers\AppointmentPreAdmissionController;
use App\Http\Controllers\AppointmentSearchAvailableController;
use App\Http\Controllers\HL7TestController;
use App\Http\Controllers\ReportVAEDController;
use App\Mail\GenericNotificationEmail;
use App\Models\Patient;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    $filename = str_replace('pre_admission', '', 'pre_admission_51_1663207992.pdf');
    $fileParts = explode('_', $filename);

    dd($fileParts);
});

Route::get('/test-sms', function () {
    $patient = Patient::first();
    $patient->contact_number = "04-8118-3422";
    $patient->save();
    $patient->sendSMS("This is a test text message from the aurora system");
    return 'Test SMS sent';
});

Route::get('/test-email', function () {
    $patient = Patient::first();
    $patient->email = "kaylee@ojc.com.au";
    $patient->save();
    $patient->sendEmail(new GenericNotificationEmail("Aurora Generic email test", "This is a test email from the aurora system"));
    return 'Test Email sent';
});


Route::get('/test-hl7parse', [HL7TestController::class, 'testHL7Parse']);
Route::get('/test-hl7create', [HL7TestController::class, 'createHealthLinkMessage']);
Route::get('/test-apt-count', [AppointmentSearchAvailableController::class, 'appointmentCount']);
Route::get('/VAED-report-test', [ReportVAEDController::class, 'generateVAEDforEpisode']);
