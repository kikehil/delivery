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
        Schema::create('liquidaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->onDelete('cascade');
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->decimal('total_ventas', 12, 2);
            $table->decimal('comision_plataforma', 12, 2);
            $table->decimal('monto_liquidar', 12, 2);
            $table->string('estado')->default('pendiente'); // pendiente, pagado, cancelado
            $table->timestamp('fecha_pago')->nullable();
            $table->string('comprobante_url')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidaciones');
    }
};
