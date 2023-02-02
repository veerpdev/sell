<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppointmentPreAdmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'token',
        'note',
        'status',
    ];

    protected $appends = [
        'document_url',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getAppointmentPreAdmissionData()
    {

        $appointment = $this->appointment;
        $organization = $appointment->organization;
        $data = [
            'organization_logo'     =>  $organization->logo_url,
            'clinic_email'          =>  $appointment->clinic->email,
            'clinic_phone'          =>  $appointment->clinic->phone_number,
            'status'                =>  $this->status,
            'note'                  =>  $this->note,
            'pre_admission_file'    =>  $this->pre_admission_file
        ];

        if ($this->status == 'BOOKED') {
            return $data;
        }

        $patient = $appointment->patient;
        $clinic = $appointment->clinic;
        $appointmentType = $appointment->appointment_type;

        $data['patient'] = [
            'title'            => $patient['title'],
            'first_name'       => $patient['first_name'],
            'last_name'        => $patient['last_name'],
            'date_of_birth'    => $patient['date_of_birth'],
            'contact_number'   => $patient['contact_number'],
            'email'            => $patient['email'],
            'address'          => $patient['address'],
            'occupation'       => $patient['occupation'],
            'kin_name'         => $patient['kin_name'],
            'kin_phone_number' => $patient['kin_phone_number'],
            'kin_relationship' => $patient['kin_relationship'],
        ];

        $data['appointment'] = [
            'specialist_name'       => $appointment->specialist->full_name,
            'date'                  => $appointment['date'],
            'start_time'            => $appointment['start_time'],
        ];

        $data['clinic'] = $clinic->name;

        $data['appointment_type'] = [
            'id' => $appointmentType->id,
            'name' => $appointmentType->name,
            'type' => $appointmentType->type,
        ];

        $data['pre_admission_sections'] = PreAdmissionSection::where('organization_id', $organization->id)
            ->with('questions')
            ->get();

        $defaultConsent = PreAdmissionConsent::where('organization_id', $organization->id)->first();

        $data['pre_admission_consent'] = $appointmentType->consent ?? $defaultConsent->text;

        return $data;
    }

    public function getDocumentUrlAttribute()
    {
        if ($this->pre_admission_file) {
            $folder = getUserOrganizationFilePath();
            $path = "{$folder}/{$this->pre_admission_file}";

            if (config('filesystems.default') !== 's3') {
                return url($path);
            }

            $expiry = config('temporary_url_expiry');
            return Storage::temporaryUrl($path, now()->addMinutes($expiry));
        }
    }
}
