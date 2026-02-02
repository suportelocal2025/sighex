<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alocacoes', function (Blueprint $table) {
            $table->foreignId('equipe_id')->nullable()->constrained('equipes')->nullOnDelete();
            $table->foreignId('modulo_id')->nullable()->constrained('modulos')->nullOnDelete();
            $table->integer('dia')->nullable();
            $table->integer('horas_abono')->default(0);
            $table->boolean('is_lider')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('alocacoes', function (Blueprint $table) {
            $table->dropForeign(['equipe_id']);
            $table->dropForeign(['modulo_id']);
            $table->dropColumn(['equipe_id', 'modulo_id', 'dia', 'horas_abono', 'is_lider']);
        });
    }
};
