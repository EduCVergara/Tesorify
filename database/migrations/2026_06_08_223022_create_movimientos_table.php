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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('tipo');
            $table->string('categoria')->nullable();
            $table->string('descripcion');
            $table->string('nombre_origen')->nullable();
            $table->decimal('monto', 12, 2);
            $table->decimal('saldo', 12, 2)->nullable();
            $table->string('fuente')->default('manual');
            $table->string('estado_conciliacion')->default('pendiente');
            $table->foreignId('socio_id')->nullable()->constrained('socios')->nullOnDelete();
            $table->foreignId('cuota_id')->nullable()->constrained('cuotas')->nullOnDelete();
            $table->json('datos_originales')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fecha', 'tipo']);
            $table->index(['fuente', 'estado_conciliacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
