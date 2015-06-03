<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

class Press extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Пресса',
            'description' => 'Выводит список статей прессы'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}