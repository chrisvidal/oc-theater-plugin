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


            $class_names = [];

            $bg_data = $bg_data->where('key', strval($key))->all();

            foreach ($bg_data as $item) {

                // CW::info(['item' => $item]);
                $class = $item['class'];

                $column = ( in_array($class, ['rt', 'rm', 'rb']) ) ? 'right' : 'left';


                if ( !array_key_exists('params', $item) ) {

                    $query = 'all';
                    $params = array_merge( $this->bg_default[$query][$class], ['width' => $width]);

                } else {

                    $query = (array_key_exists('query', $item['params'])) ? $item['params']['query'] : 'all';

                    $_params = [];

                    foreach ($item['params'] as $entry) {
                        $_params[$entry['param']] = $entry['value'];
                    }

                    $params = array_merge( $this->bg_default[$query][$class], ['width' => $width], $_params);

                }

                // Padding
                if ( array_key_exists('left', $params) )
                    $pos = $params['left'];
                if ( array_key_exists('right', $params) )
                    $pos = $params['right'];

                if ($pos) {

                    $container = $query * ( intval($pos, 10)/100 );
                    $padding = $container - $params['width'];

                    if ( array_key_exists('padding-left', $params) )
                        $params['padding-left'] = $padding;
                    if ( array_key_exists('padding-right', $params) )
                        $params['padding-right'] = $padding;
                }

                // Align
                if ( $params['width'] < $this->bg_default[$query][$class]['width'] )
                    $params['text-align'] = 'center';

                if ( $query == 'all' ) {
                }


                CW::info(['params' => $params]);


                // $height = round($width/$image['sizes'][0]*$image['sizes'][1]);
                // $item['width']  = $image['width']  = $width;
                // $item['height'] = $image['height'] = $height;

                $class_names[] = $item['class'];
                $data[] = $item;
            }

            $image['width']  = $width;
            $image['height'] = $height;
            $image['class'] = join(' ', $class_names);

            $key++;
        });


        $post->bg_query = collect($data)->groupBy('query');
        $post->bg_data = $data;

        CW::info(['Performance' => $post]);

        return $post;
    }

    // protected function processBgs($params, $backgrounds)
    // {
    //     $return = [];

    //     foreach ($params as $param) {
    //         $_param = explode(' ', $param['position']);

    //         $bg = $param['bg']-1;

    //         $background = $backgrounds->slice($bg,1)->first();
    //         if (is_null($background)) {
    //             CW::info('Background 5 is null');
    //             // $positions = [
    //             //     'hidden' = 'true';
    //             // ];
    //             continue;
    //         }

    //         $sizes = $background['sizes'];

    //         if ( count($_param) == 1 && $_param[0] == 'hidden' ) {
    //             $positions = [
    //                 'hidden' => 'true',
    //             ];
    //         } else {
    //             $class = [
    //                 'column' => (count($_param) > 1) ? $_param[1] : null,
    //                 'valign' => (count($_param) > 2) ? $_param[2] : null,
    //                 'halign' => (count($_param) > 3) ? $_param[3] : null,
    //             ];
    //             $positions = [
    //                 'bg' => $bg,
    //                 'orig_width' => $sizes[0],
    //                 'orig_height' => $sizes[1],
    //                 'width'  => (count($_param) > 0) ? intval($_param[0], 10) : null,
    //                 'height' => (count($_param) > 0) ? round($_param[0]/$sizes[0]*$sizes[1]) : null,
    //                 'class' => join(' ', $class),
    //                 'inc' => (count($_param) > 4) ? $_param[4] : null,
    //                 // 'column' => (count($_param) > 1) ? $_param[1] : null,
    //                 // 'valign' => (count($_param) > 2) ? $_param[2] : null,
    //                 // 'halign' => (count($_param) > 3) ? $_param[3] : null,
    //             ];
    //             $positions += $class;
    //         }

    //         // CW::info(['Param' => $positions]);
    //         $return[] = $positions;

    //     }

    //     $collection = collect($return);
    //     $columns = $collection->groupBy('column')->toArray();

    //     $return = [];

    //     if (array_key_exists('right', $columns)) {
    //         $right = $columns['right'];

    //         $right_height = $this->defineHeight($right, 'right');

    //         // CW::info(['Right' => $right_height]);

    //         foreach ($right_height as $key => $item) {
    //             $return[] = $item;
    //         }
    //     }

    //     if (array_key_exists('left', $columns)) {
    //         $left  = array_reverse($columns['left']);

    //         $left_height = $this->defineHeight($left, 'left');

    //         // CW::info(['Left' => $left_height]);

    //         foreach ($left_height as $key => $item) {
    //             $return[] = $item;
    //         }
    //     }

    //     if (array_key_exists('side', $columns)) {
    //         $side  = $columns['side'];

    //         foreach ($side as $key => $item) {
    //             $item += [
    //                 'height_perc' => '475px',
    //             ];
    //             $return[] = $item;
    //         }
    //     }


    //     // CW::info(['Cols' => $return]);

    //     return $return;
    // }

    // protected function defineHeight($list, $column)
    // {

    //     CW::info(['List' => $list]);

    //     $summ = [0];
    //     if ($column == 'left' & $list[0]['width'] == '592')
    //         $summ = [635];
    //     if ($column == 'left' & $list[0]['width'] == '443')
    //         $summ = [475];

    //     foreach ($list as $pos => $bg) {
    //         $summ[$pos+1] = $summ[$pos] + $bg['height'];
    //         // if (array_key_exists('inc', $bg))
    //         //     $summ[$pos+1] += $bg['inc'];
    //     }

    //     foreach ($list as $pos => &$bg) {
    //         $bg += [
    //             'top'    => $summ[$pos],
    //             'bottom' => end($summ) - $bg['height'] - $summ[$pos],
    //             'top_perc'    => $summ[$pos]/end($summ)*100 . '%',
    //             'height_perc' => $bg['height']/end($summ)*100 . '%',
    //             'bottom_perc' => (end($summ) - $bg['height'] - $summ[$pos])/end($summ)*100 . '%',
    //             'summ' => $summ,
    //         ];
    //     }

    //     return $list;
    // }

    public $bg_default = [
        'all' => [
            'rt' => [
                'left'           => '50%',
                'padding-left'   => '192',
                'text-align'     => 'right',
                'vertical-align' => 'top',
                'width'          => '768',
            ],
            'rm' => [
                'left'           => '50%',
                'padding-left'   => '192',
                'text-align'     => 'right',
                'vertical-align' => 'middle',
                'width'          => '768',
            ],
            'rb' => [
                'left'           => '50%',
                'padding-left'   => '192',
                'text-align'     => 'right',
                'vertical-align' => 'bottom',
                'width'          => '768',
            ],
            'ls' => [
                'right'          => '50%',
                'padding-right'  => '656',
                'text-align'     => 'left',
                'vertical-align' => 'bottom',
                'width'          => '304',
                'height'         => '475',
            ],
            'lm' => [
                'right'          => '50%',
                'margin-top'     => '475',
                'padding-right'  => '368',
                'text-align'     => 'left',
                'vertical-align' => 'middle',
                'width'          => '592',
            ],
            'lb' => [
                'right'          => '50%',
                'margin-top'     => '475',
                'padding-right'  => '368',
                'text-align'     => 'left',
                'vertical-align' => 'bottom',
                'width'          => '592',
            ],
        ],
        '1700' => [
            'ls' => [
                'display' => 'none',
            ],
        ],
        '1622' => [
            'rt' => [
                'left'           => '100%',
                'padding-left'   => '1003',
                'text-align'     => 'right',
                'vertical-align' => 'top',
                'width'          => '619',
            ],
            'rm' => [
                'left'           => '100%',
                'padding-left'   => '1003',
                'text-align'     => 'right',
                'vertical-align' => 'middle',
                'width'          => '619',
            ],
            'rb' => [
                'left'           => '100%',
                'padding-left'   => '1003',
                'text-align'     => 'right',
                'vertical-align' => 'bottom',
                'width'          => '619',
            ],
            'lm' => [
                'right'          => '0',
                'margin-top'     => '475',
                'padding-right'  => '0',
                'text-align'     => 'left',
                'vertical-align' => 'middle',
                'width'          => '443',
            ],
            'lb' => [
                'right'          => '0',
                'margin-top'     => '475',
                'padding-right'  => '0',
                'text-align'     => 'left',
                'vertical-align' => 'bottom',
                'width'          => '443',
            ],
        ],
        '1440' => [
            'rt' => [
                'left'           => '100%',
                'padding-left'   => '715',
                'text-align'     => 'right',
                'vertical-align' => 'top',
                'width'          => '725',
            ],
            'rm' => [
                'left'           => '100%',
                'padding-left'   => '715',
                'text-align'     => 'right',
                'vertical-align' => 'middle',
                'width'          => '725',
            ],
            'rb' => [
                'left'           => '100%',
                'padding-left'   => '715',
                'text-align'     => 'right',
                'vertical-align' => 'bottom',
                'width'          => '725',
            ],
            'lm' => [
                'display' => 'none',
            ],
            'lb' => [
                'display' => 'none',
            ],
        ],
        '1360' => [
            'rt' => [
                'left'           => '50%',
                'padding-left'   => '34',
                'text-align'     => 'right',
                'vertical-align' => 'top',
                'width'          => '646',
            ],
            'rm' => [
                'left'           => '50%',
                'padding-left'   => '34',
                'text-align'     => 'right',
                'vertical-align' => 'middle',
                'width'          => '646',
            ],
            'rb' => [
                'left'           => '50%',
                'padding-left'   => '34',
                'text-align'     => 'right',
                'vertical-align' => 'bottom',
                'width'          => '646',
            ],
        ],
        '1280' => [
            'rt' => [
                'left'           => '100%',
                'padding-left'   => '670',
                'text-align'     => 'right',
                'vertical-align' => 'top',
                'width'          => '610',
            ],
            'rm' => [
                'left'           => '100%',
                'padding-left'   => '670',
                'text-align'     => 'right',
                'vertical-align' => 'middle',
                'width'          => '610',
            ],
            'rb' => [
                'left'           => '100%',
                'padding-left'   => '670',
                'text-align'     => 'right',
                'vertical-align' => 'bottom',
                'width'          => '610',
            ],
        ],
    ];

}