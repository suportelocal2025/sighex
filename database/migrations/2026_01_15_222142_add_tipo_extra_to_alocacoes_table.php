<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alocacoes', function (Blueprint $table) {
            $table->string('tipo_extra', 10)->default('diurna')->after('horas_abono');
        });
    }

    public function down(): void
    {
        Schema::table('alocacoes', function (Blueprint $table) {
            $table->dropColumn('tipo_extra');
        });
    }
};
