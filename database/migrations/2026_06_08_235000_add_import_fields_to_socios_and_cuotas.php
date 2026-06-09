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
        Schema::table('socios', function (Blueprint $table) {
            $table->date('fecha_incorporacion')->nullable()->after('codigo_pago');
            $table->date('fecha_nacimiento')->nullable()->after('fecha_incorporacion');
            $table->json('datos_originales')->nullable()->after('observaciones');
        });

        Schema::table('cuotas', function (Blueprint $table) {
            $table->string('origen_importacion')->nullable()->after('observaciones');
            $table->json('datos_originales')->nullable()->after('origen_importacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('socios', function (Blueprint $table) {
            $table->dropColumn(['fecha_incorporacion', 'fecha_nacimiento', 'datos_originales']);
        });

        Schema::table('cuotas', function (Blueprint $table) {
            $table->dropColumn(['origen_importacion', 'datos_originales']);
        });
    }
};
