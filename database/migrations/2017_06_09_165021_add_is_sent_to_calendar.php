<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSentToCalendar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paid_time_offs', function (Blueprint $table) {
            $table->tinyInteger('is_sent_to_calendar')->unsigned()->index()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paid_time_offs', function (Blueprint $table) {
            //$table->dropColumn('is_sent_to_calendar');
        });
    }
}
