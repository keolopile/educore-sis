<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\RegistrationModule;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'student_id',
        'programme_id',
        'study_year',
        'study_semester',
        'accommodation',
        'registration_date',
        'registration_status',
        'dtef_status',
        'last_dtef_response',
        'last_dtef_at',
        'created_by',
    ];

    protected $casts = [
        'accommodation'      => 'boolean',
        'registration_date'  => 'date',
        'last_dtef_at'       => 'datetime',
    ];

    public function institution() { return $this->belongsTo(Institution::class); }
    public function student()     { return $this->belongsTo(Student::class); }
    public function programme()   { return $this->belongsTo(Programme::class); }

   public function modules()
{
    return $this->hasMany(RegistrationModule::class);
}

}
