<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'student_id',
        'programme_id',
        'commencement_date',
        'expected_completion_date',
        'level_of_entry',
        'programme_cost',
        'admission_status',
        'dtef_status',
        'last_dtef_response',
        'last_dtef_at',
        'created_by',
    ];

    protected $casts = [
        'commencement_date'       => 'date',
        'expected_completion_date'=> 'date',
        'last_dtef_at'            => 'datetime',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

