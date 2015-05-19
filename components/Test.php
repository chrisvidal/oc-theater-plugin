<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

use Cms\Classes\Page;
use Abnmt\Theater\Models\Performance as TheaterPerformance;
use Abnmt\Theater\Models\Person as TheaterPerson;

class Test extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Test Component',
            'description' => 'No description provided yet...'
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
            'performancePage' => [
                'title'       => 'Страница спектакля',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'performance',
            ],
            'personPage' => [
                'title'       => 'Страница персоналии',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'person',
            ],
        ];
    }




    public function getPerformancePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getPersonPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }



    public $performancePage;
    public $personPage;


    protected function prepareVars()
    {
        /*
         * URL links
         */
        $this->performancePage = $this->page['performancePage'] = $this->property('performancePage');
        $this->personPage = $this->page['personPage'] = $this->property('personPage');

    }


    public function onRun()
    {
        $this->prepareVars();

        $this->performances = $this->page['performances'] = $this->listPerformances();
        // $this->persons = $this->page['persons'] = $this->listPersons();

    }


    protected function listPerformances()
    {
        $performances = TheaterPerformance::with(['playbill', 'repertoire', 'background', 'featured', 'video', 'participations', 'participations.person'])->get();

        $performances->each(function($performance)
        {
            $performance->setUrl($this->performancePage, $this->controller);

            $performance->participations->each(function($role)
            {
                $role->person->setUrl($this->personPage, $this->controller);
            });

        });

        $child = $performances->where('type', '=', 'child');

        /*
         * All performances
         */
        // $performances = $performances->sortBy('premiere_date')->groupBy('type');

        // $performances = [
        //     [
        //         'slug' => 'performances',
        //         'title' => 'Спектакли',
        //         'active' => 'active',
        //         'menu' => $performances->normal->sortBy('title'),
        //         'performances' => $performances->normal->sortByDesc('premiere_date'),
        //     ],
        //     [
        //         'slug' => 'child',
        //         'title' => 'Детские спектакли',
        //         'active' => '',
        //         'menu' => $performances->sortBy('title'),
        //         'performances' => $performances->sortByDesc('premiere_date'),
        //     ],
        //     [
        //         'slug' => 'archive',
        //         'title' => 'Архив',
        //         'active' => '',
        //         'menu' => $performances->sortBy('title'),
        //         'performances' => $performances->sortByDesc('premiere_date'),
        //     ]
        // ];


        return $this->testPrint($performances);
        // return $performances;
        // return $performances->toJSON();
    }

    protected function testPrint($var)
    {
        return json_encode($var, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }

}