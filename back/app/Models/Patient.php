<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'email',
        'contact_number',
        'gender',
        'date_of_birth',
        'address',
        'street',
        'suburb',
        'city',
        'state',
        'postcode',
        'country',
        'marital_status',
        'birth_place_code',
        'country_of_birth',
        'birth_state',
        'aborginality',
        'occupation',
        'height',
        'weight',
        'race',
        'bmi',
        'preferred_contact_method',
        'appointment_confirm_method',
        'send_recall_method',
        'kin_name',
        'kin_relationship',
        'kin_email',
        'kin_phone_number',
        'clinical_alerts',
    ];

    protected $appends = [
        'ur_id',
        'full_name',
        'also_known_as',
        'int_contact_number',
        'active_alerts',
        'gender_name',
        'allergies',
        'sex_format_hl7',
        'medicare_details',
        'billings',
    ];

    public function getURIdAttribute()
    {
        return str_pad($this->id, 8, "0", STR_PAD_LEFT);
    }

    public function getActiveAlertsAttribute()
    {
        return $this->alerts->where('is_dismissed', 0);
    }

    public function getSexFormatHl7Attribute()
    {
        switch ($this->gender) {
            case 1:
                return 'M';
            case 2:
                return 'F';
            case 3:
                return 'U';
            case 9:
                return 'O';
            default:
                return null;
        }
    }


    public function getIntContactNumberAttribute()
    {
        return '+61' . substr($this->contact_number, 1);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getBillingsAttribute()
    {
        return $this->billings()->get();
    }

    public function getAlsoKnownAsAttribute()
    {
        return $this->also_known_as()->get();
    }

    public function getGenderNameAttribute()
    {
        switch ($this->gender) {
            case 1:
                return 'Male';
            case 2:
                return 'Female';
            case 3:
                return 'Other';
            default:
                return 'Undisclosed';
        }
    }

    public function getMedicareDetailsAttribute()
    {
        return [
            'medicare_no' => '1234567895',
            'medicare_reference_no' => '1'
        ];
    }

    public function getAllergiesAttribute()
    {
        return $this->allergies()->get();
    }


    /**
     * Return Patient Billing
     */
    public function billings()
    {
        return $this->hasMany(PatientBilling::class, 'patient_id');
    }


    /**
     * Return Patient Also Known As
     */
    public function also_known_as()
    {
        return $this->hasMany(PatientAlsoKnownAs::class);
    }


    /**
     * Return Patient Appointment
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id')
            ->with('appointment_type')
            ->with('referral')
            ->where('organization_id', auth()->user()->organization_id)
            ->orderBy('date', 'DESC')
            ->orderBy('start_time', 'DESC');
    }

    /**
     * Return Patient allergies
     */
    public function allergies()
    {
        return $this->hasMany(PatientAllergy::class);
    }

    /**
     * Return Patient medications
     */
    public function medications()
    {
        return $this->hasMany(PatientMedication::class);
    }

    /**
     * Return Patient alerts
     */
    public function alerts()
    {
        return $this->hasMany(PatientAlert::class);
    }


    /**
     * Returns Patients Upcoming Appointment
     */
    public function upcoming_appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id')
            ->where('date', '>=', date('Y-m-d'))
            ->with('appointment_type');
    }

    /**
     * Return Patient Recalls
     */
    public function recalls()
    {
        return $this->hasMany(PatientRecall::class, 'patient_id');
    }

    /**
     * Return Patient documents
     */
    public function documents()
    {
        return $this->hasMany(PatientDocument::class);
    }

    /**
     * Get the patients for organization.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class);
    }

    public function isPartOfOrganization($organizationId)
    {
        return $this->organizations()->where('organization_id', $organizationId)->exists();
    }

    public function sendEmail($mailable)
    {
        if (!env('DISABLE_NOTIFICATIONS', null)) {
            Mail::to($this->email)->send($mailable);
        }
    }

    public function sendSMS($message)
    {

        if (!env('DISABLE_NOTIFICATIONS', null)) {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $client = new Client($sid, $token);
            $client->messages->create(
                $this->int_contact_number,
                [
                    'from' => env('TWILIO_PHONE_NUMBER'),
                    'body' => $message,
                ]
            );
        }
    }
}
