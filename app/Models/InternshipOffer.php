<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipOffer extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'company',
        'company_id',
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function companyProfile(): BelongsTo
    {
        return $this->company();
    }
}
