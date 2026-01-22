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
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->string('product_stock_sn')->nullable()->after('product_id')->index();
            $table->date('product_stock_ed')->nullable()->after('product_stock_sn')->index();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropColumn([
                'product_stock_sn',
                'product_stock_ed'
            ]);
        });
    }
};
