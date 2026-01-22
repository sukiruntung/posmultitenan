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
            $table->string('product_slug')->unique()->after('product_name');
            $table->foreignId('merk_id')
                ->nullable()
                ->constrained('merks')
                ->onDelete('set null')
                ->after('satuan_id');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_slug',
                'merk_id'
            ]);
        });
    }
};
