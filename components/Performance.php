<?php namespace Abnmt\Theater\Components;

use Abnmt\Theater\Models\Performance as PerformanceModel;
use Cms\Classes\ComponentBase;
// use Laravelrus\LocalizedCarbon\LocalizedCarbon as Carbon;
use \Clockwork\Support\Laravel\Facade as CW;

class Performance extends ComponentBase
{

    /**
     * A Post object
     * @var Model
     */
    public $post;

    /**
     * A Post slug
     * @var string
     */
    public $slug;

    /**
     * A Roles in Performance
     * @var string
     */
    public $roles;

    /**
     * A Participation
     * @var string
     */
    public $participation;

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $personPage = 'theater/person';

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $pressPage = 'theaterPress/article';

    public function componentDetails()
    {
        return [
            'name'        => 'Спектакль',
            'description' => 'Выводит одиночный спектакль',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->prepareVars();

        if ($this->slug = $this->param('slug')) {
            $this->post = $this->page['post'] = $this->loadPost();
        }

        // $this->page['roles'] = $this->roles;

    }

    protected function prepareVars()
    {
        // $this->categoryFilter = $this->param('category');
    }

    protected function loadPost()
    {
        $post = PerformanceModel::isPublished()
            ->Performance()
            ->whereSlug($this->slug)
            ->first()
        ;

        /*
         * Add a "URL" helper attribute for linking to each performance and person press
         */

        $this->roles         = [];
        $this->participation = [];

        $post->participation->each(function ($role) {
            $role->person->setUrl($this->personPage, $this->controller);

            if ($role['group'] != null) {
                $this->roles[$role['group']][] = $role;
                return;
            }

            $this->participation[$role['title']]['title']       = $role->title;
            $this->participation[$role['title']]['type']        = $role->type;
            $this->participation[$role['title']]['description'] = $role->description;
            $this->participation[$role['title']]['group']       = $role->group;
            $this->participation[$role['title']]['persons'][]   = $role->person;
        });

        $post->press->each(function ($press) {
            $press->setUrl($this->pressPage, $this->controller);
        });

        // ROLES
        $post->roles    = $this->roles;
        $post->roles_ng = $this->participation;

        CW::info(['Performance' => $post]);

        return $post;
    }

}
