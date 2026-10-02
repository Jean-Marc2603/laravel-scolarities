<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipOffer extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'company',
        'domain',
        'location',
        'duration',
        'description',
        'skills',
        'deadline',
        'details',
        'is_active',
    ];

    protected $casts = [
        'skills' => 'array',
        'deadline' => 'date',
        'is_active' => 'boolean',
    ];
}
