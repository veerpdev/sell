<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'document_logo',
        'max_clinics',
        'max_employees',
        'owner_id',
        'is_hospital',
        'appointment_length',
        'start_time',
        'end_time',
        'status',
        'code',
        'has_billing',
        'has_coding',
        'abn_acn',
        'ip_whitelist',
    ];

    protected $hidden = [
        'ip_whitelist',
    ];

    protected $appends = [
        'user_count',
        'clinic_count',
        'is_max_users',
        'is_max_clinics',
        'logo_url',
        'document_logo_url',
        'formatted_abn_acn',
    ];

    protected $casts = [
        'ip_whitelist' => 'json',
    ];

    /**
     * Returns the total user count
     */
    public function getUserCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Returns the total clinic count
     */
    public function getClinicCountAttribute()
    {
        return $this->clinics()->count();
    }

    /**
     * Returns true if max users reached
     */
    public function getIsMaxUsersAttribute()
    {
        if ($this->user_count >= $this->max_employees) {
            return true;
        }
        return false;
    }

    /**
     * Returns true of max clinics reached
     */
    public function getIsMaxClinicsAttribute()
    {
        if ($this->clinic_count >= $this->max_clinics) {
            return true;
        }
        return false;
    }

    /**
     * Returns temporary URL for logo file
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            $folder = getUserOrganizationFilePath();
            $path = "{$folder}/{$this->logo}";

            if (config('filesystems.default') !== 's3') {
                return url($path);
            }

            $expiry = config('temporary_url_expiry');
            return Storage::temporaryUrl($path, now()->addMinutes($expiry));
        }
    }

    /**
     * Returns temporary URL for logo file
     */
    public function getDocumentLogoUrlAttribute()
    {
        if ($this->document_logo) {
            $folder = getUserOrganizationFilePath();
            $path = "{$folder}/{$this->document_logo}";

            if (config('filesystems.default') !== 's3') {
                return url($path);
            }

            $expiry = config('temporary_url_expiry');
            return Storage::temporaryUrl($path, now()->addMinutes($expiry));
        }
    }

    public function getFormattedAbnAcnAttribute()
    {
        if (!$this->abn_acn) {
            return null;
        }

        $parts = [];

        if (strlen($this->abn_acn) === 9) {
            // If it's 9 digits, it's an ACN
            $parts[] = substr($this->abn_acn, 0, 3);
            $parts[] = substr($this->abn_acn, 3, 3);
            $parts[] = substr($this->abn_acn, 6, 3);
        }

        if (strlen($this->abn_acn) === 11) {
            // It's an ABN
            $parts[] = substr($this->abn_acn, 0, 2);
            $parts[] = substr($this->abn_acn, 2, 3);
            $parts[] = substr($this->abn_acn, 5, 3);
            $parts[] = substr($this->abn_acn, 8, 3);
        }

        return implode(' ', $parts);
    }

    /**
     * Get the clinics for organization.
     */
    public function clinics()
    {
        return $this->hasMany(Clinic::class);
    }

    /**
     * Get the bulletins for organization.
     */
    public function bulletins()
    {
        return $this->hasMany(Bulletin::class);
    }


    /**
     * Get the appointment types for organization.
     */
    public function appointment_Types()
    {
        return $this->hasMany(AppointmentType::class);
    }

    /**
     * Get the notification templates for organization.
     */
    public function notificationTemplates()
    {
        return $this->hasMany(NotificationTemplate::class);
    }



    /**
     * Get the patients for organization.
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class)->with('upcoming_appointments');
    }


    /**
     * Get the users for organization.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Return Owner user
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }


    /**
     * Return Schedule Timeslots
     */
    public function scheduleTimeslots()
    {
        return $this->hasMany(HrmScheduleTimeslot::class);
    }

    public function hrmWeeklySchedule()
    {
        return $this->hasMany(HrmWeeklySchedule::class);
    }
}
