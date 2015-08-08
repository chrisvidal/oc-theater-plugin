<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateArticlesTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_articles');
        Schema::dropIfExists('abnmt_theater_articles_relations');

        Schema::create('abnmt_theater_articles', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->text('content')->nullable()->default(null);
            $table->text('excerpt')->nullable()->default(null);

            $table->text('author')->nullable()->default(null);

            $table->datetime('published_at')->nullable()->default(null);
            $table->boolean('published')->default(false);

            $table->timestamps();
        });

        Schema::create('abnmt_theater_articles_relations', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('article_id')->unsigned();
            $table->integer('relation_id')->unsigned()->index();
            $table->string('relation_type')->index();

            $table->string('description')->nullable()->default(null);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_articles');
        Schema::dropIfExists('abnmt_theater_articles_relations');
    }

}
