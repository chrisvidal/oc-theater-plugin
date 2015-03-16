<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Person as TheaterPerson;

class Troupe extends ComponentBase
{

    public $troupe;

    public $personPage;

    public $personsMenu;

    public function componentDetails()
    {
        return [
            'name'        => 'Труппа',
            'description' => 'Выводит страницу труппы'
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

    public function onRun()
    {
        $this->prepareVars();

        $this->troupe = $this->page['troupe'] = $this->listPersons();

        $this->personsMenu = $this->page['personsMenu'] = $this->preparePersonsMenu();

    }

    protected function prepareVars()
    {
        /*
         * Performance links
         */
        $this->personPage = $this->page['personPage'] = $this->property('personPage');
    }

    protected function listPersons()
    {
        /*
         * List all the persons
         */
        $persons = TheaterPerson::orderBy('family_name', 'asc')->get();

        /*
         * Add a "url" helper attribute for linking to each persons
         */
        $persons->each(function($person){
            $person->setUrl($this->personPage, $this->controller);
        });

        return $persons;
    }

    protected function preparePersonsMenu()
    {

        // $director_persons = TheaterPerson::orderBy('family_name', 'asc')->isDirector()->get();

        // $director_persons->each(function($person){
        //     $person->setUrl($this->personPage, $this->controller);
        // });

        // $director = array(
        //     'slug' => 'director',
        //     'title' => 'Художественный руководитель',
        //     'active' => 'active',
        //     'menu' => $director_persons,
        //     'persons' => $director_persons,
        // );


        $grade_persons = TheaterPerson::orderBy('family_name', 'asc')->isGrade()->get();

        $grade_persons->each(function($person){
            $person->setUrl($this->personPage, $this->controller);
        });


        $state_persons = TheaterPerson::orderBy('family_name', 'asc')->isState()->get();

        $state_persons->each(function($person){
            $person->setUrl($this->personPage, $this->controller);
        });


        $_persons = $grade_persons->merge($state_persons);

        $state = array(
            'slug' => 'state',
            'title' => 'Актеры',
            'active' => '',
            'menu' => $_persons,
            'persons' => $_persons,
        );


        $cooperate_persons = TheaterPerson::orderBy('family_name', 'asc')->isCooperate()->get();

        $cooperate_persons->each(function($person){
            $person->setUrl($this->personPage, $this->controller);
        });

        $cooperate = array(
            'slug' => 'cooperate',
            'title' => 'Приглашенные артисты',
            'active' => '',
            'menu' => $cooperate_persons,
            'persons' => $cooperate_persons,
        );

        // $menu->each(function($section){
        //     $section->performances->each(function($performance){
        //         $performance->setUrl($this->performancePage, $this->controller);
        //     })
        // })
        return $menu = array($state, $cooperate);

    }
}