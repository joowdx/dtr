<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upload extends Model
{
    use HasUlids;

    protected $casts = [
        'data' => 'object',
        'time' => 'datetime',
        'earliest' => 'datetime',
        'latest' => 'datetime',
    ];

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(Scanner::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
