<?php namespace Abnmt\Theater\Components;

use Abnmt\Theater\Models\Event as EventModel;
use Abnmt\Theater\Models\Performance as PerformanceModel;
use Carbon;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use \Clockwork\Support\Laravel\Facade as CW;

class Events extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Афиша',
            'description' => 'Компонент для вывода Афиши',
        ];
    }

    public function defineProperties()
    {
        return [
            'scopes'          => [
                'title'       => 'Наборы',
                'description' => 'Загружает для записей дополнительные наборы данных',
                'type'        => 'dropdown',
            ],
            'sort'            => [
                'title'       => 'Сортировка',
                'description' => 'Сортирует список записей',
                'type'        => 'dropdown',
            ],
            'date'            => [
                'title'       => 'Дата',
                'description' => 'Переменная с датой',
                'default'     => '{{ :date }}',
            ],
            'performancePage' => [
                'title'       => 'Страница спектакля',
                'description' => 'Шаблон страницы спектакля',
                'type'        => 'dropdown',
                'default'     => 'theater/performance',
                'group'       => 'Страницы',
            ],
            'newsPage'        => [
                'title'       => 'Страница статьи прессы',
                'description' => 'Шаблон страницы статьи',
                'type'        => 'dropdown',
                'default'     => 'theater/news',
                'group'       => 'Страницы',
            ],
        ];
    }

    public function getPerformancePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
    public function getNewsPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSortOptions()
    {
        return EventModel::$allowedSortingOptions;
    }

    public function getScopesOptions()
    {
        return EventModel::$allowedScopingOptions;
    }

    /**
     * A Params object
     * @var array
     */
    public $params;

    /**
     * A collection of posts to display
     * @var Collection
     */
    public $posts;

    /**
     * Active menu code
     * @var Collection
     */
    // public $activeMenuItem = 'playbill';

    /**
     * A collection of grouped posts to display
     * @var Collection
     */
    public $group = [
        'normal' => [],
        'child'  => [],
    ];

    /**
     *  onInit function
     */
    // public function onInit()
    // {
    //     $this['activeMenuItem'] = 'blog';
    // }

    /**
     *  onRun function
     */
    public function onRun()
    {
        $this->prepareVars();

        $this->posts         = $this->page['posts']         = $this->listPosts();
        $this->page['group'] = $this->group;
    }

    /**
     *  Prepare vars
     */
    protected function prepareVars()
    {
        $this->params = $this->getProperties();

        CW::info($this->params);
    }

    protected function listPosts()
    {

        $params = $this->params;

        /*
         * List all posts
         */
        $posts = EventModel::listFrontEnd($params);

        /*
         * Prepare for View
         */
        $active = Carbon::now();
        $posts->each(function ($post) use ($params, &$active) {

            // Assign URLs
            extract($params);
            // if ($post->relation instanceof ArticleModel)
            //     $post->relation->setUrl($newsPage, $this->controller);
            if ($post->relation instanceof PerformanceModel) {
                $post->relation->setUrl($performancePage, $this->controller);
            }

            $date = Carbon::parse($post->event_date);
            if ($active != 'active' && $date->gte($active)) {
                $post->active = $active = 'active';
            }

            // Grouping
            if ($this->inCollection($post->relation->taxonomy, 'title', 'Детские спектакли')) {
                $this->group['child'][] = $post;
            } else {
                $this->group['normal'][] = $post;
            }

        });

        CW::info($this->group);
        CW::info($posts);
        return $posts;
    }

    /*
     * Utils
     */
    protected static function inCollection($array, $key, $val)
    {
        foreach ($array as $item) {
            if (is_array($item)) {
                self::inCollection($item, $key, $val);
            }

            if (isset($item[$key]) && $item[$key] == $val) {
                return true;
            }

        }
        return false;
    }

}
