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
            $ratio = $width/$height;

            // Get classes for image from bg_data
            $image['class'] = join(' ', $bg_data->where('key', strval($key))->lists('class'));

            // Read params from bg_data
            $params = $bg_data->where('key', strval($key))->all();

            // Set dest array
            $rules = [];

            // Clean temp var
            $this->rule_temp = [];

            // Process image params for every rules through default rules
            foreach ($this->rules as $query => $columns) {
                $rules['q' . $query] = [];
                foreach ($columns as $column => $rule) {
                    $rules['q' . $query][$column] = $this->processRule($query, $column, $width, $height, $rule, $params, $key, $ratio);
                }
            }

            // CW::info(['Rules' => $rules]);

            $image['width']  = $this->rule_temp['thumb_width'];
            $image['height'] = round($image['width']/$ratio);

            $data = array_merge_recursive($data, $rules);


            // $data += $rules;
            // $image['class'] = join(' ', $class_names);

            $key++;
        });


        // Assign images in every rule a sizes and a position,
        // according columns and queries
        foreach ($data as $query => $columns) {
            foreach ($columns as $column => $rule) {
                if ($column == 'side')
                    continue;

                // Get colls
                $cols = collect($data[$query][$column]);

                CW::info([$query . $column => $cols]);

                // Sort colls
                $sort = collect($data[$query][$column])->sortBy('sort')->values()->all();

                // Equation a summ of heights in column scope
                $summ = $cols->sum('height');

                // Sort and summ if column is left
                if ($column == 'left') {
                    $sort = collect($data[$query][$column])->sortBy('sort')->reverse()->values()->all();
                    $summ = $cols->sum('height') + 475;
                }

                // Define begin top position value
                $top = 0;

                // Assigning heights and top positions to every image rule
                foreach ($sort as $key => &$value) {

                    // Add top position correction for left column images
                    if ($column == 'left' & $key == 0 && array_key_exists('height', $value)) {
                        $value['height'] += 475;
                        $value['styles']['margin-top'] = '475px';
                    }

                    // If rule don't consist height, skip rule
                    if (!array_key_exists('height', $value)) {
                        // CW::info($value);
                        continue;
                    }

                    $value['styles']['height'] = ($value['height'] / $summ * 100) . '%';
                    $value['styles']['top'] = $top . '%';

                    // Add height to top position sequence
                    $top += $value['styles']['height'];
                }

                // Return sorted and processed rules to data array
                $data[$query][$column] = $sort;
            }
        }

        // $post->bg_query = collect($data)->groupBy('query');
        // Send image data to Post
        $post->bg_data = $data;

        CW::info(['Data' => $data]);
        CW::info(['Performance' => $post]);

        return $post;
    }

    protected function processRule($query, $column, $width, $height, $rule, $params, $key, $ratio)
    {

        // Define returning array
        $return = [];

        // Check count of image params records
        $param_count = count($params);

        // ITERATE PARAMS
        // Iterate throgh params, assigned to image
        foreach ($params as $id => $param) {


            // Clean param
            $param = array_filter($param);

            // Set Classname of image rule
            $class_name = '.bg-' . ($key+1) . '.' . $param['class'];


            // Init TEMP
            if ( !array_key_exists( $class_name, $this->rule_temp ) )
                $this->rule_temp[$class_name] = [];



            // Rebuild param array
            if ( array_key_exists('params', $param) ) {
                $_params = [];
                foreach ($param['params'] as $entry) {
                    $_params[$entry['param']] = $entry['value'];
                }
                $param = array_merge($param, $_params);
                // CW::info(['param' => [$param, $_params]]);
            }


            // REJECT by COLUMN
            // Check conformity of rule columns and image param class
            if (in_array($param['class'], ['rt', 'rm', 'rb']) & $column != 'right')
                continue;
            if (in_array($param['class'], ['lt', 'lm', 'lb']) & $column != 'left')
                continue;
            if (in_array($param['class'], ['ls']) & $column != 'side')
                continue;



            // NONE in RULES
            // If rule required hide image, set styles and return
            if ($rule == 'none') {

                $return[] = [
                    'class' => $class_name,
                    'styles' => [
                        'display' => 'none',
                        '/* Rejected by' => 'RULE NONE */',
                    ],
                ];

                continue;
            }


            // WIDTH from TEMP
            // If this a first round of process current image,
            // set the width/temp_width/thumb_width (from filename),
            // else -- read width from temp_width
            if (array_key_exists('width', $this->rule_temp[$class_name]))
                $width = $this->rule_temp[$class_name]['width'];
            else
                $this->rule_temp[$class_name]['width'] = $this->rule_temp['thumb_width'] =  $width;



            // CW::info(['TEMP' => $this->rule_temp]);

            // DEFAULT STYLES
            // Load default styles and update from inheritance
            if ( !array_key_exists('styles', $this->rule_temp[$class_name]) )
                $this->rule_temp[$class_name]['styles'] = [];

            if ( is_array($rule)  && !array_key_exists('styles', $rule) )
                $rule['styles'] = [];

            $default_styles = array_merge($this->rule_temp[$class_name]['styles'], $rule['styles']);

            if (array_key_exists($param['class'], $rule))
                $default_styles = array_merge($default_styles, $rule[$param['class']]);



            // DELTA
            // If this a first round of process current image,
            // set the delta/temp_delta (from filename width),
            // else -- read delta from temp_delta
            if ( array_key_exists('delta', $this->rule_temp[$class_name]) )
                $delta = $this->rule_temp[$class_name]['delta'];
            else
                $delta = $this->rule_temp[$class_name]['delta'] = $width / $rule['width'];


            // SORT
            // Read sort key from param or set from rule param id
            $sort = $id;
            if ( array_key_exists('sort', $param) )
                $sort = intval($param['sort'], 10);



            // WIDTH from PARAMs
            // Set width from param, if exist,
            // and test on inheritance from temp_width through temp_param_id
            if ( array_key_exists('width', $param) ) {
                if ( !(array_key_exists('param_id', $this->rule_temp) && $this->rule_temp[$class_name]['param_id'] == $id) ) {

                    // Set thumb width, if necessary
                    if ($param['width'] > $this->rule_temp['thumb_width'])
                        $this->rule_temp['thumb_width'] = $param['width'];

                    // Set delta from param width
                    $delta = $this->rule_temp[$class_name]['delta'] = $param['width'] / $rule['width'];
                    // Set param_id sign
                    $this->rule_temp[$class_name]['param_id'] = $id;

                }
            }




            // SIZES and PADDING
            // For current query: delta = file_width| param_width / rule_width
            // For inheritance delta loads from temp_delta
            // Width/Height -- for current query and column
            $width  = round($rule['width'] * $delta);
            $height = round($width / $ratio);
            $margin = round($query * $rule['percent'] - $width) . 'px';


            // PARAM STYLES
            // Load param styles
            $param_styles = [];
            foreach ($param as $name => $value) {
                if ( in_array($name, $this->allowedStyles) )
                    $param_styles[$name] = $value;
            }

            $calc_styles = [];
            if ($rule['margin'] != 'none')
                $calc_styles[$rule['margin']] = $margin;



            // MERGE STYLES
            $styles = $this->rule_temp[$class_name]['styles'] = array_merge($default_styles, $calc_styles, $param_styles);

            // DISPLAY
            $styles['display'] = 'block';

            // NONE by not QUERY
            // Check if the param query fit the rule query and return
            if ( array_key_exists('query', $param) && $query > $param['query'] ) {

                // REPLACE sign
                $this->rule_temp['replace'] = [$class_name, $param['query']];

                $return[] = [
                    'class' => $class_name,
                    'styles' => [
                        'display' => 'none',
                        '/* Rejected by' => 'QUERY */',
                    ],
                ];

                continue;
            }

            if ( array_key_exists('replace', $this->rule_temp) && $class_name != $this->rule_temp['replace'][0] && $query <= $this->rule_temp['replace'][1] ) {
                CW::info([$query . $class_name . 'Rejected by REPLACE' => [
                    'Styles' => [$default_styles, $calc_styles, $param_styles],
                    'Temp' => $this->rule_temp,
                ]]);
                continue;
            }

            // PREPARE RETURN DATA
            $_ret = [
                'id'      => $id,
                'class'   => '.bg-' . ($key+1) . '.' . $param['class'],
                'width'   => $width,
                'height'  => $height,
                'ratio'   => $ratio,
                'delta'   => $delta,
                'sort'    => $sort,
                'styles'  => $styles,
            ];

            // RETURN DATA
            $return[] =  $_ret;

            CW::info([$query . $class_name => [
                'Styles' => [$default_styles, $calc_styles, $param_styles],
                'Temp' => $this->rule_temp,
                'RET' => $_ret,
            ]]);

        }

        // FILTER and RETURN DATA
        return array_filter($return);

    }

    protected $rule_temp = [];


    protected $allowedStyles = [
        'top',
        'left',
        'right',
        'bottom',
        'padding',
        'padding-top',
        'padding-left',
        'padding-right',
        'padding-bottom',
        'margin',
        'margin-top',
        'margin-left',
        'margin-right',
        'margin-bottom',
        'vertical-align',
        'text-align',
        'height',
        'display',
    ];


    protected $rules = [
        '1920' => [
            'right' => [
                'width'   => 768,
                'padding' => 192,
                'percent' => 0.5,
                'margin'  => 'margin-left',
                'styles'  => [
                    'left'          => '50%',
                    'right'         => '0',
                    'text-align'    => 'right',
                    'margin-left'  => '192px',
                    'margin-right' => '0',
                ],
                'rt' => [
                    'vertical-align' => 'top',
                ],
                'rm' => [
                    'vertical-align' => 'middle',
                ],
                'rb' => [
                    'vertical-align' => 'bottom',
                ],
            ],
            'left' => [
                'width'   => 592,
                'padding' => 368,
                'percent' => 0.5,
                'margin'  => 'margin-right',
                'styles'  => [
                    'left'          => '0',
                    'right'         => '50%',
                    'text-align'    => 'left',
                    'margin-right' => '368px',
                    'margin-left'  => '0',
                ],
                'lt' => [
                    'vertical-align' => 'top',
                ],
                'lm' => [
                    'vertical-align' => 'middle',
                ],
                'lb' => [
                    'vertical-align' => 'bottom',
                ],
            ],
            'side' => [
                'width'   => 304,
                'padding' => 656,
                'percent' => 0.5,
                'margin'  => 'margin-right',
                'styles'  => [
                    'top'            => '0',
                    'left'           => '0',
                    'right'          => '50%',
                    'text-align'     => 'left',
                    'vertical-align' => 'bottom',
                    'height'         => '475px',
                    'margin-right'   => '656px',
                ],
                'ls' => [
                    'vertical-align' => 'bottom',
                ],
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
                'margin'  => 'margin-left',
                'styles'  => [
                    'left'         => '0',
                    'margin-left' => '1003px',
                ],
            ],
            'left' => [
                'width'   => 443,
                'percent' => 0,
                'margin'  => 'none',
                'styles'  => [
                    'left'         => '0',
                    'right'        => 'auto',
                    'text-align'   => 'left',
                    'margin-right' => '0',
                    'width'        => '443px' // ???
                ],
            ],
        ],
        '1440' => [
            'right' => [
                'width'   => 725,
                'padding' => 715,
                'percent' => 1,
                'margin'  => 'margin-left',
                'styles'  => [
                    'margin-left' => '715px',
                ],
            ],
            'left' => 'none',
        ],
        '1360' => [
            'right' => [
                'width'   => 690,
                'padding' => 670,
                'percent' => 1,
                'margin'  => 'margin-left',
                'styles'  => [
                    'margin-left' => '670px',
                ],
            ],
        ],
        '1280' => [
            'right' => [
                'width'   => 610,
                'padding' => 670,
                'percent' => 1,
                'margin'  => 'margin-left',
                'styles'  => [
                    'margin-left' => '670px',
                ],
            ],
        ],
    ];


}