<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePlaybillsTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('abnmt_theater_playbills');

        Schema::create('abnmt_theater_playbills', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            // $table->string('title');
            // $table->string('slug')->index();

            $table->date('date');

            $table->boolean('published')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_playbills');
    }

}
