<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aranceles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained('grados')->cascadeOnDelete();
            $table->foreignId('dojo_id')->constrained('dojos')->cascadeOnDelete();
            $table->string('tipo', 30);
            $table->decimal('precio', 10, 2)->default(0);
            $table->text('observacion')->nullable();
            $table->smallInteger('status')->default(1);

            $table->timestamps();
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
            $table->string('registerRole')->nullable();

            $table->softDeletes();
            $table->foreignId('deleteUser_id')->nullable()->constrained('users');
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();

            $table->index(['grado_id', 'dojo_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aranceles');
    }
};
