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
        Schema::create('socios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('rut')->nullable();
            $table->string('direccion')->nullable();
            $table->string('numero_casa')->nullable();
            $table->string('sector')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('codigo_pago')->nullable()->unique();
            $table->string('estado')->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
