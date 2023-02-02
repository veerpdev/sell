<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'title',
        'free_text_default',
        'is_image',
    ];

    /**
     * Return Document Template
     */
    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }
}
