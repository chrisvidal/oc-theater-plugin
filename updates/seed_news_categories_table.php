<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\NewsCategory;
use October\Rain\Database\Updates\Seeder;

class SeedNewsCategoriesTable extends Seeder
{

    public function run()
    {

        require_once 'data/newscategories.php';

        foreach ($categories as $key => $category) {
            $this->createCategory($category);
        }

    }

    private function createCategory($category)
    {
        NewsCategory::create($category);
    }
}
