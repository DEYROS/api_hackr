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
        Schema::table('user_functionalities', function (Blueprint $table) {
            $table->foreign(['functionality_id'])->references(['id'])->on('functionalities')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_functionalities', function (Blueprint $table) {
            $table->dropForeign('user_functionalities_functionality_id_foreign');
            $table->dropForeign('user_functionalities_user_id_foreign');
        });
    }
};
