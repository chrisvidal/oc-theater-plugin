<?php namespace Abnmt\Theater\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreatePeopleTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_people');

        Schema::create('abnmt_theater_people', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();

            $table->string('family_name');
            $table->string('given_name');
            $table->string('gender')->nullable()->default(null);

            $table->string('grade')->nullable()->default(null);

            $table->text('bio')->nullable()->default(null);

            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_people');
    }

}
