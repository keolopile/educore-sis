<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpdCourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'cpd_course_id',
        'title',
        'position',
    ];

    public function course()
    {
        return $this->belongsTo(CpdCourse::class, 'cpd_course_id');
    }

    public function lessons()
    {
        return $this->hasMany(CpdLesson::class, 'cpd_course_module_id')
            ->orderBy('position');
    }
}
