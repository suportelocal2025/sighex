<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->integer('mes');
            $table->integer('ano');
            $table->enum('status', ['rascunho', 'pendente', 'aprovada', 'rejeitada', 'executada'])->default('rascunho');
            $table->text('motivo_rejeicao')->nullable();
            $table->decimal('valor_executado', 15, 2)->nullable();
            $table->foreignId('criado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('aprovado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('data_envio')->nullable();
            $table->timestamp('data_aprovacao')->nullable();
            $table->timestamps();
            
            $table->unique(['unidade_id', 'mes', 'ano']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalas');
    }
};
