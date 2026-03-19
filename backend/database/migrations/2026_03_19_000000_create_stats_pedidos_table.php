<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stats_pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_negocio');
            $table->date('fecha');
            $table->unsignedInteger('cantidad')->default(0);
            $table->primary(['id_negocio', 'fecha']);
            $table->foreign('id_negocio')->references('id')->on('negocios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stats_pedidos');
    }
};
