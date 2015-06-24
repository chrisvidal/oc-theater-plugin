<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateEventCategoriesTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_event_categories');
        Schema::dropIfExists('abnmt_theater_events_categories');

        Schema::create('abnmt_theater_event_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->string('code')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('abnmt_theater_events_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('event_id')->unsigned();
            $table->integer('event_category_id')->unsigned();
            // $table->primary(['event_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_event_categories');
        Schema::dropIfExists('abnmt_theater_events_categories');
    }

}
