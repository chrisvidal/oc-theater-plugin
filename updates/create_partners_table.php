<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePartnersTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('abnmt_theater_partners');
        Schema::create('abnmt_theater_partners', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();

            $table->string('link');

            $table->string('badge_color')->nullable()->default(null);
            $table->string('badge_gray')->nullable()->default(null);
            $table->integer('size')->nullable()->default(null);

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_partners');
    }

}
