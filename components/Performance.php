<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

class Performance extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Спектакль',
            'description' => 'Выводит одиночный спектакль'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}