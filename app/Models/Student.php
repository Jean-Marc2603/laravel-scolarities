<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function parents()
    {
        return $this->belongsToMany(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attributions(): HasMany
    {
        return $this->hasMany(Attribution::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
