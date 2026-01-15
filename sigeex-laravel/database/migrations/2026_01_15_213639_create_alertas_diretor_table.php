<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_diretor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->constrained('unidades')->onDelete('cascade');
            $table->foreignId('escala_id')->nullable()->constrained('escalas')->onDelete('cascade');
            $table->string('tipo');
            $table->string('titulo');
            $table->text('mensagem');
            $table->integer('mes')->nullable();
            $table->integer('ano')->nullable();
            $table->timestamp('prazo_limite')->nullable();
            $table->boolean('lido')->default(false);
            $table->boolean('email_enviado')->default(false);
            $table->timestamp('email_enviado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_diretor');
    }
};
