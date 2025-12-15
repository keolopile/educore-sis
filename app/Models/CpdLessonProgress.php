<?php

// app/Models/CpdLessonProgress.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpdLessonProgress extends Model
{
    protected $fillable = [
        'user_id',
        'cpd_lesson_id',
        'seconds_watched',
        'last_position_seconds',
        'completed',
    ];
}
