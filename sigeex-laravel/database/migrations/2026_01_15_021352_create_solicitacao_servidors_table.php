<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacao_servidores', function (Blueprint $table) {
            $table->id();
            $table->string('matricula', 50);
            $table->string('nome', 255);
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->string('cargo', 100)->nullable();
            $table->foreignId('solicitante_id')->constrained('usuarios');
            $table->enum('status', ['pendente', 'aprovada', 'rejeitada'])->default('pendente');
            $table->foreignId('aprovador_id')->nullable()->constrained('usuarios');
            $table->text('motivo_rejeicao')->nullable();
            $table->timestamp('data_aprovacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacao_servidores');
    }
};
