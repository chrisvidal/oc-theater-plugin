<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateNewsCategoriesTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_news_categories');
        Schema::dropIfExists('abnmt_theater_news_articles_categories');

        Schema::create('abnmt_theater_news_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->string('code')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('abnmt_theater_news_articles_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('news_id')->unsigned();
            $table->integer('news_category_id')->unsigned();
            // $table->primary(['news_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_news_categories');
        Schema::dropIfExists('abnmt_theater_news_articles_categories');
    }

}
