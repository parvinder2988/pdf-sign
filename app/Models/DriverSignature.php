<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverSignature extends Model
{
    protected $fillable = [
        'name',
        'email',
        'driver_number',
        'driver_run_number',
        'source_pdf',
        'signature_path',
        'signed_pdf_path',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];
}
