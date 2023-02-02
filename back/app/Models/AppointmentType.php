<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'type',
        'color',
        'name',
        'invoice_by',
        'arrival_time',
        'appointment_time',
        'anesthetist_required',
        'collecting_person_required',
        'status',
        'report_template',
        'default_items',
        'pre_procedure_instructions',
        'consent'
    ];

    protected $casts = [
        'default_items' => 'json',
    ];

    protected $appends = [
        'appointment_length_as_number',
        'default_items_quote',
    ];

        /**
     * Return the appointment_time attribute as a number
     */
    public function getAppointmentLengthAsNumberAttribute()
    {
        if ($this->appointment_time == 'SINGLE') {
            return 1;
        }
        
        if ($this->appointment_time == 'DOUBLE') {
            return 2;
        }
        
        return 3;
    }

    public function getDefaultItemsQuoteAttribute()
    {
        if (!$this->default_items) {
            return 0;
        }

        $amount = 0;
        foreach ($this->default_items as $item_id) {
            $item = ScheduleItem::find($item_id);
            
            $amount += $item->amount;
        }

        return $amount;
    }

    /**
     * Return Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
