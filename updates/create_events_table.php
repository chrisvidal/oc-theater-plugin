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
            $table->string('slug')->index();

            $table->datetime('datetime');

            $table->integer('performance_id')->unsigned();
            $table->integer('playbill_id')->unsigned();

            $table->boolean('published')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_events');
    }

}
