<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Performance as TheaterPerformance;

class Performances extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Репертуар',
            'description' => 'Выводит репертуар'
        ];
    }


    /*
     * Define Properties
     */
    public function defineProperties()
    {
        return [
            'performancePage' => [
                'title'       => 'Страница спектакля',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'single/performance',
            ],
            'personPage' => [
                'title'       => 'Страница биографии',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'single/person',
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


    /*
     * Registered Propertie vars
     */
    public $performancePage;
    public $personPage;


    /*
     * Registered Main Page var
     */
    public $performances;


    /*
     * Prepared Page vars
     */
    protected function prepareVars()
    {
        /*
         * URLs
         */
        $this->performancePage = $this->page['performancePage'] = $this->property('performancePage');
        $this->personPage = $this->page['personPage'] = $this->property('personPage');

    }


    /*
     * Run
     */
    public function onRun()
    {
        $this->prepareVars();

        $this->performances = $this->page['performances'] = $this->listPerformances();

    }




    protected function listPerformances()
    {

        /*
         * Get all Performances with relations
         */
        $performances = TheaterPerformance::with(['playbill', 'repertoire', 'background', 'featured', 'video', 'participations', 'participations.person', 'presses'])->isPublished()->get();

        /*
         * Add a "URL" helper attribute for linking to each performance and person relation
         */
        $performances->each(function($performance)
        {
            $performance->setUrl($this->performancePage, $this->controller);

            $performance->participations->each(function($role)
            {
                $role->person->setUrl($this->personPage, $this->controller);
            });

        });

        $normal = $performances->filter(function($performance)
        {
            if ($performance->type == 'normal' && $performance->state != 'archived')
                return $performance;
        });
        $child = $performances->filter(function($performance)
        {
            if ($performance->type == 'child' && $performance->state != 'archived')
                return $performance;
        });
        $archive = $performances->filter(function($performance)
        {
            if ($performance->state == 'archived')
                return $performance;
        });

        // $section['menu'] = $items->sortBy('title');
        // $section['performances'] = $items->sortByDesc('premiere_date');
        // $section['menu'] = $items;
        // $section['performances'] = $items;


        $data = [
            'normal' => [
                'slug' => 'performances',
                'title' => 'Спектакли',
                'active' => 'active',
                'menu' => $normal->sortBy('title')->values(),
                'performances' => $normal->sortByDesc('premiere_date')->values(),
            ],
            'child' => [
                'slug' => 'child',
                'title' => 'Детские спектакли',
                'active' => '',
                'menu' => $child->sortBy('title')->values(),
                'performances' => $child->sortByDesc('premiere_date')->values(),
            ],
            'archived' => [
                'slug' => 'archive',
                'title' => 'Архив',
                'active' => '',
                'menu' => $archive->sortBy('title')->values(),
                'performances' => $archive->sortByDesc('premiere_date')->values(),
            ]
        ];

        return $data;
    }


}