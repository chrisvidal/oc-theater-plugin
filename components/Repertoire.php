<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\PerformanceCategory as PerformanceCategoryModel;
use Abnmt\Theater\Models\Performance as PerformanceModel;


class Repertoire extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Репертуар',
            'description' => 'Выводит репертуар'
        ];
    }

    public function defineProperties()
    {
        return [];
    }


    /**
     * A collection of posts to display
     * @var Collection
     */
    public $posts;

    /**
     * If the post list should be filtered by a category, the model to use.
     * @var Model
     */
    public $category;

    /**
     * Message to display when there are no messages.
     * @var string
     */
    public $noPostsMessage = 'Спектаклей нет.';

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $postPage = 'single/performance';

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    public $categoryPage = 'pages/repertoire';

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    // public $categoryFilter = '{{ :category }}';

    /**
     * If the post list should be ordered by another attribute.
     * @var string
     */
    public $sortOrder = 'premiere_date desc';



    public function onRun()
    {
        $this->prepareVars();

        $this->category = $this->page['category'] = $this->loadCategory();
        $this->posts = $this->page['posts'] = $this->listPosts();

    }

    protected function prepareVars()
    {
        $this->categoryFilter = $this->param('category');
    }

    protected function loadCategory()
    {
        if (!$categoryId = $this->categoryFilter)
            return null;

        if (!$category = PerformanceCategoryModel::whereSlug($categoryId)->first())
            return null;

        return $category;
    }

    protected function listPosts()
    {
        $categories = $this->category ? $this->category->id : null;

        /*
         * List all the posts, eager load their categories
         */
        $posts = PerformanceModel::with('categories')->listFrontEnd([
            'sort'       => $this->sortOrder,
            'categories' => $categories,
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

        $this->page['test'] = "<pre>" . json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "</pre>\n";

        return $posts;
    }
}