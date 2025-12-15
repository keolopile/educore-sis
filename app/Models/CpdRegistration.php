<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpdRegistration extends Model
{
    protected $fillable = [
        'cpd_session_id',
        'user_id',
        'full_name',
        'email',
        'organisation',
        'role',
        'special_requirements',
        'status',
    ];

    public function session()
    {
        return $this->belongsTo(CpdSession::class, 'cpd_session_id');
    }
}
