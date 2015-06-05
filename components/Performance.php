<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Performance as PerformanceModel;

use \Clockwork\Support\Laravel\Facade as CW;
use Laravelrus\LocalizedCarbon\LocalizedCarbon as Carbon;

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
    public $personPage = 'single/person';

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $pressPage = 'single/press';


    public function componentDetails()
    {
        return [
            'name'        => 'Спектакль',
            'description' => 'Выводит одиночный спектакль'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->prepareVars();

        if ($this->slug = $this->param('slug'))
            $this->post = $this->page['post'] = $this->loadPost();

        // $this->page['roles'] = $this->roles;

    }

    protected function prepareVars()
    {
        // $this->categoryFilter = $this->param('category');
    }

    protected function loadPost()
    {
        $post = PerformanceModel::isPublished()
            ->with(['background', 'featured', 'video', 'participation.person', 'press'])
            ->with(
                ['events' => function($q) {
                    $q->where('datetime', '>=', Carbon::now())->take(2);
                }]
            )
            ->whereSlug($this->slug)
            ->first()
        ;

        /*
         * Add a "URL" helper attribute for linking to each performance and person relation
         */

        $this->roles = [];
        $this->participation = [];

        $post->participation->each(function($role)
        {
            $role->person->setUrl($this->personPage, $this->controller);

            if ($role['group'] != NULL)
                $this->roles[$role['group']][] = $role;

            $this->participation[$role['title']]['title'] = $role->title;
            $this->participation[$role['title']]['type'] = $role->type;
            $this->participation[$role['title']]['description'] = $role->description;
            $this->participation[$role['title']]['group'] = $role->group;
            $this->participation[$role['title']]['persons'][] = $role->person;
        });

        $post->press = $post->press->sortByDesc('source_date');

        $post->press->each(function($press){
            $press->setUrl($this->pressPage, $this->controller);
        });


        if ($post->featured) {

            $this->addCss('/plugins/abnmt/theater/assets/vendor/photoswipe/photoswipe.css');
            $this->addCss('/plugins/abnmt/theater/assets/vendor/photoswipe/default-skin/default-skin.css');
            $this->addJs('/plugins/abnmt/theater/assets/vendor/photoswipe/photoswipe.js');
            $this->addJs('/plugins/abnmt/theater/assets/vendor/photoswipe/photoswipe-ui-default.js');
            $this->addJs('/plugins/abnmt/theater/assets/js/performance-gallery.js');

            $post->featured->each(function($image)
            {
                $image['sizes'] = getimagesize('./' . $image->getPath());
                if ($image['sizes'][0] < $image['sizes'][1])
                    $image['thumb'] = $image->getThumb(177, null);
                else
                    $image['thumb'] = $image->getThumb(null, 177);
            });
        }

        $post->roles = $this->roles;
        $post->roles_ng = $this->participation;

        CW::info($post);

        return $post;
    }

    // protected function loadEvents($post)
    // {
    //     $post->events();
    // }
}