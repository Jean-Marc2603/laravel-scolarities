<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipTask extends Model
{
    protected $fillable = [
        'student_internship_id',
        'name',
        'description',
        'progress',
        'planned_date',
    ];

    protected $casts = [
        'progress' => 'integer',
        'planned_date' => 'date',
    ];

    public function internship(): BelongsTo
    {
        return $this->belongsTo(StudentInternship::class, 'student_internship_id');
    }
}
