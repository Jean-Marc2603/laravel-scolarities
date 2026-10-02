<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvDocument extends Model
{
    protected $fillable = [
        'user_id',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'analysis_results',
        'last_analyzed_at',
    ];

    protected $casts = [
        'analysis_results' => 'array',
        'last_analyzed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
