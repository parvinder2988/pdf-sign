<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DELETE d1 FROM driver_signatures d1 INNER JOIN driver_signatures d2 ON d1.email = d2.email AND d1.id < d2.id WHERE d1.email <> ""');

        Schema::table('driver_signatures', function ($table) {
            $table->unique('email', 'driver_signatures_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('driver_signatures', function ($table) {
            $table->dropUnique('driver_signatures_email_unique');
        });
    }
};
