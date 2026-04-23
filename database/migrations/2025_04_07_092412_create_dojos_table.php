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
        Schema::create('dojos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('logo',600)->nullable();
            $table->foreignId('person_id')->nullable()->constrained('people');  

            $table->foreignId('ciudad_id')->nullable()->constrained('ciudads');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();

            $table->smallInteger('status')->default(1);
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dojos');
    }
};
