<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

class Person extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Персоналия',
            'description' => 'Выводит страницу биографии'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}