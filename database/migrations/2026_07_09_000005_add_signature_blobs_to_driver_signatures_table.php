<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE driver_signatures ADD signature_blob MEDIUMBLOB NULL AFTER signed_pdf_path');
        DB::statement('ALTER TABLE driver_signatures ADD signed_pdf_blob MEDIUMBLOB NULL AFTER signature_blob');
    }

    public function down(): void
    {
        Schema::table('driver_signatures', function (Blueprint $table) {
            $table->dropColumn(['signature_blob', 'signed_pdf_blob']);
        });
    }
};
