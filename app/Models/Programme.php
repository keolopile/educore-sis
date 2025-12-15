<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Programme extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'code',
        'name',
        'level',
        'duration_years',
        'is_active',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function admissions()
{
    return $this->hasMany(Admission::class);
}

public function modules()
{
    return $this->hasMany(Module::class);
}

}
