<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribuicao_orcamento', function (Blueprint $table) {
            $table->decimal('margin_percentual', 5, 2)->default(10)->after('valor_distribuido');
        });
    }

    public function down(): void
    {
        Schema::table('distribuicao_orcamento', function (Blueprint $table) {
            $table->dropColumn('margin_percentual');
        });
    }
};
