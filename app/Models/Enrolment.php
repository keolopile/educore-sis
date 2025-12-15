<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrolment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cpd_session_id',
        'enrolment_status',   // pending | active | completed | cancelled
        'payment_status',     // pending | paid | failed | refunded
        'organisation_name',
        'position_title',
        'moodle_user_id',

        // 🔹 New CCPD registration fields
        'id_number',
        'gender',
        'phone',
        'address',
        'employer',
        'designation',
        'department',
        'work_phone',
        'work_email',
        'sponsorship_type',
    ];

    protected $casts = [
        'enrolment_status' => 'string',
        'payment_status'   => 'string',
    ];

    /* ─── Relationships ───────────────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CpdSession::class, 'cpd_session_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /* Convenience accessors so you can do $enrolment->course / ->domain */

    public function getCourseAttribute()
    {
        return $this->session?->course;
    }

    public function getDomainAttribute()
    {
        return $this->session?->course?->domain;
    }

    /* ─── Simple helpers / scopes ─────────────────────────────────── */

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('enrolment_status', ['active', 'completed']);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === 'paid';
    }
}



