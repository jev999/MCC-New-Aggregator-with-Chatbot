<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModifyDepartmentColumnTypeInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Change department from ENUM to VARCHAR(255)
        DB::statement("
            ALTER TABLE `users`
            MODIFY COLUMN `department` VARCHAR(255) NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert department back to ENUM (original values)
        DB::statement("
            ALTER TABLE `users`
            MODIFY COLUMN `department` ENUM('BSIT','BSBA','BEED','BSHM','BSED') NULL
        ");
    }
}