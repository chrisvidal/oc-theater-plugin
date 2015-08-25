<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePressesTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('abnmt_theater_presses');
        Schema::create('abnmt_theater_presses', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->text('content')->nullable()->default(null);
            $table->text('excerpt')->nullable()->default(null);

            $table->text('author')->nullable()->default(null);
            $table->text('source')->nullable()->default(null);
            $table->text('source_date')->nullable()->default(null);
            $table->text('source_link')->nullable()->default(null);

            $table->datetime('published_at')->nullable()->default(null);
            $table->boolean('published')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_presses');
    }

}
