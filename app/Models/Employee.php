<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'biometrics_id',
        'name',
        'regular',
        'user_id'
    ];

    protected $casts = [
        'name' => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'biometrics_id', 'biometrics_id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->name->last}, {$this->name->first} {$this->name->extension}";
    }


}
