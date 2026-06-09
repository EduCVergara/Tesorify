<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cuotas', function (Blueprint $table) {
            $table->decimal('monto_pagado', 12, 2)->default(0)->after('monto');
        });

        DB::table('cuotas')
            ->where('estado', 'pagada')
            ->update(['monto_pagado' => DB::raw('monto')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuotas', function (Blueprint $table) {
            $table->dropColumn('monto_pagado');
        });
    }
};
