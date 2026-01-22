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
        Schema::table('menu_accesses', function (Blueprint $table) {
            $table->boolean('can_unvalidate')->default(false)->after('can_validate')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_accesses', function (Blueprint $table) {
            $table->dropColumn('can_unvalidate');
        });
    }
};
