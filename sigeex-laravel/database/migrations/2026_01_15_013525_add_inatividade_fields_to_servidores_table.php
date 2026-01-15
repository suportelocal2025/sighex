<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('servidores', function (Blueprint $table) {
            $table->date('inativo_inicio')->nullable();
            $table->date('inativo_fim')->nullable();
            $table->string('motivo_inativo')->nullable();
            $table->boolean('inativo_indefinido')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('servidores', function (Blueprint $table) {
            $table->dropColumn(['inativo_inicio', 'inativo_fim', 'motivo_inativo', 'inativo_indefinido']);
        });
    }
};
