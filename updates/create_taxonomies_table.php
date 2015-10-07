<?php namespace Abnmt\Theater\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateTaxonomiesTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('abnmt_theater_taxonomies');
        Schema::dropIfExists('abnmt_theater_taxonomies_relations');

        Schema::create('abnmt_theater_taxonomies', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('title');
            $table->string('slug')->index();
            $table->string('description')->nullable();

            $table->string('model')->nullable()->default(null);

            $table->integer('parent_id')->unsigned()->nullable()->default(null);

            $table->integer('sort_order')->nullable();

            $table->timestamps();
        });

        Schema::create('abnmt_theater_taxonomies_relations', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('taxonomy_id')->unsigned();
            $table->integer('relation_id')->unsigned();
            $table->string('relation_type');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_taxonomies');
        Schema::dropIfExists('abnmt_theater_taxonomies_relations');
    }

}
