<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Performance as TheaterPerformance;

class Performances extends ComponentBase
{

    public $performances;

    public $performancesMenu;

    public $performancePage;

    public $test;

    public function componentDetails()
    {
        return [
            'name'        => 'Репертуар',
            'description' => 'Выводит репертуар'
        ];
    }

    public function defineProperties()
    {
        return [
            'performancePage' => [
                'title'       => 'Страница спектакля',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'performance',
            ],
        ];
    }

    public function getPerformancePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->performances = $this->page['performances'] = $this->listPerformances();

    }

    protected function prepareVars()
    {
        /*
         * Performance links
         */
        $this->performancePage = $this->page['performancePage'] = $this->property('performancePage');

        $this->performancesMenu = $this->page['performancesMenu'] = $this->preparePerformancesMenu();

        $this->page['test'] = json_encode($this->preparePerformancesMenu(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

    }

    protected function listPerformances()
    {
        /*
         * List all the performances
         */
        $performances = TheaterPerformance::orderBy('premiere_date', 'desc')
            ->get();

        /*
         * Add a "url" helper attribute for linking to each performances
         */
        $performances->each(function($performance){
            $performance->setUrl($this->performancePage, $this->controller);
        });

        return $performances;
    }

    protected function preparePerformancesMenu()
    {

        $normal_performance = TheaterPerformance::orderBy('title', 'asc')
            ->with('repertoire')
            ->isNormal()
            ->get();

        $normal_performance->each(function($performance)
        {
            $performance->setUrl($this->performancePage, $this->controller);
        });

        $normal = array(
            'slug' => 'performances',
            'title' => 'Спектакли',
            'active' => 'active',
            'menu' => $normal_performance->sortBy('title'),
            'performances' => $normal_performance->sortBy('premiere_date')->reverse(),
        );

        $child_performance = TheaterPerformance::orderBy('title', 'asc')
            ->with('repertoire')
            ->isChild()
            ->get();

        $child_performance->each(function($performance)
        {
            $performance->setUrl($this->performancePage, $this->controller);
        });

        $child = array(
            'slug' => 'child',
            'title' => 'Детские спектакли',
            'active' => '',
            'menu' => $child_performance->sortBy('title'),
            'performances' => $child_performance->sortBy('premiere_date')->reverse(),
        );

        $archive_performance = TheaterPerformance::orderBy('title', 'asc')
            ->with('repertoire')
            ->isArchive()
            ->get();

        $archive_performance->each(function($performance)
        {
            $performance->setUrl($this->performancePage, $this->controller);
        });

        $archive = array(
            'slug' => 'archive',
            'title' => 'Архив',
            'active' => '',
            'menu' => $archive_performance->sortBy('title'),
            'performances' => $archive_performance->sortBy('premiere_date')->reverse(),
        );


        return $menu = array($normal, $child, $archive);
    }

}