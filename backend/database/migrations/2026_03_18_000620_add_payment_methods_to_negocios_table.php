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
        Schema::table('negocios', function (Blueprint $table) {
            $table->boolean('acepta_efectivo')->default(true)->after('consumo_sucursal');
            $table->boolean('acepta_tarjeta')->default(false)->after('acepta_efectivo');
            $table->boolean('acepta_transferencia')->default(false)->after('acepta_tarjeta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn(['acepta_efectivo', 'acepta_tarjeta', 'acepta_transferencia']);
        });
    }
};
