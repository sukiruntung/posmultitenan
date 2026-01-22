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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('supplier_email')->nullable()->unique()->after('supplier_alamat');
            $table->string('supplier_phone1')->nullable()->after('supplier_email');
            $table->string('supplier_phone2')->nullable()->after('supplier_phone1');
            $table->string('supplier_picname1')->nullable()->after('supplier_phone2');
            $table->string('supplier_picphone1')->nullable()->after('supplier_picname1');
            $table->string('supplier_picname2')->nullable()->after('supplier_picphone1');
            $table->string('supplier_picphone2')->nullable()->after('supplier_picname2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            //
            $table->dropColumn([
                'supplier_email',
                'supplier_phone1',
                'supplier_phone2',
                'supplier_picname1',
                'supplier_picphone1',
                'supplier_picname2',
                'supplier_picphone2'
            ]);
        });
    }
};
