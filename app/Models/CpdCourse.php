<?php
// app/Models/CpdCourse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CpdCourseModule;

class CpdCourse extends Model
{
    protected $fillable = [
        'cpd_domain_id',
        'code',
        'title',
        'short_description',
        'full_description',
        'duration_days',
        'cpd_points',
        'default_price',
        'currency',
        'is_active',
        'moodle_course_id',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(CpdDomain::class, 'cpd_domain_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CpdSession::class, 'cpd_course_id');
    }

public function modules()
    {
        return $this->hasMany(CpdCourseModule::class, 'cpd_course_id')
            ->orderBy('position');
    }

    public function lessons()
    {
        return $this->hasManyThrough(
            CpdLesson::class,
            CpdCourseModule::class,
            'cpd_course_id',        // FK on modules
            'cpd_course_module_id', // FK on lessons
            'id',                   // local key on courses
            'id'                    // local key on modules
        )->orderBy('position');
    }


}
