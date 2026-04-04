<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            
            $table->string('ci')->nullable();

            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('paternal_surname')->nullable();
            $table->string('maternal_surname')->nullable();

            $table->date('birth_date')->nullable();
            $table->string('email')->nullable();
            $table->string('country_code')->nullable();
            $table->string('phone')->nullable();

            $table->text('address')->nullable();

            $table->string('gender')->nullable();
            $table->string('image',600)->nullable();      

            
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('people');
    }
};
