<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsContractorToEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->tinyInteger('is_contractor')->unsigned()->index()->default(0);
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            // SQLite cannot drop a column that is still indexed.
            $table->dropIndex(['is_contractor']);
            $table->dropColumn('is_contractor');
        });
    }
}
