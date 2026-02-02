<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamento_global', function (Blueprint $table) {
            $table->id();
            $table->integer('ano')->unique();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->decimal('reserva_tecnica_percentual', 5, 2)->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamento_global');
    }
};
