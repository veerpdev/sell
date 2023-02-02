<?php

namespace App\Providers;

use App\Models\Mail;
use App\Models\Room;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\BirthCode;
use App\Models\Appointment;
use App\Models\ScheduleFee;
use App\Models\Organization;
use App\Policies\MailPolicy;
use App\Policies\RoomPolicy;
use App\Policies\UserPolicy;
use App\Models\PatientLetter;
use App\Models\PatientRecall;
use App\Models\PatientReport;
use App\Models\DocumentSection;
use App\Models\PatientBilling;
use App\Models\DocumentTemplate;
use App\Policies\ClinicPolicy;
use App\Models\AppointmentType;
use App\Models\PatientDocument;
use App\Models\DoctorAddressBook;
use App\Policies\PatientPolicy;
use App\Policies\BirthCodePolicy;
use App\Models\AnestheticQuestion;
use App\Models\AppointmentPayment;
use App\Models\AppointmentReferral;
use App\Models\PatientClinicalNote;
use App\Models\PreAdmissionConsent;
use App\Models\PreAdmissionSection;
use App\Policies\AppointmentPolicy;
use App\Policies\ScheduleFeePolicy;
use App\Models\NotificationTemplate;
use App\Models\PatientRecallSentLog;
use App\Models\PreAdmissionQuestion;
use App\Policies\OrganizationPolicy;
use App\Policies\PatientLetterPolicy;
use App\Policies\PatientRecallPolicy;
use App\Policies\PatientReportPolicy;
use App\Policies\DocumentSectionPolicy;
use App\Models\PatientSpecialistAudio;
use App\Policies\PatientBillingPolicy;
use App\Policies\DocumentTemplatePolicy;
use App\Models\AppointmentPreAdmission;
use App\Policies\AppointmentTypePolicy;
use App\Policies\PatientDocumentPolicy;
use App\Policies\DoctorAddressBookPolicy;
use App\Models\AppointmentTimeRequirement;
use App\Models\PatientAlert;
use App\Models\ScheduleItem;
use App\Policies\AnestheticQuestionPolicy;
use App\Policies\AppointmentPaymentPolicy;
use App\Policies\AppointmentReferralPolicy;
use App\Models\HrmWeeklySchedule;
use App\Policies\HRMWorkSchedulePolicy;
use App\Policies\PatientClinicalNotePolicy;
use App\Policies\PreAdmissionConsentPolicy;
use App\Policies\PreAdmissionSectionPolicy;
use App\Policies\NotificationTemplatePolicy;
use App\Policies\PatientRecallSentLogPolicy;
use App\Policies\PreAdmissionQuestionPolicy;
use App\Policies\PatientSpecialistAudioPolicy;
use App\Policies\AppointmentPreAdmissionPolicy;
use App\Policies\AppointmentTimeRequirementPolicy;
use App\Policies\PatientAlertPolicy;
use App\Policies\DocumentHeaderFooterTemplatePolicy;
use App\Policies\ScheduleItemPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AnestheticQuestion::class           => AnestheticQuestionPolicy::class,
        Appointment::class                  => AppointmentPolicy::class,
        AppointmentPayment::class           => AppointmentPaymentPolicy::class,
        AppointmentPreAdmission::class      => AppointmentPreAdmissionPolicy::class,
        AppointmentReferral::class          => AppointmentReferralPolicy::class,
        AppointmentTimeRequirement::class   => AppointmentTimeRequirementPolicy::class,
        AppointmentType::class              => AppointmentTypePolicy::class,
        BirthCode::class                    => BirthCodePolicy::class,
        Clinic::class                       => ClinicPolicy::class,
        DocumentHeaderFooterTemplate::class => DocumentHeaderFooterTemplatePolicy::class,
        Mail::class                         => MailPolicy::class,
        NotificationTemplate::class         => NotificationTemplatePolicy::class,
        Organization::class                 => OrganizationPolicy::class,
        Patient::class                      => PatientPolicy::class,
        PatientAlert::class                 => PatientAlertPolicy::class,
        PatientBilling::class               => PatientBillingPolicy::class,
        PatientClinicalNote::class          => PatientClinicalNotePolicy::class,
        PatientDocument::class              => PatientDocumentPolicy::class,
        PatientLetter::class                => PatientLetterPolicy::class,
        PatientRecall::class                => PatientRecallPolicy::class,
        PatientRecallSentLog::class         => PatientRecallSentLogPolicy::class,
        PatientReport::class                => PatientReportPolicy::class,
        PatientSpecialistAudio::class       => PatientSpecialistAudioPolicy::class,
        PreAdmissionConsent::class          => PreAdmissionConsentPolicy::class,
        PreAdmissionQuestion::class         => PreAdmissionQuestionPolicy::class,
        PreAdmissionSection::class          => PreAdmissionSectionPolicy::class,
        DoctorAddressBook::class            => DoctorAddressBookPolicy::class,
        DocumentSection::class              => DocumentSectionPolicy::class,
        DocumentTemplate::class             => DocumentTemplatePolicy::class,
        Room::class                         => RoomPolicy::class,
        ScheduleFee::class                  => ScheduleFeePolicy::class,
        ScheduleItem::class                 => ScheduleItemPolicy::class,
        User::class                         => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
