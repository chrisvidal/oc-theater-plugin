<?php namespace Abnmt\Theater\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateParticipationsTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_participation');

        Schema::create('abnmt_theater_participation', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');

            $table->string('type')->nullable();

            $table->string('group')->nullable()->default(null);

            $table->integer('performance_id');
            $table->string('performance_title')->nullable()->default(null);
            $table->integer('person_id');
            $table->string('person_title')->nullable()->default(null);

            $table->integer('sort_order')->nullable();

            $table->text('description')->nullable()->default(null);


            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_participation');
    }

}
