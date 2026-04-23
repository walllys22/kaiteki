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
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dojo_id')->nullable()->constrained('dojos');
            $table->foreignId('person_id')->nullable()->constrained('people');  
            $table->date('entry_date')->nullable();
            $table->foreignId('horario_id')->nullable()->constrained('horarios');
            $table->foreignId('grado_id')->nullable()->constrained('grados');
            $table->smallInteger('status')->default(1);
            $table->text('observacion')->nullable();

            $table->smallInteger('status')->default(1);

            $table->timestamps();            
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
            $table->string('registerRole')->nullable();

            $table->softDeletes();
            $table->foreignId('deleteUser_id')->nullable()->constrained('users');
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
