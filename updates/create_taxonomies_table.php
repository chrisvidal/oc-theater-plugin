<?php namespace Abnmt\Theater\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTaxonomiesTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('abnmt_theater_taxonomies');
        Schema::dropIfExists('abnmt_theater_taxonomy_groups');
        Schema::dropIfExists('abnmt_theater_taxonomy_relations');

        Schema::create('abnmt_theater_taxonomies', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug');

            $table->text('description')->nullable()->default(null);

            $table->integer('taxonomy_group_id');

            $table->timestamps();
        });

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

        Schema::create('abnmt_theater_taxonomy_relations', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('taxonomy_id');

            $table->integer('object_id');
            $table->string('object_type');

            $table->string('description')->nulable()->default(null);

            // $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_taxonomies');
        Schema::dropIfExists('abnmt_theater_taxonomy_groups');
        Schema::dropIfExists('abnmt_theater_taxonomy_relations');
    }

}
