<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El código institucional pertenece al vínculo tutor/alumno (tutor_alumno),
     * no al alumno: un alumno puede tener más de un tutor y cada vínculo tiene su
     * propio código. En alumnos quedaba una columna duplicada que nadie leía.
     */
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('codigo_institucional');
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('codigo_institucional')->nullable();
        });
    }
};