<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Taxonomy;
use Abnmt\Theater\Models\TaxonomyGroup;
use October\Rain\Database\Updates\Seeder;

class SeedTaxonomiesTable extends Seeder
{

    public function run()
    {

        require_once 'data/taxonomies.php';

        foreach ($taxonomies as $key => $taxonomy) {
            $this->createTaxonomy($taxonomy);
        }
        foreach ($taxonomy_groups as $key => $taxonomy_group) {
            $this->createTaxonomyGroup($taxonomy_group);
        }

    }

    private function createTaxonomy($taxonomy)
    {
        Taxonomy::create($taxonomy);
    }
    private function createTaxonomyGroup($taxonomy_group)
    {
        TaxonomyGroup::create($taxonomy_group);
    }
}
