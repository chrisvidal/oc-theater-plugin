<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\PressCategory;
use October\Rain\Database\Updates\Seeder;

class SeedPressCategoriesTable extends Seeder
{

    public function run()
    {

        require_once 'data/presscategories.php';

        foreach ($categories as $key => $category) {
            $this->createCategory($category);
        }

    }

    private function createCategory($category)
    {
        PressCategory::create($category);
    }
}
