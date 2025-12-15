<?php

// app/Models/CpdSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CpdSession extends Model
{
    protected $fillable = [
        'cpd_course_id',
        'start_date',
        'end_date',
        'delivery_mode',
        'location',
        'price',
        'currency',
        'capacity',
        'seats_taken',
        'moodle_course_id',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(CpdCourse::class, 'cpd_course_id');
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class, 'cpd_session_id');
    }
}
