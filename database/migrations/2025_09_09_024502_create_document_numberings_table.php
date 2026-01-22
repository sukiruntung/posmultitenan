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
        Schema::create('document_numberings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')
                ->constrained('outlets')
                ->onDelete('cascade');
            $table->string('document_numbering_name')->index();
            $table->string('document_numbering_prefix')->nullable()->index();
            $table->string('document_numbering_format')->nullable();
            $table->integer('document_numbering_numberlength')->default(4);
            $table->integer('document_numbering_currentnumber')->default(0)->index();
            $table->enum('document_numbering_resettype', ['daily', 'yearly', 'monthly'])->default('yearly')->index();
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
        Schema::dropIfExists('document_numberings');
    }
};
