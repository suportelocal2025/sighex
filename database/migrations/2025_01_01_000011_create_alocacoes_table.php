<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alocacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escala_id')->constrained('escalas')->cascadeOnDelete();
            $table->foreignId('servidor_id')->constrained('servidores')->cascadeOnDelete();
            $table->date('data');
            $table->integer('horas')->default(12);
            $table->timestamps();
            
            $table->unique(['escala_id', 'servidor_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alocacoes');
    }
};
