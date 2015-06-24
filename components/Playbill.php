<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

use Abnmt\Theater\Models\Playbill as PlaybillModel;
use Abnmt\Theater\Models\Event as EventModel;

use \Clockwork\Support\Laravel\Facade as CW;

setlocale(LC_ALL, 'Russian');
use Laravelrus\LocalizedCarbon\LocalizedCarbon as Carbon;

class Playbill extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Афиша',
            'description' => 'Выводит афишу'
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
    public $categoryPage = 'pages/playbill';

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    // public $categoryFilter = '{{ :category }}';

    /**
     * If the post list should be ordered by another attribute.
     * @var string
     */
    public $sortOrder = 'datetime asc';

    /**
     * If the post list should be grouped by another attribute.
     * @var string
     */
    public $group = [
        'normal' => [],
        'child'  => [],
    ];



    public function onRun()
    {
        $this->prepareVars();

        $this->category = $this->page['category'] = $this->loadCategory();
        $this->posts    = $this->page['posts']    = $this->listPosts();

    }

    protected function prepareVars()
    {
        $this->categoryFilter = $this->param('category');
    }

    protected function loadCategory()
    {
        if (!$categoryId = $this->categoryFilter)
            return null;

        if ($categoryId == 'now')
            $categoryId = Carbon::now()->startOfMonth()->format('Y-m-d');
        else
            $categoryId = Carbon::parse($categoryId)->startOfMonth()->format('Y-m-d');

        if (!$category = PlaybillModel::where('date', '=', $categoryId)->first())
            return null;

        return $category;
    }

    protected function listPosts()
    {
        $categories = $this->category ? $this->category->id : null;

        /*
         * List all the posts, eager load their categories
         */
        $posts = EventModel::with(['performance.categories'])->listFrontEnd([
            'sort'       => $this->sortOrder,
            'categories' => $categories,
        ]);

        /*
         * Add a "url" helper attribute for linking to each post and category and Grouping
         */
        $posts->each(function($post){
            $post->setUrl($this->postPage, $this->controller);

            $post->performance->setUrl($this->postPage, $this->controller);

            $post->categories->each(function($category) use ($post) {
                $category->setUrl($this->categoryPage, $this->controller);
            });

            $post->performance->categories->each(function($category) use ($post) {
                $category->setUrl($this->categoryPage, $this->controller);

                /*
                 * Grouping
                 */
                if ($category->slug == 'child' && $category->slug != 'premiere')
                    $this->group['child'][] = $post;
                elseif ($category->slug != 'premiere')
                    $this->group['normal'][] = $post;

                $this->page['group'] = $this->group;

            });


        });

        // CW::info($this->group);
        CW::info($posts);

        // $this->page['test'] = "<pre>" . json_encode($this->group, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "</pre>\n";

        return $posts;
    }
}