<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\EventCategory;
use October\Rain\Database\Updates\Seeder;

class SeedEventCategoriesTable extends Seeder
{

    public function run()
    {

        require_once 'data/eventcategories.php';

        foreach ($categories as $key => $category) {
            $this->createCategory($category);
        }

    }

    private function createCategory($category)
    {
        EventCategory::create($category);
    }
}
