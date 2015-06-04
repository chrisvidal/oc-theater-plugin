<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\PerformanceCategory;
use October\Rain\Database\Updates\Seeder;

class SeedPerformanceCategoriesTable extends Seeder
{

    public function run()
    {

        require_once 'data/performancecategories.php';

        foreach ($categories as $key => $category) {
            $this->createCategory($category);
        }

    }

    private function createCategory($category)
    {
        PerformanceCategory::create($category);
    }
}
