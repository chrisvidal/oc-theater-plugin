<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePeopleTable extends Migration
{

    public function up()
    {
        Schema::create('abnmt_theater_people', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title')->nullable();
            $table->string('slug')->index();

            $table->string('family_name');
            $table->string('given_name');
            $table->string('gender');
            $table->string('grade')->nullable();
            $table->string('state')->nullable();
            $table->text('bio')->nullable();
            $table->text('bio_html')->nullable();
            $table->text('roles')->nullable();
            $table->text('roles_html')->nullable();

            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_people');
    }

}
