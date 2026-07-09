<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_signatures', function (Blueprint $table) {
            $table->string('email')->default('')->after('name');
            $table->string('driver_number')->default('')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('driver_signatures', function (Blueprint $table) {
            $table->dropColumn(['email', 'driver_number']);
        });
    }
};
