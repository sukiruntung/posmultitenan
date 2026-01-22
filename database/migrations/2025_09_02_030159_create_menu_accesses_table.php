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
        Schema::create('menu_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')
                ->constrained('outlets')
                ->onDelete('cascade');
            $table->foreignId('menu_id')
                ->constrained('menus')
                ->onDelete('cascade');
            $table->foreignId('user_group_id')
                ->constrained('user_groups')
                ->onDelete('cascade');
            $table->boolean('can_view')->default(false)->index();
            $table->boolean('can_create')->default(false)->index();
            $table->boolean('can_edit')->default(false)->index();
            $table->boolean('can_delete')->default(false)->index();
            $table->boolean('can_validate')->default(false)->index();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['menu_id', 'user_group_id'], 'unique_menu_access');
            $table->index(['deleted_at', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_accesses');
    }
};
