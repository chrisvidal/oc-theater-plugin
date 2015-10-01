<?php namespace Abnmt\Theater\Components;

use Abnmt\Theater\Models\Background;
use Cms\Classes\ComponentBase;
use CW;

class Backgrounds extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'abnmt.theater::lang.components.backgrounds.name',
            'description' => 'abnmt.theater::lang.components.backgrounds.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'layout' => [
                'title'       => 'Макет',
                'description' => 'Определяет макет для вывода',
                'default'     => '{{ :slug }}',
            ],

        ];
    }

    /**
     * A Params object
     * @var array
     */
    public $params;

    /**
     * A collection of backgrounds to display
     * @var Collection
     */
    public $backgrounds;

    /**
     *  onRun function
     */
    public function onRun()
    {
        $this->prepareVars();

        $this->backgrounds = $this->page['backgrounds'] = $this->loadLayout();

        CW::info(['loadLayout' => $this->backgrounds]);

    }

    /**
     *  Prepare vars
     */
    protected function prepareVars()
    {
        $this->params = $this->getProperties();

        // CW::info(['Params' => $this->params]);
    }

    /**
     * @return null
     */
    protected function loadLayout()
    {
        $params = $this->params;
        $layout = Background::whereSlug($params['layout'])->with('images')->first();

        if (count($layout->images) == 0) {
            return;
        }

        // PREPARE RULES
        $rules = $this->prepareRules();
        CW::info(['Rules' => $rules]);

        // POCESS META
        $metas = $this->prepareMetas($layout->meta['backgrounds']);
        CW::info(['Meta' => $metas]);

        // ASSIGN SIZES
        // Asiign sizes and class
        $layout->images->each(function ($image) {
            $image->class_name = preg_replace('#\.(jpg|png|svg)#', '', $image->file_name);
            $this->getSizes($image);
        });

        // ASSIGN IMAGES
        $images = $layout->images;

        // PROCESS STYLES
        $styles = $this->processStyles($rules, $metas, $images, $params['layout']);

        // return; // TEMP

        // RETURN for POST
        return compact('styles', 'images');
    }

    /**
     * PROCESS STYLES
     */

    /**
     * @param $rules
     * @param $meta
     * @param $images
     */
    protected function processStyles($rules, $metas, $images, $layout)
    {
        $styles = [];

        foreach ($this->queries as $key => $query) {
            foreach ($this->positions as $column => $positions) {
                foreach ($positions as $position) {

                    CW::info($query . ' ' . $column . ' ' . $position);

                    $_rules = $rules
                        ->where('query', $query)
                        ->where('position', $position)
                        ->first()
                    ;

                    // if ($_rules['common_rules']['visibility'] == 'hidden') {
                    //     continue;
                    // }

                    CW::info(['rules' => $_rules]);

                    $_metas = $this->selectMeta($metas, $query, $position);

                    // CW::info(['After select Meta' . $query . $position, $_rules, $_metas]);
                    CW::info(['meta' => $_metas]);

                    foreach ($_metas as $_meta) {

                        // if (array_key_exists($class_name, $styles[$query])) {
                        //     continue;
                        // }
                        // if ($meta['params'] == 'none') {
                        //     continue;
                        // }

                        $image = $images
                            ->where('class_name', $_meta['class_name'])
                            ->first()
                        ;
                        $_meta['sizes'] = $image['sizes'];

                        // $styles[$query][$class_name] = array_merge($_rules, ['params' => $meta_params], ['position' => $position], ['sizes' => $sizes]);
                        // $styles[$query][$_meta['class_name']] = array_merge($_rules, $_meta);
                        $_styles  = array_merge($_rules, $_meta);
                        $styles[] = $this->computeParams($_styles);

                        // CW::info([$query . $element . ' ' . $class_name, $_rules]);
                    }

                }
            }
        }

        CW::info(['After Compute' => $styles]);

        return $this->processParams(collect($styles), $layout);
    }

    /**
     * @param $styles
     * @return mixed
     */
    protected function processParams($styles, $layout)
    {

        $return = [];
        foreach ($this->queries as $query) {
            $return[$query . 'px'] = [];
            foreach ($this->positions as $column => $positions) {
                $$column = $styles
                    ->where('query', $query)
                    ->where('column', $column)
                    // ->all()
                ;

                CW::info([$column => $$column]);

                // Calculate SUMM
                $padding = 0;
                $count   = $$column->count();

                $summ = $$column->sum(function ($item) {
                    $_ret = $item['sizes']['height'];
                    if (array_key_exists('params', $item) && !is_null($item['params'])) {
                        // if (array_key_exists('params', $item) && !is_null($item['params']) && $item['params'] != 'none') {
                        if (array_key_exists('margin-top', $item['params'])) {
                            $_ret += $item['params']['margin-top'];
                        }
                        if (array_key_exists('margin-bottom', $item['params'])) {
                            $_ret += $item['params']['margin-bottom'];
                        }
                    }
                    return $_ret;
                });

                $summ += $count * $padding;

                $$column = $$column->all();

                // Define begin top position value
                $top = 0;

                if ($layout != 'playbill' && $column == 'left') {
                    $top = 475;
                    $summ += $top;
                }
                if ($layout != 'playbill' && $column == 'right') {
                    $top = 64;
                    $summ += $top;
                }

                // Assigning TOP/BOTTOM POSITIONS/MARGINS for image element
                foreach ($$column as $key => &$item) {

                    // If rule don't consist height, skip rule
                    // if (!array_key_exists('height', $item)) {
                    //     // CW::info($value);
                    //     continue;
                    // }

                    $_item        = [];
                    $_item['top'] = ($top / $summ * 100) . '%';

                    $_height = $item['sizes']['height'];
                    $_width  = $item['sizes']['width'];

                    if (array_key_exists('params', $item) && !is_null($item['params'])) {
                        // if (array_key_exists('params', $item) && !is_null($item['params']) && $item['params'] != 'none') {
                        // Margin top
                        if (array_key_exists('margin-top', $item['params'])) {
                            $_height += $item['params']['margin-top'];
                            $_item['margin-top'] = ($item['params']['margin-top'] / $_width * 100) . '%';
                        }
                        // Margin bottom
                        if (array_key_exists('margin-bottom', $item['params'])) {
                            $_height += $item['params']['margin-bottom'];
                            $_item['margin-bottom'] = ($item['params']['margin-bottom'] / $_width * 100) . '%';
                        }
                    }

                    $_item['bottom'] = (($summ - $top - $_height) / $summ * 100) . '%';

                    $top += $_height + $padding;

                    $item['position_rules'] = array_merge($item['position_rules'], $_item);

                    $return[$query . 'px'][$item['class_name']] = $item;

                }

                // CW::info([$column => $$column]);
            }
        }

        CW::info(['Process' => $return]);
        return $return;
    }

    protected $temp = [];

    /**
     * @param $item
     */
    protected function computeParams($item)
    {

        // Read or init  temp
        if (!array_key_exists($item['class_name'], $this->temp)) {
            $temp = $this->temp[$item['class_name']] = [
                'query'  => $item['query'],
                'column' => $item['column'],
            ];
        } else {
            $temp = $this->temp[$item['class_name']];
        }

        CW::info(['Temp before' => $this->temp]);

        // Reject by none
        if ($item['common_rules']['visibility'] == 'hidden') {

            if (array_key_exists('reposition', $temp)) {
                CW::info('Reposition');
                return;
            }

            CW::info(['Hidden' => $item]);
            return $item;

        }

        // Width, Height & Ratio
        $ratio = $item['sizes']['ratio'];

        // COMPUTE DELTA
        if (array_key_exists('delta', $temp) && $temp['column'] == $item['column']) {

            // NEXT iterations -- DELTA from TEMP
            $delta = $temp['delta'];
            // RECOMPUTE SIZES with DELTA
            $width  = round($item['common_rules']['width'] * $delta);
            $height = round($width / $ratio);

        } else {

            if (array_key_exists('delta', $temp) && $temp['column'] != $item['column']) {
                $temp = $this->temp[$item['class_name']] = [
                    'query'      => $item['query'],
                    'column'     => $item['column'],
                    'reposition' => 'changed',
                ];
            }
            // FIRST iteration
            // Assign WIDTH/HEIGHT
            if (is_null($item['params']) || !array_key_exists('width', $item['params'])) {
                // SIZES from RULES
                $width  = $item['common_rules']['width'];
                $height = round($item['common_rules']['width'] / $ratio);
            } else {
                // SIZES from PARAMS
                $width  = intval($item['params']['width']);
                $height = round($item['params']['width'] / $ratio);
            }

            $delta = $temp['delta'] = $width / $item['common_rules']['width'];

        }

        // Delta Margin
        $margin = 100 - ($delta * 100) . '%';

        $item['position_rules']['margin']                        = 0; // Clear margin !!!
        $item['position_rules'][$item['common_rules']['margin']] = $margin;

        // Add params
        if (!is_null($item['params'])) {
            // if (!is_null($item['params']) && $item['params'] != 'none') {
            foreach ($item['params'] as $name => $value) {
                if (in_array($name, $this->allowedStyles)) {
                    if (in_array($name, $this->sizes)) {
                        // $item['position_rules'][$name] = $value . 'px';
                        $item['position_rules'][$name] = $value;
                    } else {
                        $item['position_rules'][$name] = $value;
                    }
                }
            }
        }

        // Return
        $item['sizes'] = compact('width', 'height', 'ratio', 'delta');

        // Write Temp
        $this->temp[$item['class_name']] = $temp;

        CW::info(['Temp after' => $this->temp]);

        CW::info(['Compute' => $item]);

        return $item;
    }

    /**
     * @param $metas
     * @param $query
     * @param $position
     * @return mixed
     */
    protected static function selectMeta($metas, $query, $position)
    {

        $return = [];

        foreach ($metas as $meta) {
            if ($meta['query'] >= $query || $meta['query'] == 'all') {
                $meta['query'] = $query;
                if ($meta['position'] == $position) {
                    $return[] = $meta;
                }
                if ($meta['position'] == 'none') {
                    foreach ($return as $key => $item) {
                        // CW::info($item);
                        if ($item['class_name'] == $meta['class_name']) {
                            unset($return[$key]);
                        }
                    }
                    // $return[] = $meta;
                }
            }
        }

        return $return;

    }

    /**
     * PROCESS META
     */

    /**
     * @param $metas
     * @return mixed
     */
    protected static function prepareMetas($metas)
    {
        $return = [];

        // CW::info($metas);

        foreach ($metas as $meta) {
            $class_name = preg_replace('#\.(jpg|png|svg)#', '', $meta['key']);

            foreach ($meta['queries'] as $items) {
                $query    = $items['query'];
                $position = $items['position'];
                $params   = array_key_exists('params', $items) ? $items['params'] : null;

                if ($params != null && count($params) > 0) {
                    $_params = [];
                    foreach ($params as $entry) {
                        $_params[$entry['param']] = $entry['value'];
                    }
                    $params = $_params;
                }

                // if (!array_key_exists($class_name, $return)) {
                //     $return[$class_name] = [];
                // }

                $return[] = compact('class_name', 'query', 'position', 'params');
            }
        }

        return collect($return);
    }

    /**
     * UTIL: GET SIZES
     */

    /**
     * @param $image
     */
    protected static function getSizes(&$image)
    {
        // Get image sizes
        $sizes = getimagesize('./' . $image->getPath());

        // Width from filename
        // preg_match('/.+?_(\d+)\.jpg/', $image->file_name, $matches);
        $width  = $sizes[0];
        $height = round($width / $sizes[0] * $sizes[1]);
        $ratio  = $width / $height;

        $image['sizes'] = compact('width', 'height', 'ratio');

    }

    /**
     * PREPARE RULES
     */

    protected $rules_temp = [];

    /**
     * @return mixed
     */
    protected function prepareRules()
    {

        $return = [];

        foreach (['position_rules', 'common_rules', 'container_rules'] as $collection) {
            $$collection = collect($this->$collection);
        }

        foreach ($this->queries as $key => $query) {
            foreach ($this->positions as $column => $positions) {
                foreach ($positions as $position) {

                    $_merge = compact('query', 'column', 'position');

                    // Read or init  temp
                    if (!array_key_exists($position, $this->rules_temp)) {
                        $temp          = $this->rules_temp[$position]          = $_merge;
                        $temp['query'] = $this->rules_temp[$position]['query'] = ['all', $query];
                    } else {
                        $temp            = $this->rules_temp[$position];
                        $temp['query'][] = $this->rules_temp[$position]['query'][] = $query;
                    }

                    // CW::info($query . ' ' . $column . ' ' . $position);

                    foreach (['common_rules', 'container_rules', 'position_rules'] as $q) {

                        $_merge[$q] = [];

                        // foreach (['all', $query] as $_q) {
                        foreach ($temp['query'] as $_q) {
                            foreach (['all', $column] as $_c) {
                                foreach (['all', $position] as $_p) {
                                    // echo $q . ' ' . $c . ' ' . $p . "\n";

                                    $_ret = $$q
                                        ->where('query', $_q)
                                        ->where('column', $_c)
                                        ->where('position', $_p)
                                        ->first()
                                    ;

                                    if (!is_null($_ret)) {

                                        // Read or init  temp
                                        if (!array_key_exists($q, $this->rules_temp[$position])) {
                                            $temp[$q] = $this->rules_temp[$position][$q] = [];
                                        } else {
                                            $temp[$q] = $this->rules_temp[$position][$q];
                                        }

                                        // CW::info([$_q . ' ' . $_c . ' ' . $_p . ' ' . $q, $_merge[$q], $_ret['rules'], array_replace($_merge[$q], $_ret['rules'])]);
                                        $_merge[$q] = array_replace($_merge[$q], $_ret['rules']);
                                        $_merge[$q] = $this->rules_temp[$position][$q] = array_replace($temp[$q], $_merge[$q]);
                                    }

                                }
                            }
                        }

                    }

                    // CW::info([$query . ' ' . $column . ' ' . $position . ' merge', $temp, $_merge, array_replace($temp, $_merge)]);
                    // $_merge = $this->rules_temp[$position] = array_replace($temp, $_merge);

                    $return[] = $_merge;
                    // CW::info($_merge);

                }
            }
        }

        return collect($return);
    }

    protected $sizes = [
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
        'height',
    ];
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
        'background-position',
        'height',
        'display',
        'transform',
    ];

    protected $queries = ['all', '1700', '1622', '1440', '1360', '1280'];

    // protected $columns = ['right', 'left', 'side'];

    // protected $positions = ['rt', 'rm', 'rb', 'lt', 'lm', 'lb', 'ls', 'none'];

    protected $positions = [
        'right' => ['rt', 'rm', 'rb'],
        'left'  => ['lt', 'lm', 'lb'],
        'side'  => ['ls'],
        'none'  => ['none'],
    ];

    // protected $column_rules = [
    //     'right' => [],
    //     'left' => [],
    // ];

    protected $position_rules = [
        [
            'query'    => 'all',
            'column'   => 'all',
            'position' => 'all',
            'rules'    => ['display' => 'block'],
        ],
        [
            'query'    => 'all',
            'column'   => 'all',
            'position' => 'none',
            'rules'    => ['display' => 'none'],
        ],
        [
            'query'    => 'all',
            'column'   => 'right',
            'position' => 'rt',
            'rules'    => ['background-position' => 'right top'],
        ],
        [
            'query'    => 'all',
            'column'   => 'right',
            'position' => 'rm',
            'rules'    => [
                'background-position' => 'right center',
                'border-top'          => '50px solid #000',
                'border-bottom'       => '50px solid #000',
            ],
        ],
        [
            'query'    => 'all',
            'column'   => 'right',
            'position' => 'rb',
            'rules'    => ['background-position' => 'right bottom'],
        ],
        [
            'query'    => 'all',
            'column'   => 'left',
            'position' => 'lt',
            'rules'    => ['background-position' => 'left top'],
        ],
        [
            'query'    => 'all',
            'column'   => 'left',
            'position' => 'lm',
            'rules'    => [
                'background-position' => 'left center',
                'border-top'          => '50px solid #000',
                'border-bottom'       => '50px solid #000',
            ],
        ],
        [
            'query'    => 'all',
            'column'   => 'left',
            'position' => 'lb',
            'rules'    => ['background-position' => 'left bottom'],
        ],
        [
            'query'    => 'all',
            'column'   => 'side',
            'position' => 'ls',
            'rules'    => ['background-position' => 'left top'],
        ],
    ];

    protected $common_rules = [
        [
            'query'    => 'all',
            'column'   => 'all',
            'position' => 'all',
            'rules'    => ['visibility' => 'visible'],
        ],
        [
            'query'    => 'all',
            'column'   => 'all',
            'position' => 'none',
            'rules'    => ['visibility' => 'hidden'],
        ],
        [
            'query'    => 'all',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => [
                'width'   => 768,
                'padding' => 192,
                'percent' => 0.5,
                'margin'  => 'margin-left',
            ],
        ],
        [
            'query'    => 'all',
            'column'   => 'left',
            'position' => 'all',
            'rules'    => [
                'width'   => 592,
                'padding' => 368,
                'percent' => 0.5,
                'margin'  => 'margin-right',
            ],
        ],
        [
            'query'    => 'all',
            'column'   => 'side',
            'position' => 'ls',
            'rules'    => [
                'width'   => 304,
                'padding' => 656,
                'percent' => 0.5,
                'margin'  => 'margin-right',
            ],
        ],
        [
            'query'    => '1700',
            'column'   => 'side',
            'position' => 'ls',
            'rules'    => ['visibility' => 'hidden'],
        ],
        [
            'query'    => '1622',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => [
                'width'   => 619,
                'padding' => 1003,
                'percent' => 1,
            ],
        ],
        [
            'query'    => '1622',
            'column'   => 'left',
            'position' => 'all',
            'rules'    => [
                'width'   => 443,
                'padding' => 0,
                'percent' => 1,
            ],
        ],
        [
            'query'    => '1440',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => [
                'width'   => 725,
                'padding' => 715,
            ],
        ],
        [
            'query'    => '1440',
            'column'   => 'left',
            'position' => 'all',
            'rules'    => ['visibility' => 'hidden'],
        ],
        [
            'query'    => '1360',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => [
                'width'   => 690,
                'padding' => 670,
            ],
        ],
        [
            'query'    => '1280',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => [
                'width'   => 610,
                'padding' => 670,
            ],
        ],
    ];

    protected $container_rules = [
        [
            'query'    => 'all',
            'column'   => 'all',
            'position' => 'all',
            'rules'    => ['display' => 'block'],
        ],
        [
            'query'    => 'all',
            'column'   => 'all',
            'position' => 'none',
            'rules'    => ['display' => 'none'],
        ],
        [
            'query'    => 'all',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => [
                'left'         => '50%',
                'right'        => '0',
                'margin-left'  => '192px',
                'margin-right' => '0',
                'width'        => 'auto',
            ],
        ],
        [
            'query'    => 'all',
            'column'   => 'left',
            'position' => 'all',
            'rules'    => [
                'left'         => '0',
                'right'        => '50%',
                'margin-right' => '368px',
                'margin-left'  => '0',
                'width'        => 'auto',
            ],
        ],
        [
            'query'    => 'all',
            'column'   => 'side',
            'position' => 'ls',
            'rules'    => [
                'top'          => '0',
                'left'         => '0',
                'right'        => '50%',
                'height'       => '475px',
                'margin-right' => '656px',
                'width'        => 'auto',
            ],
        ],
        [
            'query'    => '1700',
            'column'   => 'side',
            'position' => 'ls',
            'rules'    => ['display' => 'none'],
        ],
        [
            'query'    => '1622',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => [
                'left'        => '0',
                'margin-left' => '1003px',
            ],
        ],
        [
            'query'    => '1622',
            'column'   => 'left',
            'position' => 'all',
            'rules'    => [
                'right'        => 'auto',
                'margin-right' => '0',
                'width'        => '443px',
            ],
        ],
        [
            'query'    => '1440',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => ['margin-left' => '715px'],
        ],
        [
            'query'    => '1440',
            'column'   => 'left',
            'position' => 'all',
            'rules'    => ['display' => 'none'],
        ],
        [
            'query'    => '1360',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => ['margin-left' => '670px'],
        ],
        [
            'query'    => '1280',
            'column'   => 'right',
            'position' => 'all',
            'rules'    => ['margin-left' => '670px'],
        ],
    ];

}
