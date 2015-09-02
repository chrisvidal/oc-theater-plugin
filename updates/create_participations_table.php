<?php namespace Abnmt\Theater\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateParticipationsTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_participations');

        Schema::create('abnmt_theater_participations', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');

            $table->string('type')->nullable();
            $table->string('group')->nullable()->default(null);

            $table->integer('performance_id')->unsigned();
            $table->integer('person_id')->unsigned();

            $table->integer('sort_order')->nullable();

            $table->text('description')->nullable()->default(null);


            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_participations');
    }

}
