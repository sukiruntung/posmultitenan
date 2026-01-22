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
        Schema::table('penerimaan_barangs', function (Blueprint $table) {
            $table->integer('penerimaan_barang_validatedby')->defalut(0)->after('notes');
            $table->dateTime('penerimaan_barang_validatedat')->nullable()->after('penerimaan_barang_validatedby')->index();
            $table->enum('penerimaan_barang_status', ['pending', 'validated', 'belum lunas', 'lunas'])->default('pending')->after('penerimaan_barang_validatedat')->index(); // pending, validated, cancelled
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penerimaan_barangs', function (Blueprint $table) {

            $table->dropColumn(['penerimaan_barang_validatedby', 'penerimaan_barang_validatedat', 'penerimaan_barang_status']);
        });
    }
};
