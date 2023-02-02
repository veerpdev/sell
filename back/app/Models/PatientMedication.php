<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'name',
        'instructions',
        'start_date',
        'end_date',
    ];

    /**
     * Return Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
