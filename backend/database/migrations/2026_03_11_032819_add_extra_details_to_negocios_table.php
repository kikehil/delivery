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
            if (!Schema::hasColumn('negocios', 'direccion')) {
                $table->string('direccion')->nullable();
            }
            if (!Schema::hasColumn('negocios', 'horarios')) {
                $table->json('horarios')->nullable();
            }
            if (!Schema::hasColumn('negocios', 'opciones_servicio')) {
                $table->json('opciones_servicio')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn(['direccion', 'horarios', 'opciones_servicio']);
        });
    }
};
