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
    public $pressPage = 'theaterPress/article';


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
            ->Performance()
            ->whereSlug($this->slug)
            ->first()
        ;

        /*
         * Add a "URL" helper attribute for linking to each performance and person press
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

        $post->press->each(function($press){
            $press->setUrl($this->pressPage, $this->controller);
        });

        // ROLES
        $post->roles = $this->roles;
        $post->roles_ng = $this->participation;


        // BACKGROUNDS

        $key = 0;
        $bg = $post->background;
        $bg_data = collect($post->meta['backgrounds']);

        // $default = $this->$bg_default;
        $data = [];

        $bg->each(function($image) use (&$key, $bg_data, &$data){
            $image['sizes'] = $sizes = getimagesize('./' . $image->getPath());

            // Width from filename
            preg_match('/.+?_(\d+)\.png/', $image->file_name, $matches);
            $width = $matches[1];
            $height = round($width/$image->sizes[0]*$image->sizes[1]);

            // Read params
            $params = $bg_data->where('key', strval($key))->all();

            // Set dest array
            $rules = [];

            // Clean temp var
            $this->rule_temp = [];

            // Process image params for every rules (default turn)
            foreach ($this->rules as $query => $columns) {
                $rules['q' . $query] = [];
                foreach ($columns as $column => $rule) {
                    $rules['q' . $query][$column] = $this->processRule($query, $column, $width, $height, $rule, $params, $key);
                }
            }

            CW::info(['Rules' => $rules]);

            $image['width']  = $width;
            $image['height'] = $height;

            $data = array_merge_recursive($data, $rules);
            // $data += $rules;
            // $image['class'] = join(' ', $class_names);

            $key++;
        });


        $post->bg_query = collect($data)->groupBy('query');
        $post->bg_data = $data;

        CW::info(['Data' => $data]);
        CW::info(['Performance' => $post]);

        return $post;
    }

    protected function processRule($query, $column, $width, $height, $rule, $params, $key)
    {

        $return = [];


        foreach ($params as $id => $param) {

            // CW::info(['param' => $param]);
            // return;

            // Clean param
            $param = array_filter($param);

            if ( array_key_exists('query', $param) && $query != $param['query'])
                continue;

            if (in_array($param['class'], ['rt', 'rm', 'rb']) & $column != 'right')
                continue;
            if (in_array($param['class'], ['lt', 'lm', 'lb']) & $column != 'left')
                continue;
            if (in_array($param['class'], ['ls']) & $column != 'side')
                continue;

            if (array_key_exists('width', $this->rule_temp))
                $width = $this->rule_temp['width'];
            else
                $rule_temp['width'] = $width;


            if ($rule == 'none') {
                $return[] = [
                    'class' => 'bg-' . ($key+1),
                    'display' => 'none',
                ];
                continue;
            }


            if (array_key_exists('delta', $this->rule_temp))
                $delta = $this->rule_temp['delta'];
            else
                $delta = $this->rule_temp['delta'] = $width / $rule['width'];

            if ( array_key_exists('width', $param) ) {
                if ( !(array_key_exists('param_id', $this->rule_temp) && $this->rule_temp['param_id'] == $id) ) {

                    if ($param['width'] > $width)
                        $this->rule_temp['width'] = $param['width'];

                    $delta = $this->rule_temp['delta'] = $param['width'] / $rule['width'];
                    $this->rule_temp['param_id'] = $id;
                }
            }

            $padding = round($query * $rule['percent'] - $rule['width'] * $delta);

            // if ( $column == 'left' && in_array($query, ['1622'])) {
            //     // CW::info([$width, $rule['width']]);
            //     $padding = round($rule['width'] * $delta);
            // }


            $return[] =  [
                'class' => 'bg-' . ($key+1),
                'padding' => $padding,
                'width' => round($rule['width'] * $delta),
                'delta' => $delta,
            ];

        }

        return array_filter($return);

        // $return['width'] = $width; // width for thumb
        // $return['delta'] = $width / $rule['width'];
    }

    protected $rule_temp = [];

    protected $rules = [
        '1920' => [
            'right' => [
                'width'   => 768,
                'padding' => 192,
                'percent' => 0.5,
                'delta'   => 'width(file) / width(768)',
                'padding' => '1920 * 50% - width(768) * delta',
            ],
            'left' => [
                'width'   => 592,
                'padding' => 368,
                'percent' => 0.5,
                'delta'   => 'width(file) / width(592)',
                'padding' => '1920 * 50% - width(592) * delta',
            ],
            'side' => [
                'width'   => 304,
                'padding' => 656,
                'percent' => 0.5,
                'delta'   => 'width(file) / width(592)',
                'padding' => '1920 * 50% - width(592) * delta',
            ],
        ],
        '1700' => [
            'side' => 'none',
        ],
        '1622' => [
            'right' => [
                'width'   => 619,
                'padding' => 1003,
                'percent' => 1,
                'delta'   => 'width(param) / width(619)',
                'padding' => '1622 * 100% - width(619) * delta',
            ],
            'left' => [
                'width'   => 443,
                'percent' => 0,
                'delta'   => 'width(param) / width(443)',
                // 'width'   => 'width(443) * delta',
            ],
        ],
        '1440' => [
            'right' => [
                'width'   => 725,
                'padding' => 715,
                'percent' => 1,
                'delta'   => 'width(param) / width(725)',
                'padding' => '1440 * 100% - width(725) * delta',
            ],
            'left' => 'none',
        ],
        '1360' => [
            'right' => [
                'width'   => 690,
                'padding' => 670,
                'percent' => 1,
                'delta'   => 'width(param) / width(690)',
                'padding' => '1360 * 100% - width(690) * delta',
            ],
        ],
        '1280' => [
            'right' => [
                'width'   => 610,
                'padding' => 670,
                'percent' => 1,
                'delta'   => 'width(param) / width(610)',
                'padding' => '1280 * 100% - width(610) * delta',
            ],
        ],
    ];


}