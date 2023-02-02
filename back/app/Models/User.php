<?php

namespace App\Models;

use Carbon\Carbon;
use Twilio\Rest\Client;
use Illuminate\Mail\Mailable;
use App\Models\HrmScheduleTimeslot;
use Illuminate\Support\Facades\Mail;
use App\Enum\UserRole as UserRoleEnum;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\SpecialistClinicRelation;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    const TIME_FORMAT = 'H:i:s';

    protected $fillable = [
        'username',
        'email',
        'title',
        'first_name',
        'last_name',
        'password',
        'role_id',
        'organization_id',
        'date_of_birth',
        'mobile_number',
        'address',
        'education_code',
        'sign_off',
        'photo',
        'authorization_pin',
        'outside_hours',
    ];

    protected $hidden = [
        'authorization_pin',
        'password',
    ];

    protected $appends = [
        'role_name',
        'full_name',
        'photo_url',
        'signature_url',
        'formatted_abn_acn',
        'unread_mails',
        'unreadDocuments'
    ];

    protected $casts = [
        'role_id' => UserRoleEnum::class,
    ];

    public function getRoleNameAttribute()
    {
        return $this->role->name;
    }


    public function getFullNameAttribute()
    {
        return $this->title . " " . $this->first_name . " " . $this->last_name;
    }


    public function getUnreadDocumentsAttribute()
    {
        if ($this->role_id === UserRoleEnum::SPECIALIST) {
            return PatientDocument::where('organization_id', $this->organization_id)
                ->where('specialist_id', $this->id)
                ->where('is_read', '0')
                ->count();
        }
        return 0;
    }

    /**
     * Returns temporary URL for photo file
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            $folder = getUserOrganizationFilePath();
            $path = "{$folder}/{$this->photo}";

            if (config('filesystems.default') !== 's3') {
                return url($path);
            }

            $expiry = config('temporary_url_expiry');
            return Storage::temporaryUrl($path, now()->addMinutes($expiry));
        }
    }

    /**
     * Returns temporary URL for photo file
     */
    public function getSignatureUrlAttribute()
    {
        if ($this->signature) {
            $folder = getUserOrganizationFilePath();
            $path = "{$folder}/{$this->signature}";

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

    public function getUnreadMailsAttribute()
    {
        return Mailbox::select('mails.id as id', 'mails.from_user_id as from_user_id')
            ->leftJoin('mails', 'mailboxes.mail_id', '=', 'mails.id')
            ->where('mailboxes.user_id', '=', $this->id)
            ->where('mails.from_user_id', '<>', $this->id)
            ->where('mailboxes.status', '=', 'inbox')
            ->where('mailboxes.status', '=', 'inbox')
            ->where('mailboxes.is_read', '=', 0)
            ->get();
    }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Return User Role
     */
    public function role()
    {
        return $this->belongsTo(UserRole::class);
    }

    /**
     * Return Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Return Provider Number
     */
    public function specialistClinicRelations()
    {
        return $this->hasMany(SpecialistClinicRelation::class, 'specialist_id');
    }

    /**
     * Return Schedule Timeslots
     */
    public function scheduleTimeslots()
    {
        return $this->hasMany(HrmScheduleTimeslot::class);
    }

    public function anethetistScheduleTimeslots()
    {
        return $this->hasMany(HrmScheduleTimeslot::class, 'anesthetist_id', 'id');
    }

    /*
    * Get HRMUserBaseSChedule at time
    *
    * @param string $time
    * @param string $day
    * @return HRMUserBaseSchedule
    */
    public function hrmUserBaseScheduleAtTimeDay($time, $day)
    {
        return $this->scheduleTimeslots
            ->where('week_day', $day)
            ->where('start_time', '<=', date(self::TIME_FORMAT, $time))
            ->where('end_time', '>', date(self::TIME_FORMAT, $time))->first();
    }

    /**
     * Return Organization
     */
    public function appointments($fieldKey = 'specialist_id')
    {
        return $this->hasMany(Appointment::class, $fieldKey, 'id');
    }

    public function hrmWeeklySchedule()
    {
        return $this->hasMany(HrmWeeklySchedule::class);
    }

    public function AnethetistHrmWeeklySchedule()
    {
        return $this->hasMany(HrmWeeklySchedule::class, 'anesthetist_id', 'id');
    }
    /**
     * Return Organization
     */
    public function isAdmin()
    {
        return $this->role->slug == 'admin';
    }

    public function employeeLeaves()
    {
        return $this->hasMany(HrmEmployeeLeave::class);
    }
    /**
     * translate template
     */
    public function translate($template, $data)
    {
        $words = [
            '[first_name]' => $this->first_name,
            '[app_link]'  => env('APP_URL'),
            '[username]'  => $this->username,
            '[password]'  => $data['password'],
        ];

        $translated = $template;

        foreach ($words as $key => $word) {
            $translated = str_replace($key, $word, $translated);
        }

        return $translated;
    }

    /*
    * Check if a user has a particular role
    *
    * @param string $role
    * @return boolean
    */
    public function hasRole($role)
    {
        if ($this->role->slug == $role) {
            return true;
        }

        return false;
    }

    /*
    * Check if a user has any one of a set of roles
    *
    * @param array $roles
    * @return boolean
    */
    public function hasAnyRole($roles)
    {
        if (in_array($this->role->slug, $roles)) {
            return true;
        }

        return false;
    }

    /*
    * Check if a user is available to work at a certain time on a certain day
    *
    * @param string $time
    * @param string $day
    * @return boolean
    */
    public function canWorkAt($time, $day, $date)
    {
        if (
            count($this->scheduleTimeslots
                ->where('week_day', $day)
                ->where('start_time', '<=', date(self::TIME_FORMAT, $time))
                ->where('end_time', '>', date(self::TIME_FORMAT, $time))) > 0
            && count($this->employeeLeaves
                ->where('status', "Approved")
                ->where('start_date', '<=', date('Y-m-d', $date))
                ->where('end_date', '>=', date('Y-m-d', $date))) === 0
        ) {
            return true;
        }
        return false;
    }

    /*
    * Get user scheduleTimeslots at a certain time on a certain date
    *
    * @param string $time
    * @param string $day
    * @return HrmWeeklySchedule
    */

    public function hrmUserBaseSchedulesTimeDay($time, $date)
    {
        $schedule = $this->userWorkSchedule($date);
        return $schedule
            ->where('start_time', '<=', date(self::TIME_FORMAT, $time))
            ->where('end_time', '>', date(self::TIME_FORMAT, $time))->first();
    }

    /*
    * Check if a specialist is has an appointment at a certain time on a certain day
    *
    * @param string $time
    * @param string $day
    * @return boolean
    */
    public function hasAppointmentAtTime($time, $date)
    {
        if (count($this->appointments
            ->where('date', date('Y-m-d', $date))
            ->where('start_time', '<=', date(self::TIME_FORMAT, $time))
            ->where('end_time', '>', date(self::TIME_FORMAT, $time))) > 0) {
            return true;
        }

        return false;
    }

    /*
    * Check if a specialist can undergo a certain appointment at a certain time on a certain day
    *
    * @param string $time
    * @param string $day
    * @return boolean
    */
    public function canAppointmentTypeAt($time, $day, $appointmentType)
    {
        $schedule = $this->scheduleTimeslots
            ->where('week_day', $day)
            ->where('start_time', '<=', date(self::TIME_FORMAT, $time))
            ->where('end_time', '>', date(self::TIME_FORMAT, $time))
            ->first();

        if ($schedule->restriction == "NONE" || $schedule->restriction == $appointmentType->type) {
            return true;
        }
        return false;
    }

    public function update(array $attributes = [], array $options = [])
    {
        parent::update($attributes, $options);
        $arrID = [];
        if (array_key_exists('schedule_timeslots', $attributes)) {
            foreach ($attributes['schedule_timeslots'] as $schedule) {
                $schedule = (object) $schedule;
                $scheduleObj = null;
                if (isset($schedule->id) && $schedule->id != null) {
                    $scheduleObj = HrmScheduleTimeslot::find($schedule->id);
                }
                if ($scheduleObj == null) {
                    $scheduleObj = new HrmScheduleTimeslot();
                    $scheduleObj->user_id = $this->id;
                }
                $scheduleObj->clinic_id = $schedule->clinic_id;
                $scheduleObj->week_day = $schedule->week_day;
                $scheduleObj->start_time = $schedule->start_time;
                $scheduleObj->end_time = $schedule->end_time;
                $scheduleObj->anesthetist_id = $schedule->anesthetist_id;
                $scheduleObj->save();
                $arrID[] = $scheduleObj->id;
            }
        }
        HrmScheduleTimeslot::where('user_id', $this->id)
            ->whereNotIn('id', $arrID)
            ->delete();

        $arrID = [];
        if (
            array_key_exists('specialist_clinic_relations', $attributes)
            && $attributes['specialist_clinic_relations']
        ) {
            foreach ($attributes['specialist_clinic_relations'] as $provider) {
                $provider = (object) $provider;
                $providerObj = null;
                if (isset($provider->id) && $provider->id != null) {
                    $providerObj = SpecialistClinicRelation::find($provider->id);
                }
                if ($providerObj == null) {
                    $providerObj = new SpecialistClinicRelation();
                    $providerObj->specialist_id = $this->id;
                }
                $providerObj->clinic_id = $provider->clinic_id;
                $providerObj->provider_number = $provider->provider_number;
                $providerObj->save();
                $arrID[] = $providerObj->id;
            }
        }
        SpecialistClinicRelation::where('specialist_id', $this->id)
            ->whereNotIn('id', $arrID)
            ->delete();

        return User::where('id', $this->id)
            ->with('scheduleTimeslots')
            ->with('specialistClinicRelations')
            ->first();
    }

    public function sendEmail(Mailable $mailable)
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

    // generate work Schedule based on published schedule and template
    // TODO - filter by employee approved leaves
    public function generateWorkSchedule($date = null)
    {
        if (!$date) {
            return $this->scheduleTimeslots()->where('is_template', true)
                ->with('anesthetist')->get();
        }
        $date = Carbon::parse($date);
        $publishedSlots = HrmFilledWeek::where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())->exists();

        if ($publishedSlots) {
            return $this->hrmWeeklySchedule
                ->where('date', $date->toDateString())
                ->where('status', "PUBLISHED")
                ->with('anesthetist')
                ->get();
        } else {
            $scheduleTimeslots = $this->scheduleTimeslots()
                ->where('week_day', strtoupper($date->format('D')))
                ->with('anesthetist')->get();

            foreach ($scheduleTimeslots as $slot) {
                $slot->date = $date->toDateString();
            }

            return $scheduleTimeslots;
        }
    }

    public function userWorkSchedule($date = null)
    {
        if (!$date) {
            return $this->scheduleTimeslots()->where('is_template', true)
                ->with('anesthetist');
        }

        $date = Carbon::parse($date);
        if (HrmFilledWeek::where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())->exists()
        ) {
            return $this->hrmWeeklySchedule()
                ->where('date', $date->toDateString())
                ->where('status', "PUBLISHED")
                ->with('clinic')
                ->with('anesthetist');
        } else {
            return $this->scheduleTimeslots()
                ->where('week_day', strtoupper($date->format('D')))
                ->with('anesthetist');
        }
    }
}
