<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Person as TheaterPerson;

class Person extends ComponentBase
{

    public $person;

    /*
     * Registered Propertie vars
     */
    public $performancePage;
    public $personPage;

    public $personsMenu;

    public $active;
    public $test;

    public $roles;

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
            'slug' => [
                'title'       => 'Заголовок',
                'description' => 'Имя',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'personPage' => [
                'title'       => 'Страница биографии',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'single/person',
            ],
            'performancePage' => [
                'title'       => 'Страница спектакля',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'single/performance',
            ],
        ];
    }


    /*
     * Get Properties
     */
    public function getPerformancePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
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
        $this->performancePage = $this->page['performancePage'] = $this->property('performancePage');
        $this->personPage = $this->page['personPage'] = $this->property('personPage');
    }

    public function onRun()
    {

        $this->prepareVars();

        $this->person = $this->page['person'] = $this->loadPerson();

        $this->person->roles = $this->page['person']['roles'] = $this->roles;

        $this->personsMenu = $this->page['personsMenu'] = $this->preparePersonsMenu();

        $this->page['test'] = json_encode($this->person, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }

    protected function loadPerson()
    {

        $slug = $this->property('slug');
        $person = TheaterPerson::isPublished()->where('slug', '=', $slug)->with(['portrait', 'participations', 'participations.performance', 'presses'])->first();

        $person->participations->each(function($participation){

            $participation->performance->setUrl($this->performancePage, $this->controller);

            if ($participation->type == 'roles'){
                $this->roles[$participation->performance->title]['roles'][] = $participation->title;
                $this->roles[$participation->performance->title]['url'] = $participation->performance->url;
            }

        });

        return $person;
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


        $slug = $this->property('slug');

        $grade_persons = TheaterPerson::orderBy('family_name', 'asc')->with(['portrait'])->isGrade()->get();

        $grade_persons->each(function($person){
            $person->setUrl($this->personPage, $this->controller);

            if ($person->slug == $this->property('slug')){
                $person->active = 'active';
                $this->active = 'state';
            }
        });


        $state_persons = TheaterPerson::orderBy('family_name', 'asc')->with(['portrait'])->isState()->get();

        $state_persons->each(function($person){
            $person->setUrl($this->personPage, $this->controller);

            if ($person->slug == $this->property('slug')){
                $person->active = 'active';
                $this->active = 'state';
            }
        });


        $_persons = $grade_persons->merge($state_persons);

        $state = array(
            'slug' => 'state',
            'title' => 'Актеры',
            'active' =>  ($this->active == 'state') ? 'active' : '',
            'menu' => $_persons,
            'persons' => $_persons,
        );


        $cooperate_persons = TheaterPerson::orderBy('family_name', 'asc')->with(['portrait'])->isCooperate()->get();

        $cooperate_persons->each(function($person){
            $person->setUrl($this->personPage, $this->controller);

            if ($person->slug == $this->property('slug')){
                $person->active = 'active';
                $this->active = 'cooperate';
            }
        });

        $cooperate = array(
            'slug' => 'cooperate',
            'title' => 'Приглашенные артисты',
            'active' =>  ($this->active == 'cooperate') ? 'active' : '',
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