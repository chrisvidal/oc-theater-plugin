<?php namespace Abnmt\Theater\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateBackgroundsTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('abnmt_theater_backgrounds');
        Schema::create('abnmt_theater_backgrounds', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->string('description')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_backgrounds');
    }

}
