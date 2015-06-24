<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\PersonCategory as PersonCategoryModel;
use Abnmt\Theater\Models\Person as PersonModel;

use \Clockwork\Support\Laravel\Facade as CW;

class Troupe extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Труппа',
            'description' => 'Выводит список труппы'
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
    public $noPostsMessage = 'Людей нет.';

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $postPage = 'single/person';

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    public $categoryPage = 'pages/troupe';

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    // public $categoryFilter = '{{ :category }}';

    /**
     * If the post list should be ordered by another attribute.
     * @var string
     */
    public $sortOrder = 'family_name asc';


    /**
     * If the post list should be grouped by another attribute.
     * @var string
     */
    public $group = [
        'normal' => [],
        'grade'  => [],
    ];

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

        if (!$category = PersonCategoryModel::whereSlug($categoryId)->first())
            return null;

        return $category;
    }

    protected function listPosts()
    {
        $categories = $this->category ? $this->category->id : null;

        /*
         * List all the posts, eager load their categories
         */
        $posts = PersonModel::with(['categories', 'portrait'])->listFrontEnd([
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

            if ($post->portrait) {
                $image = $post->portrait;
                $image['thumb'] = $image->getThumb(250, 250, 'crop');
            }

            /*
             * Grouping
             */
            if (!is_null($post->grade))
                $this->group['grade'][] = $post;
            else
                $this->group['normal'][] = $post;

            $this->page['group'] = $this->group;
        });

        CW::info($posts);
        CW::info($this->group);

        return $posts;
    }
}