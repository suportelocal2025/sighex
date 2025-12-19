<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escala_equipe_servidores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escala_id')->constrained('escalas')->cascadeOnDelete();
            $table->foreignId('equipe_id')->constrained('equipes')->cascadeOnDelete();
            $table->foreignId('servidor_id')->constrained('servidores')->cascadeOnDelete();
            $table->foreignId('modulo_id')->nullable()->constrained('modulos')->nullOnDelete();
            $table->boolean('lider')->default(false);
            $table->timestamps();
            
            $table->unique(['escala_id', 'servidor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escala_equipe_servidores');
    }
};
