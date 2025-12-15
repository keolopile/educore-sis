<?php
// app/Models/CpdDomain.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CpdDomain extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(CpdCourse::class, 'cpd_domain_id');
    }
}
