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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_picname1')->nullable()->after('customer_phone2');
            $table->string('customer_picphone1')->nullable()->after('customer_picname1');
            $table->string('customer_picname2')->nullable()->after('customer_picphone1');
            $table->string('customer_picphone2')->nullable()->after('customer_picname2');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('customer_picname1');
            $table->dropColumn('customer_picphone1');
            $table->dropColumn('customer_picname2');
            $table->dropColumn('customer_picphone2');
            //
        });
    }
};
