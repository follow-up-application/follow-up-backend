<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('docentes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tutores', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('curso_division_turno', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tutor_alumno', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('tutor_alumno', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('curso_division_turno', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tutores', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('docentes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
