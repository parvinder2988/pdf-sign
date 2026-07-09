<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DELETE d1 FROM driver_signatures d1 INNER JOIN driver_signatures d2 ON d1.driver_number = d2.driver_number AND d1.id < d2.id WHERE d1.driver_number <> ""');
        DB::statement('DELETE d1 FROM driver_signatures d1 INNER JOIN driver_signatures d2 ON d1.driver_run_number = d2.driver_run_number AND d1.id < d2.id WHERE d1.driver_run_number <> ""');

        Schema::table('driver_signatures', function ($table) {
            $table->unique('driver_number', 'driver_signatures_driver_number_unique');
            $table->unique('driver_run_number', 'driver_signatures_driver_run_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('driver_signatures', function ($table) {
            $table->dropUnique('driver_signatures_driver_number_unique');
            $table->dropUnique('driver_signatures_driver_run_number_unique');
        });
    }
};
