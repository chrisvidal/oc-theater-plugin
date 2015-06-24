<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePressCategoriesTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_press_categories');
        Schema::dropIfExists('abnmt_theater_press_articles_categories');

        Schema::create('abnmt_theater_press_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->string('code')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('abnmt_theater_press_articles_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('press_id')->unsigned();
            $table->integer('press_category_id')->unsigned();
            // $table->primary(['press_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_press_categories');
        Schema::dropIfExists('abnmt_theater_press_articles_categories');
    }

}
