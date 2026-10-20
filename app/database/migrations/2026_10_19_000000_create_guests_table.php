<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // "Familia Pérez"
            $table->string('phone', 30)->nullable();      // para el botón de WhatsApp
            $table->unsignedTinyInteger('seats')->default(1);
            $table->string('code', 16)->unique();         // parte aleatoria de la URL
            $table->string('status', 16)->default('pending')->index();
            $table->unsignedTinyInteger('attending')->nullable();
            $table->json('attendee_names')->nullable();
            $table->string('diet', 200)->nullable();
            $table->text('message')->nullable();
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
