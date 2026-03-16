<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->enum('tipo', ['fijo', 'porcentaje']);
            $table->decimal('valor', 10, 2);
            $table->integer('limite_uso')->default(100);
            $table->integer('usos_actuales')->default(0);
            $table->enum('estado', ['activo', 'expirado'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupones');
    }
};
