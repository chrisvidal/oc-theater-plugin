<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateParticipationsTable extends Migration
{

    public function up()
    {
        Schema::create('abnmt_theater_participation', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');

            $table->string('type')->nullable();

            $table->string('group')->nullable()->default(null);

            $table->integer('performance_id');
            $table->integer('person_id');

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
