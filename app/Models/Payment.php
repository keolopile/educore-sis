<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'enrolment_id',
        'amount',
        'currency',
        'method',
        'local_reference',
        'gateway_reference',
        'status',
        'gateway_payload',
    ];

    protected $casts = [
        'gateway_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class);
    }
}
