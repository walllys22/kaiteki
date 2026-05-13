<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grados', function (Blueprint $table) {
            $table->unsignedSmallInteger('orden')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('grados', function (Blueprint $table) {
            $table->dropColumn('orden');
        });
    }
};
