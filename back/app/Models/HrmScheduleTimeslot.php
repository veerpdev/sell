<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmScheduleTimeslot extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'week_day',
        'category',
        'restriction',
        'user_id',
        'start_time',
        'end_time',
        'is_template',
        'organization_id',
        'anesthetist_id',
    ];

    protected $appends = [
        'clinic_name'
    ];

    /**
     * Return the clinic name of the clinic
     */
    public function getClinicNameAttribute()
    {
        return $this->clinic->name;
    }

    /**
     * Return user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anesthetist()
    {
        return $this->belongsTo(User::class, 'anesthetist_id');
    }

    /**
     * Return clinic
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Return organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function hrmWeeklySchedule()
    {
        return $this->hasMany(HrmWeeklySchedule::class);
    }
}
