<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'student_id',
        'programme_id',
        'registration_id',
        'study_year',
        'study_semester',
        'academic_year',
        'exam_session',
        'overall_status',
        'gpa',
        'remarks',
        'dtef_status',
        'last_dtef_response',
        'last_dtef_at',
        'created_by',
    ];

    protected $casts = [
        'last_dtef_at' => 'datetime',
    ];

    public function institution() { return $this->belongsTo(Institution::class); }
    public function student()     { return $this->belongsTo(Student::class); }
    public function programme()   { return $this->belongsTo(Programme::class); }
    public function registration(){ return $this->belongsTo(Registration::class); }

    public function items()
    {
        return $this->hasMany(ResultItem::class);
    }
}
