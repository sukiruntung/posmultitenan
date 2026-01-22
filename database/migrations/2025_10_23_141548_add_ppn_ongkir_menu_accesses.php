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
            $table->boolean('can_ppn')->default(false)->after('can_print2')->index();
            $table->tinyInteger('ppn_rate')->default(0)->after('can_ppn');
            $table->boolean('can_ongkir')->default(false)->after('ppn_rate')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_accesses', function (Blueprint $table) {
            $table->dropColumn(['can_ppn', 'ppn_rate', 'can_ongkir']);
        });
    }
};
