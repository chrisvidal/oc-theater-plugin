<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Person as TheaterPerson;

class Person extends ComponentBase
{

    public $person;

    public $personPage;

    public $personsMenu;

    public function componentDetails()
    {
        return [
            'name'        => 'Персоналия',
            'description' => 'Выводит страницу биографии'
        ];
    }

    public function defineProperties()
    {
        return [
            'personPage' => [
                'title'       => 'Страница биографии',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'troupe',
            ],
        ];
    }

    public function getPersonPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    protected function prepareVars()
    {
        /*
         * Performance links
         */
        $this->personPage = $this->page['personPage'] = $this->property('personPage');
    }

    public function onRun()
    {

        $this->prepareVars();

        $this->person = $this->page['person'] = $this->loadPerson();

        $this->personsMenu = $this->page['personsMenu'] = $this->preparePersonsMenu();
    }

    protected function loadPerson()
    {

        $slug = $this->property('slug');
        $person = TheaterPerson::isPublished()->where('slug', '=', $slug)->first();

        return $person;
    }

    protected function preparePersonsMenu()
    {
        $state = array(
            'slug' => 'state',
            'title' => 'Актеры',
            'active' => 'active',
            'menu' => TheaterPerson::orderBy('title', 'asc')->isState()->get(),
            'persons' => TheaterPerson::orderBy('title', 'asc')->isState()->get(),
        );
        $cooperate = array(
            'slug' => 'cooperate',
            'title' => 'Приглашенные артисты',
            'active' => '',
            'menu' => TheaterPerson::orderBy('title', 'asc')->isCooperate()->get(),
            'persons' => TheaterPerson::orderBy('title', 'asc')->isCooperate()->get(),
        );

        return $menu = array($state, $cooperate);

        // $menu->each(function($section){
        //     $section->performances->each(function($performance){
        //         $performance->setUrl($this->performancePage, $this->controller);
        //     })
        // })
    }
}