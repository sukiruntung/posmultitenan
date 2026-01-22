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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')
                ->constrained('outlets')
                ->onDelete('cascade');
            $table->dateTime('sales_orders_tanggal')->index();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('sales_order_no')->unique();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['deleted_at', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
