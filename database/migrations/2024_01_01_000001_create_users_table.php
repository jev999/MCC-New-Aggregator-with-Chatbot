<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('surname');
            $table->string('ms365_account')->unique();
            $table->string('password');
            $table->enum('role', ['student', 'faculty']);
            $table->enum('department', ['BSIT', 'BSBA', 'BEED', 'BSHM'])->nullable();
            $table->enum('year_level', ['1st Year', '2nd Year', '3rd Year', '4th Year'])->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
