<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'short_code',
        'logo_path',
        'phone',
        'email',
        'website',
        'address_line1',
        'address_line2',
        'city',
        'country',
        'dtef_enabled',
        'dtef_environment',
        'dtef_username',
        'dtef_password',
    ];

    public function students()
{
    return $this->hasMany(Student::class);
}

}
