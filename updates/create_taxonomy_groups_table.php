<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTaxonomyGroupsTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('abnmt_theater_taxonomy_groups');

        Schema::create('abnmt_theater_taxonomy_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug');

            $table->text('description')->nullable()->default(null);

            $table->string('object_type')->nullable()->default(null);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_taxonomy_groups');
    }

}
