<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

use Cms\Classes\Page;
use Cms\Classes\Content;

use Request;

// use Abnmt\Theater\Models\Event         as EventModel;
use Abnmt\Theater\Models\Article       as ArticleModel;
use Abnmt\Theater\Models\Performance   as PerformanceModel;
use Abnmt\Theater\Models\Person        as PersonModel;
// use Abnmt\Theater\Models\Taxonomy      as TaxonomyModel;
// use Abnmt\Theater\Models\Participation as ParticipationModel;

use \Clockwork\Support\Laravel\Facade as CW;

class Theater extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Theater Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'title'       => 'Тип',
                'description' => 'Выводит записи с выбранным типом',
                'type'        => 'dropdown',
                'default'     => 'person'
            ],
            'categories' => [
                'title'       => 'Категории',
                'description' => 'Выводит записи выбранных категории',
                'type'        => 'dropdown',
                'depends'     => ['type'],
            ],
            'sort' => [
                'title'       => 'Сортировка',
                'description' => 'Сортирует список записей',
                'type'        => 'dropdown',
                'depends'     => ['type'],
            ],
            'with' => [
                'title'       => 'Дополнительно',
                'description' => 'Загружает для записей дополнительные данные',
                'type'        => 'dropdown',
                'depends'     => ['type'],
            ],
            'performancePage' => [
                'title'       => 'Страница спектакля',
                'description' => 'Шаблон страницы спектакля',
                'type'        => 'dropdown',
                'default'     => 'theater/performance',
                'group'       => 'Страницы'
            ],
            'personPage' => [
                'title'       => 'Страница персоналии',
                'description' => 'Шаблон страницы персоналии',
                'type'        => 'dropdown',
                'default'     => 'theater/person',
                'group'       => 'Страницы'
            ],
            'articlePage' => [
                'title'       => 'Страница статьи',
                'description' => 'Шаблон страницы статьи',
                'type'        => 'dropdown',
                'default'     => 'theater/article',
                'group'       => 'Страницы'
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
    public function getArticlePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getTypeOptions()
    {
        $options = [
            'person'      => 'Люди',
            'performance' => 'Спектакли',
            'article'     => 'Статьи',
        ];
        return $options;
    }

    public function getCategoriesOptions()
    {
        $code = Request::input('type');
        $options = [
            'person' => [
                '{{ :category }}' => 'Из URL',
                'actor'           => 'Актёры',
                'cooperate'       => 'Приглашенные артисты',
                'administration'  => 'Администрация',
            ],
            'performance' => [
                '{{ :category }}' => 'Из URL',
                'normal'          => 'Спектакли',
                'child'           => 'Детские спектакли',
                'archived'        => 'Архивные спектакли',
            ],
            'article' => [
                '{{ :category }}' => 'Из URL',
                'news'            => 'Новости',
                'press'           => 'Пресса',
                'journal'         => 'Журнал',
            ],
            'event' => [
                '{{ :category }}' => 'Из URL',
                'performance'     => 'Спектакль',
                'concert'         => 'Концерт',
            ],
        ];
        return $options[$code];
    }

    public function getSortOptions()
    {
        $code = Request::input('type');
        $options = [
            'person'      => PersonModel::$allowedSortingOptions,
            'performance' => PerformanceModel::$allowedSortingOptions,
            'article'     => ArticleModel::$allowedSortingOptions,
            'event'       => EventModel::$allowedSortingOptions,
        ];
        return $options[$code];
    }

    public function getWithOptions()
    {
        $code = Request::input('type');
        $options = [
            'person' => [
                'relation, relation.taxonomy, participation, portrait, featured' => 'По умолчанию',
            ],
            'performance' => [
                'playbill' => 'По умолчанию',
            ],
            'article' => [
                'taxonomy' => 'По умолчанию',
            ],
            'event' => [
                'relation' => 'По умолчанию',
            ],
        ];
        return $options[$code];
    }

    /**
     * A Params object
     * @var array
     */
    public $params;

    /**
     * A Post object
     * @var Model
     */
    // public $post;

    /**
     * A Post slug
     * @var string
     */
    // public $slug;

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $postPage;

    /**
     * A collection of posts to display
     * @var Collection
     */
    public $posts;

    /**
     * If the post list should be filtered by a category, the model to use.
     * @var Model
     */
    // public $category;


    /**
     *  onRun function
     */
    public function onRun()
    {
        $this->prepareVars();

        $this->posts = $this->page['posts'] = $this->listPosts();

        // if ($this->slug = $this->param('slug'))
        //     $this->post = $this->page['post'] = $this->loadPost();

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

        extract($this->params);

        $model = "Abnmt\\Theater\\Models\\" . ucfirst($type);

        /*
         * List all the posts
         */
        $posts = $model::isPublished();

        if (isset($with)) {
            if (!is_array($with)) $with = array_map('trim', explode(',', $with));
            $posts = $posts->with($with);
        }

        if (isset($categories)) {
            if (!is_array($categories)) $categories = array_map('trim', explode(',', $categories));
            $posts = $posts->whereHas('taxonomy', function($q) use ($categories) {
                foreach ($categories as $key => $category) {
                    if ($key == 0)
                        $q->where('slug', '=', $category);
                    else
                        $q->orWhere('slug', '=', $category);
                }
            });
        }

        if (isset($sort)) {
            if (!is_array($sort)) $sort = array_map('trim', explode(',', $sort));
            foreach ($sort as $_sort) {
                list($sortField, $sortDirection) = explode(' ', $_sort);
                $posts = $posts->orderBy($sortField, $sortDirection);
            }
        }

        $posts = $posts->get();

        $page = $type . 'Page';
        $this->postPage = $$page;

        $posts->each(function($post) {
            $post->setUrl($this->postPage, $this->controller);
        });

        CW::info($posts);
        CW::info($this->postPage);
        CW::info($page);
        return $posts;
    }

}