<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InternshipApplication extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'offer_id',
        'applied_at',
        'compatibility_score',
        'status',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'compatibility_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studentInternship(): HasOne
    {
        return $this->hasOne(StudentInternship::class, 'internship_application_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACCEPTED => 'Acceptée',
            self::STATUS_REJECTED => 'Refusée',
            default => 'En attente',
        };
    }
}
