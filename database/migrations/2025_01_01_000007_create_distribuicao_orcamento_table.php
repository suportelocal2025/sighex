<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribuicao_orcamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->integer('ano');
            $table->decimal('valor_distribuido', 15, 2)->default(0);
            $table->decimal('valor_gasto', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['unidade_id', 'ano']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribuicao_orcamento');
    }
};
