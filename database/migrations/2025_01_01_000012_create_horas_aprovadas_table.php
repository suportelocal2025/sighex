<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horas_aprovadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servidor_id')->constrained('servidores')->cascadeOnDelete();
            $table->foreignId('escala_id')->constrained('escalas')->cascadeOnDelete();
            $table->integer('mes');
            $table->integer('ano');
            $table->integer('horas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horas_aprovadas');
    }
};
