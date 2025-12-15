<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResultItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'result_id',
        'module_id',
        'mark',
        'grade',
        'remark',
        'is_supplementary',
    ];

    protected $casts = [
        'is_supplementary' => 'boolean',
    ];

    public function result() { return $this->belongsTo(Result::class); }
    public function module() { return $this->belongsTo(Module::class); }
}
