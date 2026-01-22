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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('hargajualgrosir', 15, 2)->default(0)->after('satuan_id');
            $table->decimal('hargajual1', 15, 2)->default(0)->after('hargajualgrosir');
            $table->decimal('hargajual2', 15, 2)->default(0)->after('hargajual1');
            $table->decimal('hargajual3', 15, 2)->default(0)->after('hargajual2');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
