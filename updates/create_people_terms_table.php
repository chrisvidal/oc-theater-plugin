<?php namespace Abnmt\Theater\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreatePeopleTermsTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('abnmt_theater_people_terms');
        Schema::dropIfExists('abnmt_theater_people_term_groups');
        Schema::dropIfExists('abnmt_theater_people_term_relations');

        Schema::create('abnmt_theater_people_terms', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug');

            $table->text('description')->nullable()->default(null);

            $table->integer('people_term_group_id');

            $table->timestamps();
        });

        Schema::create('abnmt_theater_people_term_groups', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug');

            $table->text('description')->nullable()->default(null);

            $table->timestamps();
        });

        Schema::create('abnmt_theater_people_term_relations', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('people_term_id');
            $table->integer('person_id');

            $table->string('description')->nulable()->default(null);
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_people_terms');
        Schema::dropIfExists('abnmt_theater_people_term_groups');
        Schema::dropIfExists('abnmt_theater_people_term_relations');
    }

}
