<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoriesTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_categories');
        Schema::dropIfExists('abnmt_theater_object_categories');

        Schema::create('abnmt_theater_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug');
        });

        Schema::create('abnmt_theater_object_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('category_id');
            $table->string('object_type');
            $table->integer('object_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_categories');
        Schema::dropIfExists('abnmt_theater_object_categories');
    }

}
