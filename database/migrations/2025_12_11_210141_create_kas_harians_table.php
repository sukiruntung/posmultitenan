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
        Schema::create('kas_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')
                ->constrained('outlets')
                ->onDelete('cascade');
            $table->foreignId('kasir_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->dateTime('kas_harian_tanggalbuka')->index();
            $table->dateTime('kas_harian_tanggaltutup')
                ->nullable()
                ->index();
            $table->decimal('kas_harian_saldoawal', 10, 2)->default(0);
            $table->decimal('kas_harian_saldoakhir', 10, 2)->default(0);
            $table->decimal('kas_harian_saldoseharusnya', 10, 2)->default(0);
            $table->decimal('kas_harian_selisih', 10, 2)->default(0);
            $table->enum('kas_harian_status', ['buka', 'tutup'])
                ->default('buka')->index();
            $table->text('kas_harian_notes')->nullable();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_harians');
    }
};
