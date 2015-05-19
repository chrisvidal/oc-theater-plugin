<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Performance as TheaterPerformance;

class Performance extends ComponentBase
{
    /*
     * Registered Main Page var
     */
    public $performance;
    public $roles;
    public $participations_g;

    public $active;

    public $test;

    public function componentDetails()
    {
        return [
            'name'        => 'Спектакль',
            'description' => 'Выводит страницу спектакля'
        ];
    }

    /*
     * Define Properties
     */
    public function defineProperties()
    {
        return
        [
            'slug' => [
                'title'       => 'Заголовок',
                'description' => 'Название спектакля',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
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
     * Prepared Page vars
     */
    protected function prepareVars()
    {
        /*
         * Performance links
         */
        $this->performancePage = $this->page['performancePage'] = $this->property('performancePage');
        $this->personPage = $this->page['personPage'] = $this->property('personPage');

        // $this->performancesMenu = $this->page['performancesMenu'] = $this->preparePerformancesMenu();
    }

    public function onRun()
    {

        $this->prepareVars();

        $this->slug = $this->page['slug'] = $this->property('slug');

        $this->performance = $this->page['performance'] = $this->loadPerformance();
        $this->performances = $this->page['performances'] = $this->listPerformances();

        // $this->page['roles'] = $this->roles;
        $this->page['test'] = json_encode($this->loadPerformance(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }

    protected function loadPerformance()
    {

        $slug = $this->property('slug');
        $performance = TheaterPerformance::isPublished()
            ->where('slug', '=', $slug)
            ->with(['background', 'featured', 'video', 'participations', 'participations.person', 'presses'])
            ->first();

        /*
         * Add a "URL" helper attribute for linking to each performance and person relation
         */

        $this->roles = array();
        $this->participations_g = array();

        $performance->participations->each(function($role)
        {
            $role->person->setUrl($this->personPage, $this->controller);

            if ($role['group'] != NULL)
                $this->roles[$role['group']][] = $role;

            $this->participations_g[$role['title']]['title'] = $role->title;
            $this->participations_g[$role['title']]['type'] = $role->type;
            $this->participations_g[$role['title']]['description'] = $role->description;
            $this->participations_g[$role['title']]['group'] = $role->group;
            $this->participations_g[$role['title']]['persons'][] = $role->person;
        });




        if ($performance->featured) {

            $this->addCss('/plugins/abnmt/theater/assets/vendor/photoswipe/photoswipe.css');
            $this->addCss('/plugins/abnmt/theater/assets/vendor/photoswipe/default-skin/default-skin.css');
            $this->addJs('/plugins/abnmt/theater/assets/vendor/photoswipe/photoswipe.js');
            $this->addJs('/plugins/abnmt/theater/assets/vendor/photoswipe/photoswipe-ui-default.js');
            $this->addJs('/plugins/abnmt/theater/assets/js/performance-gallery.js');

            $performance->featured->each(function($image)
            {
                $image['sizes'] = getimagesize('./' . $image->getPath());
                if ($image['sizes'][0] < $image['sizes'][1])
                    $image['thumb'] = $image->getThumb(177, null);
                else
                    $image['thumb'] = $image->getThumb(null, 177);
            });
        }

        $performance->roles = $this->roles;
        $performance->participations_g = $this->participations_g;

        // print_r($this->roles);

        return $performance;
    }



    protected function listPerformances()
    {

        /*
         * Get all Performances with relations
         */
        $performances = TheaterPerformance::with(['playbill', 'repertoire', 'background', 'featured', 'video', 'participations', 'participations.person'])->isPublished()->get();

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
            if ($performance->type == 'normal' && $performance->state != 'archived'){

                if ($performance->slug == $this->property('slug')) {
                    $performance->active = 'active';
                    $this->active = 'normal';
                }
                return $performance;
            }
        });
        $child = $performances->filter(function($performance)
        {
            if ($performance->type == 'child' && $performance->state != 'archived'){
                if ($performance->slug == $this->property('slug')) {
                    $performance->active = 'active';
                    $this->active = 'child';
                }
                return $performance;
            }
        });
        $archive = $performances->filter(function($performance)
        {
            if ($performance->state == 'archived'){
                if ($performance->slug == $this->property('slug')) {
                    $performance->active = 'active';
                    $this->active = 'archived';
                }
                return $performance;
            }
        });

        // $section['menu'] = $items->sortBy('title');
        // $section['performances'] = $items->sortByDesc('premiere_date');
        // $section['menu'] = $items;
        // $section['performances'] = $items;


        $data = [
            'normal' => [
                'slug' => 'performances',
                'title' => 'Спектакли',
                'active' =>  ($this->active == 'normal') ? 'active' : '',
                'menu' => $normal->sortBy('title')->values(),
                'performances' => $normal->sortByDesc('premiere_date')->values(),
            ],
            'child' => [
                'slug' => 'child',
                'title' => 'Детские спектакли',
                'active' =>  ($this->active == 'child') ? 'active' : '',
                'menu' => $child->sortBy('title')->values(),
                'performances' => $child->sortByDesc('premiere_date')->values(),
            ],
            'archived' => [
                'slug' => 'archive',
                'title' => 'Архив',
                'active' =>  ($this->active == 'archived') ? 'active' : '',
                'menu' => $archive->sortBy('title')->values(),
                'performances' => $archive->sortByDesc('premiere_date')->values(),
            ]
        ];

        return $data;
    }



    // protected function preparePerformancesMenu()
    // {
    //     $slug = $this->property('slug');



    //     $normal_performance = TheaterPerformance::orderBy('title', 'asc')->isNormal()->get();
    //     $normal_performance->each(function($performance)
    //     {
    //         $performance->setUrl($this->performancePage, $this->controller);

    //         if ($performance->slug == $this->property('slug')) {
    //             $performance->active = 'active';
    //             $this->active = 'normal';
    //         }
    //     });

    //     $normal = array(
    //         'slug' => 'performances',
    //         'title' => 'Спектакли',
    //         'active' =>  ($this->active == 'normal') ? 'active' : '',
    //         'menu' => $normal_performance->sortBy('title'),
    //         'performances' => $normal_performance->sortBy('premiere_date')->reverse(),
    //     );



    //     $child_performance = TheaterPerformance::orderBy('title', 'asc')->isChild()->get();
    //     $child_performance->each(function($performance)
    //     {
    //         $performance->setUrl($this->performancePage, $this->controller);

    //         if ($performance->slug == $this->property('slug')) {
    //             $performance->active = 'active';
    //             $this->active = 'child';
    //         }
    //     });

    //     $child = array(
    //         'slug' => 'child',
    //         'title' => 'Детские спектакли',
    //         'active' =>  ($this->active == 'child') ? 'active' : '',
    //         'menu' => $child_performance->sortBy('title'),
    //         'performances' => $child_performance->sortBy('premiere_date')->reverse(),
    //     );



    //     $archive_performance = TheaterPerformance::orderBy('title', 'asc')->isArchive()->get();
    //     $archive_performance->each(function($performance)
    //     {
    //         $performance->setUrl($this->performancePage, $this->controller);

    //         if ($performance->slug == $this->property('slug')) {
    //             $performance->active = 'active';
    //             $this->active = 'archive';
    //         }
    //     });

    //     $archive = array(
    //         'slug' => 'archive',
    //         'title' => 'Архив',
    //         'active' =>  ($this->active == 'archive') ? 'active' : '',
    //         'menu' => $archive_performance->sortBy('title'),
    //         'performances' => $archive_performance->sortBy('premiere_date')->reverse(),
    //     );



    //     return $menu = array($normal, $child, $archive);
    // }
}