<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Performance;
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
        Performance::create($performance);
    }
}
