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
        // CW::info(['Rules' => $rules]);

        // POCESS META
        $metas = $this->processMeta($layout->meta['backgrounds']);
        // CW::info(['Meta' => $metas]);

        // ASSIGN SIZES
        // Asiign sizes and class
        $layout->images->each(function ($image) {
            $image->class_name = preg_replace('#\.(jpg|png|svg)#', '', $image->file_name);
            $this->getSizes($image);
        });

        // ASSIGN IMAGES
        $images = $layout->images;

        // PROCESS STYLES
        $styles = $this->processStyles($rules, $metas, $images);

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
    protected function processStyles($rules, $metas, $images)
    {
        $styles = [];
        // CW::info(['Images', $images]);

        foreach ($this->queries as $key => $query) {
            $styles[$query] = [];
            foreach ($this->elements as $element) {

                $_rules  = $rules[$query][$element];
                $_styles = $this->selectMeta($metas, $query, $element);
                CW::info(['After select Meta' . $query . $element, $_rules, $_styles]);

                foreach ($_styles as $key => $entry) {
                    extract($entry);

                    if (array_key_exists($class_name, $styles[$query])) {
                        continue;
                    }
                    if ($meta_params == 'none') {
                        continue;
                    }

                    $sizes = [];
                    foreach ($images as $key => $image) {
                        if ($class_name == $image['class_name']) {
                            $sizes = $image['sizes'];
                        }
                    }

                    $styles[$query][$class_name] = array_merge($_rules, ['params' => $meta_params], ['sizes' => $sizes]);
                    // CW::info([$query . $element . ' ' . $class_name, $_rules]);

                }

            }
        }

        CW::info(['Styles before compute' => $styles]);

        return $this->processParams($styles);
    }

    /**
     * @param $styles
     * @return mixed
     */
    protected function processParams($styles)
    {

        $_styles = [];

        // Prepare for collect
        foreach ($styles as $query => $entries) {
            foreach ($entries as $class_name => $entry) {
                $_item     = array_merge(['query' => $query], ['class_name' => $class_name], $entry);
                $_styles[] = $this->computeParams($_item);
            }
        }

        CW::info(['Styles before assign positions' => $_styles]);

        $_styles = collect($_styles);

        // CW::info(['temp' => $_styles->count()]);
        // CW::info(['temp' => $_styles->where('column', 'right')->all()]);

        $return = [];
        foreach ($this->queries as $query) {
            $return[$query . 'px'] = [];
            foreach ($this->columns as $column) {
                $$column = $_styles
                    ->where('query', intval($query))
                    ->where('column', $column)
                    // ->all()
                ;

                // Calculate SUMM
                $padding = 5;
                $count   = $$column->count();

                $summ = $$column->sum(function ($item) {
                    $_ret = $item['sizes']['height'];
                    // if (array_key_exists('params', $item) && !is_null($item['params'])) {
                    if (array_key_exists('params', $item) && !is_null($item['params']) && $item['params'] != 'none') {
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

                    // if (array_key_exists('params', $item) && !is_null($item['params'])) {
                    if (array_key_exists('params', $item) && !is_null($item['params']) && $item['params'] != 'none') {
                        // Margin top
                        if (array_key_exists('margin-top', $item['params'])) {
                            $_height += $item['params']['margin-top'];
                        }
                        // Margin bottom
                        if (array_key_exists('margin-bottom', $item['params'])) {
                            $_height += $item['params']['margin-bottom'];
                        }
                    }

                    $_item['bottom'] = (($summ - $top - $_height) / $summ * 100) . '%';

                    $top += $_height + $padding;

                    $item['element_rules'] = array_merge($item['element_rules'], $_item);

                    $return[$query . 'px'][$item['class_name']] = $item;

                }

                // CW::info([$column => $$column]);
            }
        }

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

        // Reject by none
        if ($item['rules'] == 'none') {
            $item['container_rules'] = [
                'display' => 'none',
            ];
            return $item;
        }

        // Width, Height & Ratio
        $ratio = $item['sizes']['ratio'];

        // COMPUTE DELTA
        if (array_key_exists('delta', $temp) && $temp['column'] == $item['column']) {
            // NEXT iterations -- DELTA from TEMP
            $delta = $temp['delta'];
            // RECOMPUTE SIZES with DELTA
            $width  = round($item['rules']['width'] * $delta);
            $height = round($width / $ratio);
        } else {
            // FIRST iteration
            // Assign WIDTH/HEIGHT
            if (is_null($item['params']) || !array_key_exists('width', $item['params'])) {
                // SIZES from RULES
                $width  = $item['rules']['width'];
                $height = round($item['rules']['width'] / $ratio);
            } else {
                // SIZES from PARAMS
                $width  = intval($item['params']['width']);
                $height = round($item['params']['width'] / $ratio);
            }

            $delta = $temp['delta'] = $width / $item['rules']['width'];
        }

        // Delta Margin
        $margin = 100 - ($delta * 100) . '%';

        $item['element_rules']['margin']                 = 0; // Clear margin !!!
        $item['element_rules'][$item['rules']['margin']] = $margin;

        // Add params
        // if (!is_null($item['params'])) {
        if (!is_null($item['params']) && $item['params'] != 'none') {
            foreach ($item['params'] as $name => $value) {
                if (in_array($name, $this->allowedStyles)) {
                    $item['element_rules'][$name] = $value;
                }
            }
        }

        // Return
        $item['sizes'] = compact('width', 'height', 'ratio', 'delta');

        // Write Temp
        $this->temp[$item['class_name']] = $temp;

        return $item;
    }

    /**
     * @param $metas
     * @param $query
     * @param $element
     * @return mixed
     */
    protected static function selectMeta($metas, $query, $element)
    {

        $return = [];

        foreach ($metas as $class_name => $queries) {
            foreach ($queries as $meta_query => $meta_params) {

                if ($meta_query >= $query || $meta_query == 'all') {
                    CW::info($query);
                    CW::info($meta_query);
                    CW::info($element);
                    CW::info($meta_params['position']);
                    if ($meta_params['position'] == $element) {

                        $return[] = [
                            'class_name'  => $class_name,
                            'meta_params' => $meta_params['params'],
                        ];

                    }
                    if ($meta_params['position'] == 'none') {
                        // $return[] = [
                        //     'class_name'  => $class_name,
                        //     'meta_params' => 'none',
                        // ];
                        foreach ($return as $key => $item) {
                            if ($item['class_name'] == $class_name) {
                                unset($return[$key]);
                            }
                        }
                    }
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
    protected static function processMeta($metas)
    {
        $return = [];

        CW::info($metas);

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

                if (!array_key_exists($class_name, $return)) {
                    $return[$class_name] = [];
                }

                $return[$class_name][$query] = compact('position', 'params');
            }
        }

        return $return;
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

    /**
     * @return mixed
     */
    protected function prepareRules()
    {
        $return = [];
        foreach ($this->queries as $key => $query) {
            // CW::info($query);
            $return[$query] = [];
            foreach ($this->columns as $column) {
                // CW::info($column);
                foreach ($this->elements as $element) {
                    // CW::info($element);
                    foreach (['rules', 'container_rules', 'element_rules'] as $q) {

                        $continue = false;

                        // CW::info($query . ' ' . $column . ' ' . $element . ' ' . $q);

                        $_query   = 'all';
                        $_column  = 'all';
                        $_element = 'all';

                        $_array = $this->$q;
                        // CW::info(['arr' => $_array]);
                        // CW::info(['query' => $query]);

                        if (array_key_exists($query, $_array)) {
                            $_query = $query;
                        } elseif (!array_key_exists($_query, $_array)) {
                            // CW::info('continue');
                            $continue = true;
                            continue;
                        }

                        $_array = $_array[$_query];
                        // CW::info(['_query' => $_array]);
                        // CW::info(['column' => $column]);

                        if (array_key_exists($column, $_array)) {
                            $_column = $column;
                        } elseif (!array_key_exists($_column, $_array)) {
                            // CW::info('continue');
                            $continue = true;
                            continue;
                        }

                        $_array = $_array[$_column];
                        // CW::info(['_column' => $_array]);
                        // CW::info(['element' => $element]);

                        if (array_key_exists($element, $_array)) {
                            $_element = $element;
                        } elseif (!array_key_exists($_element, $_array)) {
                            // CW::info('continue');
                            $continue = true;
                            continue;
                        }

                        $_array = $_array[$_element];
                        // CW::info(['_element' => $_array]);

                        $$q = $_array;
                        // CW::info(['$$q' => $$q]);

                    }

                    if ($continue) {
                        continue;
                    }

                    if ($key > 0) {
                        // CW::info($query);
                        // CW::info($element);
                        // CW::info($this->queries[$key - 1]);
                        // CW::info($this->queries[$key]);

                        $pre = $return[$this->queries[$key - 1]][$element];
                        $cur = compact('column', 'rules', 'container_rules', 'element_rules');

                        // CW::info([$pre, $cur]);
                        // CW::info(array_replace_recursive($pre, $cur));

                        $return[$query][$element] = array_replace_recursive($pre, $cur);
                    } else {
                        $return[$query][$element] = compact('column', 'rules', 'container_rules', 'element_rules');
                    }

                    // CW::info(['Return ' . $query . ' ' . $element => $return[$query][$element]]);

                }
            }
        }

        return $return;
    }

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

    protected $queries = ['1920', '1622', '1440', '1360', '1280'];

    protected $columns = ['right', 'left'];

    protected $elements = ['rt', 'rm', 'rb', 'lt', 'lm', 'lb'];

    protected $element_rules = [
        'all' => [
            'right' => [
                'rt' => ['background-position' => 'right top'],
                'rm' => ['background-position' => 'right center'],
                'rb' => ['background-position' => 'right bottom'],
            ],
            'left'  => [
                'lt' => ['background-position' => 'left top'],
                'lm' => ['background-position' => 'left center'],
                'lb' => ['background-position' => 'left bottom'],
            ],
        ],
    ];

    protected $rules = [
        '1920' => [
            'right' => [
                'all' => [
                    'width'   => 768,
                    'padding' => 192,
                    'percent' => 0.5,
                    'margin'  => 'margin-left',
                ],
            ],
            'left'  => [
                'all' => [
                    'width'   => 592,
                    'padding' => 368,
                    'percent' => 0.5,
                    'margin'  => 'margin-right',
                ],
            ],
        ],
        '1622' => [
            'right' => [
                'all' => [
                    'width'   => 619,
                    'padding' => 1003,
                    'percent' => 1,
                ],
            ],
            'left'  => [
                'all' => [
                    'width'   => 443,
                    'padding' => 0,
                    'percent' => 1,
                    'margin'  => 'margin-right',
                ],
            ],
        ],
        '1440' => [
            'right' => [
                'all' => [
                    'width'   => 725,
                    'padding' => 715,
                ],
            ],
            'left'  => [
                'all' => 'none',
            ],
        ],
        '1360' => [
            'right' => [
                'all' => [
                    'width'   => 690,
                    'padding' => 670,
                ],
            ],
            'left'  => [
                'all' => 'none',
            ],
        ],
        '1280' => [
            'right' => [
                'all' => [
                    'width'   => 610,
                    'padding' => 670,
                ],
            ],
            'left'  => [
                'all' => 'none',
            ],
        ],
    ];

    protected $container_rules = [
        '1920' => [
            'right' => [
                'all' => [
                    'left'         => '50%',
                    'right'        => '0',
                    'margin-left'  => '192px',
                    'margin-right' => '0',
                    // 'margin-top'   => '64px',
                    'width'        => 'auto',
                ],
            ],
            'left'  => [
                'all' => [
                    'left'         => '0',
                    'right'        => '50%',
                    'margin-right' => '368px',
                    'margin-left'  => '0',
                    // 'margin-top'   => '475px',
                    'width'        => 'auto',
                ],
            ],
        ],
        '1622' => [
            'right' => [
                'all' => [
                    'left'        => '0',
                    'margin-left' => '1003px',
                ],
            ],
            'left'  => [
                'all' => [
                    'right'        => 'auto',
                    'margin-right' => '0',
                    'width'        => '443px', // ???
                ],
            ],
        ],
        '1440' => [
            'right' => [
                'all' => [
                    'margin-left' => '715px',
                ],
            ],
            'left'  => [
                'all' => 'none',
            ],
        ],
        '1360' => [
            'right' => [
                'all' => [
                    'margin-left' => '670px',
                ],
            ],
            'left'  => [
                'all' => 'none',
            ],
        ],
        '1280' => [
            'right' => [
                'all' => [
                    'margin-left' => '670px',
                ],
            ],
            'left'  => [
                'all' => 'none',
            ],
        ],
    ];

}
