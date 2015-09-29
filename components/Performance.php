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

    protected $rules = [
        '1920' => [
            'right' => [
                'width'            => 768,
                'padding'          => 192,
                'percent'          => 0.5,
                'padding'          => 'padding-left',
                'margin'           => 'margin-left',
                'container_styles' => [
                    'left'         => '50%',
                    'right'        => '0',
                    'margin-left'  => '192px',
                    'margin-right' => '0',
                    'margin-top'   => '64px',
                    'width'        => 'auto',
                ],
                'rt'               => [
                    'background-position' => 'right top',
                ],
                'rm'               => [
                    'background-position' => 'right center',
                ],
                'rb'               => [
                    'background-position' => 'right bottom',
                ],
            ],
            'left'  => [
                'width'            => 592,
                'padding'          => 368,
                'percent'          => 0.5,
                'padding'          => 'padding-right',
                'margin'           => 'margin-right',
                'container_styles' => [
                    'left'         => '0',
                    'right'        => '50%',
                    'margin-right' => '368px',
                    'margin-left'  => '0',
                    'margin-top'   => '475px',
                    'width'        => 'auto',
                ],
                'lt'               => [
                    'background-position' => 'left top',
                ],
                'lm'               => [
                    'background-position' => 'left bottom',
                ],
                'lb'               => [
                    'background-position' => 'left bottom',
                ],
            ],
            'side'  => [
                'width'            => 304,
                'padding'          => 656,
                'percent'          => 0.5,
                'padding'          => 'padding-right',
                'margin'           => 'margin-right',
                'container_styles' => [
                    'top'                 => '0',
                    'left'                => '0',
                    'right'               => '50%',
                    'background-position' => 'bottom',
                    'height'              => '475px',
                    'margin-right'        => '656px',
                    'width'               => 'auto',
                ],
                'ls'               => [
                    'background-position' => 'left top',
                    'width'               => '100%',
                    'height'              => '100%',
                ],
            ],

        ],
        '1700' => [
            'side' => 'none',
        ],
        '1622' => [
            'right' => [
                'width'            => 619,
                'padding'          => 1003,
                'percent'          => 1,
                'padding'          => 'padding-left',
                'margin'           => 'margin-left',
                'container_styles' => [
                    'left'         => '0',
                    'margin-left'  => '1003px',
                    'margin-right' => '0',
                    'width'        => 'auto',
                ],
            ],
            'left'  => [
                'width'            => 443,
                'percent'          => 0,
                'padding'          => 'none',
                'margin'           => 'none',
                'container_styles' => [
                    'left'         => '0',
                    'right'        => 'auto',
                    'margin-right' => '0',
                    'margin-right' => '0',
                    'width'        => '443px', // ???
                ],
            ],
        ],
        '1440' => [
            'right' => [
                'width'            => 725,
                'padding'          => 715,
                'percent'          => 1,
                'padding'          => 'padding-left',
                'margin'           => 'margin-left',
                'container_styles' => [
                    'margin-left'  => '715px',
                    'margin-right' => '0',
                    'width'        => 'auto',
                ],
            ],
            'left'  => 'none',
        ],
        '1360' => [
            'right' => [
                'width'            => 690,
                'padding'          => 670,
                'percent'          => 1,
                'padding'          => 'padding-left',
                'margin'           => 'margin-left',
                'container_styles' => [
                    'margin-left'  => '670px',
                    'margin-right' => '0',
                    'width'        => 'auto',
                ],
                'item_styles'      => [
                    'margin-right' => '0',
                ],
                'rt'               => [
                    'background-position' => 'right top',
                ],
                'rm'               => [
                    'background-position' => 'right center',
                ],
                'rb'               => [
                    'background-position' => 'right bottom',
                ],
            ],
        ],
        '1280' => [
            'right' => [
                'width'            => 610,
                'padding'          => 670,
                'percent'          => 1,
                'padding'          => 'padding-left',
                'margin'           => 'margin-left',
                'container_styles' => [
                    'margin-left'  => '670px',
                    'margin-right' => '0',
                    'width'        => 'auto',
                ],
            ],
        ],
    ];

}
