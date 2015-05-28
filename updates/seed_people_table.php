<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Person;
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
        Person::create($person);
    }

}
