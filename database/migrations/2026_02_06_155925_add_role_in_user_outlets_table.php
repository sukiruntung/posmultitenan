<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_outlets', function (Blueprint $table) {
            $table->enum('role', ['admin', 'owner', 'staff'])->default('staff')->after('outlet_id');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_outlets', function (Blueprint $table) {
            //
            $table->dropColumn('role');
        });
    }
};
