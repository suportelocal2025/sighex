<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            $table->boolean('usa_margem')->default(false);
            $table->boolean('excede_margem')->default(false);
            $table->boolean('requer_aprovacao_super')->default(false);
            $table->decimal('valor_previsto', 15, 2)->nullable();
            $table->decimal('orcamento_mes', 15, 2)->nullable();
            $table->decimal('limite_margem', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            $table->dropColumn(['usa_margem', 'excede_margem', 'requer_aprovacao_super', 'valor_previsto', 'orcamento_mes', 'limite_margem']);
        });
    }
};
