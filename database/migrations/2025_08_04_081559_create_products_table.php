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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')
                ->constrained('outlets')
                ->onDelete('cascade');
            $table->string('product_kode', 20)->index()->nullable();
            $table->string('product_catalog', 100)->index();
            $table->string('product_name')->index();
            $table->integer('product_minstock')->default(0)->index();
            $table->foreignId('satuan_id')
                ->constrained('satuans')
                ->onDelete('cascade');
            $table->foreignId('kelompok_product_id')
                ->constrained('kelompok_products')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['product_catalog', 'satuan_id'], 'unique_product');
            $table->index(['deleted_at', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
