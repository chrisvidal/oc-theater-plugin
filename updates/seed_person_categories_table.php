<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\PersonCategory;
use October\Rain\Database\Updates\Seeder;

class SeedPersonCategoriesTable extends Seeder
{

    public function run()
    {

        require_once 'data/personcategories.php';

        foreach ($categories as $key => $category) {
            $this->createCategory($category);
        }

    }

    private function createCategory($category)
    {
        PersonCategory::create($category);
    }
}
