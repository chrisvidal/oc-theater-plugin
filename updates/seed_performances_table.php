<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Models\PerformanceCategory;
use October\Rain\Database\Updates\Seeder;

class SeedPerformancesTable extends Seeder
{

    public function run()
    {

        require_once 'data/performances.php';

        foreach ($performances as $key => $performance) {
            $this->createPerformance($performance);
        }

    }

    private function createPerformance($performance)
    {
        $categories = $performance['category'];

        unset($performance['category']);

        $model = Performance::create($performance);

        foreach ($categories as $key => $category_slug) {
            $category = PerformanceCategory::where('slug', '=', $category_slug)->first();
            if (!is_null($category)) {
                $model->categories()->save($category);
            }
        }

    }
}
