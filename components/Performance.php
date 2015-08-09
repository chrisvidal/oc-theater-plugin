<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Performance as PerformanceModel;

use \Clockwork\Support\Laravel\Facade as CW;
// use Laravelrus\LocalizedCarbon\LocalizedCarbon as Carbon;
use \Carbon\Carbon as Carbon;

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
    public $relationPage = 'theater/relation';


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
            ->with(['background', 'background_mobile', 'background_flat', 'background_mask', 'featured', 'video', 'participation.person', 'relation'])
            ->with(
                ['events' => function($q) {
                    $q->where('event_date', '>=', Carbon::now())->take(2);
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

            if ($role['group'] != NULL) {
                $this->roles[$role['group']][] = $role;
                return;
            }

            $this->participation[$role['title']]['title'] = $role->title;
            $this->participation[$role['title']]['type'] = $role->type;
            $this->participation[$role['title']]['description'] = $role->description;
            $this->participation[$role['title']]['group'] = $role->group;
            $this->participation[$role['title']]['persons'][] = $role->person;
        });

        // $post->relation = $post->relation->sortByDesc('source_date');

        $post->relation->each(function($relation){
            $relation->setUrl($this->relationPage, $this->controller);
        });


        $post->background->each(function($image){
            $image['sizes'] = getimagesize('./' . $image->getPath());

            preg_match('/.+?_(\d+)\.png/', $image->file_name, $matches);

            $width = $matches[1];
            $height = round($width/$image->sizes[0]*$image->sizes[1]);

            $image['dest'] = $image->getThumb($width, $height, ['extension' => 'png']);

            // $flat = $this->post->background_flat[$pos];
            // $mask = $this->post->background_masq[$pos];

            // $image['dest_flat'] = $flat->getThumb($width, $height);
            // $image['dest_mask'] = $mask->getThumb($width, $height);

            $image['dest_sizes'] = getimagesize('./' . $image->dest);

            // $pos++;
        });
        $post->background_flat->each(function($image){
            $image['sizes'] = getimagesize('./' . $image->getPath());

            preg_match('/.+?_(\d+)_flat\.jpg/', $image->file_name, $matches);

            $width = $matches[1];
            $height = round($width/$image->sizes[0]*$image->sizes[1]);

            $image['dest'] = $image->getThumb($width, $height);
            $image['dest_sizes'] = getimagesize('./' . $image->dest);
        });
        $post->background_mask->each(function($image){
            $image['sizes'] = getimagesize('./' . $image->getPath());

            preg_match('/.+?_(\d+)_mask\.jpg/', $image->file_name, $matches);

            $width = $matches[1];
            $height = round($width/$image->sizes[0]*$image->sizes[1]);

            $image['dest'] = $image->getThumb($width, $height);
            $image['dest_sizes'] = getimagesize('./' . $image->dest);
        });

        if ( $bg_min = $post->background_mobile ) {
            $bg_min['dest'] = $bg_min->getThumb(960, null);
            $bg_min['dest_sizes'] = getimagesize('./' . $bg_min->dest);

            $post->background_mobile = $bg_min;
        }


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
                // $image['thumb'] = $image->getThumb(177, 177, 'portrait');
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