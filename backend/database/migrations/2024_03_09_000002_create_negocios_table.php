<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negocios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->nullable()->constrained('users')->onDelete('set null');
            $table->string('nombre');
            $table->string('categoria')->nullable();
            $table->string('logo_url')->nullable();
            $table->enum('plan', ['esencial', 'pro', 'elite'])->default('esencial');
            $table->enum('estado', ['pendiente', 'activo'])->default('activo');
            $table->foreignId('id_zona_base')->nullable()->constrained('zonas')->nullOnDelete();
            $table->string('nombre_responsable')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono_contacto', 20)->default('525500000000');
            $table->string('whatsapp_pedidos', 20)->nullable();
            $table->text('direccion')->nullable();
            // New fields for the modern stack usually include:
            $table->boolean('modulo_abierto')->default(true);
            $table->boolean('entrega_domicilio')->default(true);
            $table->boolean('recolecta_pedidos')->default(true);
            $table->boolean('consumo_sucursal')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negocios');
    }
};
