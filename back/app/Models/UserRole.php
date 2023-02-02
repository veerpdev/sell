<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'hrm_type',
    ];

    /**
     * Return bool
     */
    public function isEmployee()
    {
        return strtolower($this->hrm_type) == 'employee';
    }

    /**
     * Return employee roles
     */
    public static function employeeRoles()
    {
        return self::where('hrm_type', 'employee');
    }
}
