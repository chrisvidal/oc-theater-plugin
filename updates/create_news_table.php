<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateNewsTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_news');

        Schema::create('abnmt_theater_news', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->string('excerpt')->nullable()->default(null);
            $table->text('content')->nullable()->default(null);

            // $table->text('cover')->nullable()->default(null);

            $table->datetime('published_at')->nullable()->default(null);
            $table->boolean('published')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_news');
    }

}
