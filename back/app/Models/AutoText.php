<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoText extends Model
{
    use HasFactory;

    protected $fillable = ['organization_id','user_id','text','suggested_codes','report_types'];
}
