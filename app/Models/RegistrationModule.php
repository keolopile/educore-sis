<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistrationModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'module_id',
        'is_repeated',
    ];

    protected $casts = [
        'is_repeated' => 'boolean',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
