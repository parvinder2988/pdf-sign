<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureOtp extends Model
{
    protected $fillable = [
        'email',
        'code',
        'code_hash',
        'ip_address',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
}
