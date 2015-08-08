<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateEventsTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_events');

        Schema::create('abnmt_theater_events', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('description')->nullable();

            $table->date('event_date');

            $table->integer('relation_id')->unsigned()->nullable();
            $table->string('relation_type')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_events');
    }

}
