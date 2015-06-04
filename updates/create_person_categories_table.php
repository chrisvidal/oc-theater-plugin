<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePersonCategoriesTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_person_categories');
        Schema::dropIfExists('abnmt_theater_persons_categories');

        Schema::create('abnmt_theater_person_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->string('code')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('abnmt_theater_persons_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('person_id')->unsigned();
            $table->integer('person_category_id')->unsigned();
            // $table->primary(['person_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_person_categories');
        Schema::dropIfExists('abnmt_theater_persons_categories');
    }

}
