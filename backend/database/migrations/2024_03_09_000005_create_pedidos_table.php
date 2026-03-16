<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comercio_id')->constrained('negocios');
            $table->string('cliente_zona', 150);
            $table->longText('items_json'); // Detailed items
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('envio', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('cupon', 50)->nullable();
            $table->text('instrucciones')->nullable();
            $table->enum('estado', ['pendiente', 'aceptado', 'en_preparacion', 'en_camino', 'entregado', 'cancelado'])->default('pendiente');
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'tarjeta'])->default('efectivo');
            $table->enum('modalidad', ['delivery', 'pickup', 'dinein'])->default('delivery');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
