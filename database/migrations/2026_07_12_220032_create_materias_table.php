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
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion');

            // El turno ya no vive en la materia: se hereda del curso division turno.
            $table->foreignId('curso_division_turno_id')
                ->constrained('curso_division_turno')
                ->restrictOnDelete();

            $table->foreignUuid('docente_id')->constrained('docentes')->restrictOnDelete();

            $table->timestamps();

            // Dos materias homónimas solo pueden coexistir si pertenecen a cursos distintos.
            $table->unique(['nombre', 'curso_division_turno_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
