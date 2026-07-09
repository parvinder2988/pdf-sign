<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver_run_number');
            $table->string('source_pdf')->default('ilovepdf-merged.pdf');
            $table->string('signature_path');
            $table->string('signed_pdf_path');
            $table->timestamp('signed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_signatures');
    }
};
