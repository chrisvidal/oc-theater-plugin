<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePerformanceCategoriesTable extends Migration
{

    public function up()
    {

        Schema::dropIfExists('abnmt_theater_performance_categories');
        Schema::dropIfExists('abnmt_theater_performances_categories');

        Schema::create('abnmt_theater_performance_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->string('code')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('abnmt_theater_performances_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('performance_id')->unsigned();
            $table->integer('performance_category_id')->unsigned();
            // $table->primary(['performance_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_performance_categories');
        Schema::dropIfExists('abnmt_theater_performances_categories');
    }

}
