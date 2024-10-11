<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // User qui fait l'action
            $table->foreignId('target_id')->nullable()->constrained('users')->onDelete('set null'); // User cible (ex. ajout/suppression de fonctionnalité)
            $table->string('action'); // Action effectuée
            $table->string('functionality')->nullable(); // Fonctionnalité concernée
            $table->timestamp('created_at')->useCurrent(); // Seulement created_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
