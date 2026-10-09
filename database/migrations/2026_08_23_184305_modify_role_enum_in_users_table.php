<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE users
                MODIFY role ENUM('admin','vendor','customer')
                DEFAULT 'customer'
            ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE users
                MODIFY role ENUM('vendor','customer')
                DEFAULT 'customer'
            ");
        });
    }
};
