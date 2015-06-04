<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Person;
use Abnmt\Theater\Models\PersonCategory;
use October\Rain\Database\Updates\Seeder;

class SeedPeopleTable extends Seeder
{

    public function run()
    {

        require_once 'data/people.php';

        foreach ($people as $key => $person) {
            $this->createPerson($person);
        }

    }

    private function createPerson($person)
    {

        $categories = [
            'state' => array_key_exists('state', $person) ? $person['state'] : null,
        ];

        unset($person['state']);

        $model = Person::create($person);

        // echo $model->;

        if ( array_key_exists('state', $categories) ) {
            $category = PersonCategory::where('slug', '=', $categories['state'])->first();

            if (!is_null($category)) {
                $model->categories()->save($category);
            }
        }
    }

}
