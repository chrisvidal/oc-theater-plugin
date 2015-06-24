<?php namespace Abnmt\Theater\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreatePerformancesTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_performances');

        Schema::create('abnmt_theater_performances', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();

            $table->string('description')->nullable();
            $table->string('author')->nullable();
            $table->text('content')->nullable();
            $table->text('synopsis')->nullable();

            // $table->text('authors')->nullable();
            // $table->text('roles')->nullable();

            $table->string('title_html')->nullable();
            // $table->string('state')->nullable();
            // $table->string('type')->nullable();
            // $table->string('genre')->nullable();
            $table->time('duration')->nullable();
            $table->smallInteger('entracte')->nullable();

            $table->date('premiere_date')->nullable();
            $table->date('removed_date')->nullable();

            $table->string('rate')->nullable();

            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_performances');
    }

}
