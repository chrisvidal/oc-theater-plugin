<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Performance as TheaterPerformance;

class Performance extends ComponentBase
{

    public $performance;

    public $performancePage;

    public $active;

    public function componentDetails()
    {
        return [
            'name'        => 'Спектакль',
            'description' => 'Выводит страницу спектакля'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Заголовок',
                'description' => 'Название спектакля',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
        ];
    }

    protected function prepareVars()
    {
        /*
         * Performance links
         */
        $this->performancePage = $this->page['performancePage'] = $this->property('performancePage');

        $this->performancesMenu = $this->page['performancesMenu'] = $this->preparePerformancesMenu();
    }

    public function onRun()
    {

        $this->prepareVars();

        $this->slug = $this->page['slug'] = $this->property('slug');

        $this->performance = $this->page['performance'] = $this->loadPerformance();
    }

    protected function loadPerformance()
    {

        $slug = $this->property('slug');
        $performance = TheaterPerformance::isPublished()->where('slug', '=', $slug)->first();

        return $performance;
    }

    protected function preparePerformancesMenu()
    {
        $slug = $this->property('slug');



        $normal_performance = TheaterPerformance::orderBy('title', 'asc')->isNormal()->get();
        $normal_performance->each(function($performance)
        {
            $performance->setUrl($this->performancePage, $this->controller);

            if ($performance->slug == $this->property('slug')) {
                $performance->active = 'active';
                $this->active = 'normal';
            }
        });

        $normal = array(
            'slug' => 'performances',
            'title' => 'Спектакли',
            'active' =>  ($this->active == 'normal') ? 'active' : '',
            'menu' => $normal_performance->sortBy('title'),
            'performances' => $normal_performance->sortBy('premiere_date')->reverse(),
        );



        $child_performance = TheaterPerformance::orderBy('title', 'asc')->isChild()->get();
        $child_performance->each(function($performance)
        {
            $performance->setUrl($this->performancePage, $this->controller);

            if ($performance->slug == $this->property('slug')) {
                $performance->active = 'active';
                $this->active = 'child';
            }
        });

        $child = array(
            'slug' => 'child',
            'title' => 'Детские спектакли',
            'active' =>  ($this->active == 'child') ? 'active' : '',
            'menu' => $child_performance->sortBy('title'),
            'performances' => $child_performance->sortBy('premiere_date')->reverse(),
        );



        $archive_performance = TheaterPerformance::orderBy('title', 'asc')->isArchive()->get();
        $archive_performance->each(function($performance)
        {
            $performance->setUrl($this->performancePage, $this->controller);

            if ($performance->slug == $this->property('slug')) {
                $performance->active = 'active';
                $this->active = 'archive';
            }
        });

        $archive = array(
            'slug' => 'archive',
            'title' => 'Архив',
            'active' =>  ($this->active == 'archive') ? 'active' : '',
            'menu' => $archive_performance->sortBy('title'),
            'performances' => $archive_performance->sortBy('premiere_date')->reverse(),
        );



        return $menu = array($normal, $child, $archive);
    }
}