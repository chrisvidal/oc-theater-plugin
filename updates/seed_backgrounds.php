<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Background;
use October\Rain\Database\Updates\Seeder;

class SeedBackgroundsTable extends Seeder
{

    public function run()
    {

        $layouts = [
            [
                'title' => 'Афиша',
                'slug'  => 'playbill',
                'meta'  => [],
            ],
        ];

        foreach ($layouts as $layout) {
            $model = Background::create($layout);
        }

    }

}
