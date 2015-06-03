<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

class Playbill extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Афиша',
            'description' => 'Выводит афишу'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}