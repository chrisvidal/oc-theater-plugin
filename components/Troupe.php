<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

class Troupe extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Труппа',
            'description' => 'Выводит список труппы'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}