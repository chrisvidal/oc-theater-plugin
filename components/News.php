<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\News as NewsModel;

class News extends ComponentBase
{
    /**
     * A collection of posts to display
     * @var Collection
     */
    public $posts;

    /**
     * Parameter to use for the page number
     * @var string
     */
    public $pageParam;

    /**
     * If the post list should be filtered by a category, the model to use.
     * @var Model
     */
    public $category;

    /**
     * Message to display when there are no messages.
     * @var string
     */
    public $noPostsMessage;

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $postPage;

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    public $categoryPage;

    /**
     * If the post list should be ordered by another attribute.
     * @var string
     */
    public $sortOrder;



    public function componentDetails()
    {
        return [
            'name'        => 'Новости',
            'description' => 'Выводит список новостей'
        ];
    }

    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'title'       => '№ страницы',
                'description' => 'Переменная с номером страницы',
                'type'        => 'string',
                'default'     => '{{ :page }}',
            ],
            'categoryFilter' => [
                'title'       => 'Фильтр категории',
                'description' => 'Переменная и фильтр категории',
                'type'        => 'string',
                'default'     => ''
            ],
            'postsPerPage' => [
                'title'             => 'Количество записей на страницу',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Должно быть целым числом',
                'default'           => '10',
            ],
            'noPostsMessage' => [
                'title'        => 'Сообщение об отсутствие записей',
                'description'  => 'Строка с сообщением об отсутствии записей',
                'type'         => 'string',
                'default'      => 'Новостей нет',
                'showExternalParam' => false
            ],
            'sortOrder' => [
                'title'       => 'Порядок сортировки',
                'description' => 'Выберите порядок сортировки постов',
                'type'        => 'dropdown',
                'default'     => 'published_at desc'
            ],
            'categoryPage' => [
                'title'       => 'Страница для рубрик',
                'description' => 'Шаблон для вывода списка записей определенной рубрики',
                'type'        => 'dropdown',
                'default'     => 'blog/category',
                'group'       => 'Links',
            ],
            'postPage' => [
                'title'       => 'Страница для постов',
                'description' => 'Шаблон для вывода одной записи',
                'type'        => 'dropdown',
                'default'     => 'blog/post',
                'group'       => 'Links',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getPostPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSortOrderOptions()
    {
        return NewsModel::$allowedSortingOptions;
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->category = $this->page['category'] = $this->loadCategory();
        $this->posts = $this->page['posts'] = $this->listPosts();

        /*
         * If the page number is not valid, redirect
         */
        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->posts->lastPage()) && $currentPage > 1)
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
        }
    }

    protected function prepareVars()
    {
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->noPostsMessage = $this->page['noPostsMessage'] = $this->property('noPostsMessage');

        /*
         * Page links
         */
        $this->postPage = $this->page['postPage'] = $this->property('postPage');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
    }

    protected function listPosts()
    {
        $categories = $this->category ? $this->category->id : null;

        /*
         * List all the posts, eager load their categories
         */
        $posts = NewsModel::with('categories')->listFrontEnd([
            'page'       => $this->property('pageNumber'),
            'sort'       => $this->property('sortOrder'),
            'perPage'    => $this->property('postsPerPage'),
            'categories' => $categories
        ]);

        /*
         * Add a "url" helper attribute for linking to each post and category
         */
        $posts->each(function($post){
            $post->setUrl($this->postPage, $this->controller);

            $post->categories->each(function($category){
                $category->setUrl($this->categoryPage, $this->controller);
            });
        });

        return $posts;
    }

    protected function loadCategory()
    {
        if (!$categoryId = $this->property('categoryFilter'))
            return null;

        if (!$category = BlogCategory::whereSlug($categoryId)->first())
            return null;

        return $category;
    }
}
