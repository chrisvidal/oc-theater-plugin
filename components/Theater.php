<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Cms\Classes\Content;
use Abnmt\Theater\Models\Event as EventModel;
use Abnmt\Theater\Models\Performance as PerformanceModel;
use Abnmt\Theater\Models\Person as PersonModel;
use Abnmt\Theater\Models\News as NewsModel;
use Abnmt\Theater\Models\Press as PressModel;

class Theater extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Theater Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * Define Component Properties
     */
    public function defineProperties()
    {
        return [
            // 'section' => [
            //     'title'       => 'Раздел',
            //     'description' => 'Переменная для URL',
            //     'default'     => '{{ :section }}',
            //     'type'        => 'string',
            // ],
            // 'subsection' => [
            //     'title'       => 'Подраздел',
            //     'description' => 'Переменная для URL',
            //     'default'     => '{{ :subsection }}',
            //     'type'        => 'string',
            // ],
            // 'item' => [
            //     'title'       => 'Страница',
            //     'description' => 'Переменная для URL',
            //     'default'     => '{{ :item }}',
            //     'type'        => 'string',
            // ],
            'testPage' => [
                'title'       => 'Контент',
                'description' => '??',
                'type'        => 'dropdown',
            ],
        ];
    }

    /*
     * Get Properties
     */
    public function getTestPageOptions()
    {
        return Content::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Prepared Page vars
     */
    protected function prepareVars()
    {
        /**
         * Template Links
         */
        // $this->performancePage = $this->page['performancePage'] = 'single/performance';
        // $this->personPage      = $this->page['personPage']      = 'single/person';
        // $this->newsPage        = $this->page['newsPage']        = 'single/news';
        // $this->pressPage       = $this->page['pressPage']       = 'single/press';

        // $this->repertoirePage  = $this->page['repertoirePage']  = 'pages/repertoire';
        // $this->troupePage      = $this->page['troupePage']      = 'pages/troupe';
        // $this->playbillPage    = $this->page['playbillPage']    = 'pages/playbill';

        /**
         * Models
         */
        $this->performance = PerformanceModel::isPublished();
        $this->person      = PersonModel::isPublished();
        $this->news        = NewsModel::isPublished();
        $this->press       = PressModel::isPublished();

        $this->models = [
            'performance' => PerformanceModel::isPublished(),
            'person'      => PersonModel::isPublished(),
            'news'        => NewsModel::isPublished(),
            'press'       => PressModel::isPublished(),
            'events'      => EventModel::isPublished(),
        ];

        /**
         * Prepare Slug
         */
        $this->section = $this->page['section'] = $this->param('section');
        $this->list    = $this->page['list']    = $this->param('list');
        $this->single  = $this->page['single']  = $this->param('single');

    }

    /**
     * On Run
     */
    public function onRun()
    {
        $this->prepareVars();

        // if ($this->single) {
        //     $this->data = $this->page['data'] = $this->getSingle();
        // }

        // if ($this->list) {
        //     $this->list = $this->page['list'] = $this->getList();
        // }

        // if ($this->list) {
        //     $this->list = $this->page['list'] = $this->getData();
        // }

        /**
         * Data
         */
        $this->data = $this->page['data'] = $this->getData();

        /**
         * Titles
         */
        $this->meta_title = $this->page->meta_title = $this->page->title = $this->title;

        /**
         * Test
         */
        $this->test = $this->page['test'] = '<pre>' . json_encode(['#DATA#' => $this->data, '#SUBMENU#' => $this->submenu, '#BYGROUP#' => $this->byGroup], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . '</pre>';
    }

    /**
     * On End
     */
    // public function onEnd()
    // {
    //     // $this->meta_title = $this->page->meta_title = $this->title;
    //     $this->title = $this->page->title = $this->title;
    // }

    protected function getData()
    {

        $options = [
            'news' => [
                'default' => [
                    'title' => 'Архив новостей',
                    'model' => NewsModel::isPublished(),
                    'sort'  => [['published_at', 'desc']],
                ],
                'press' => [
                    'title' => 'Архив прессы',
                    'model' => PressModel::isPublished(),
                    'sort'  => [['source_date', 'desc']],
                ],
            ],
            'press' => [
                'default' => [
                    'title' => 'Архив прессы',
                    'model' => PressModel::isPublished(),
                    'sort'  => [['source_date', 'desc']],
                ],
            ],
            'playbill' => [
                'default' => [
                    'title' => $this->localeCurrentMonth(),
                    'model' => EventModel::isPublished(),
                    'sort'  => [['datetime', 'asc']],
                    'with'  => ['performance'],
                    'query' => [['datetime', '>=', \Carbon\Carbon::now()->startOfMonth()]],
                ],
                'next' => [
                    'title' => $this->localeNextMonth(),
                    'model' => EventModel::isPublished(),
                    'sort'  => [['datetime', 'asc']],
                    'with'  => ['performance'],
                    'query' => [['datetime', '>=', \Carbon\Carbon::now()->startOfMonth()->addMonth()]],
                ],
                '#child' => [
                    'title' => 'Детские спектакли',
                ],
            ],
            'repertoire' => [
                'default' => [
                    'title' => 'Репертуар',
                    'model' => PerformanceModel::isPublished(),
                    'query' => [['state', '=', 'normal'], ['type', '=', 'normal']],
                    'sort'  => [['premiere_date', 'desc']],
                    'with'  => ['repertoire'],
                ],
                'child' => [
                    'title' => 'Детские спектакли',
                    'model' => PerformanceModel::isPublished(),
                    'query' => [['state', '=', 'normal'], ['type', '=', 'child']],
                    'sort'  => [['premiere_date', 'desc']],
                    'with'  => ['repertoire'],
                ],
                'archive' => [
                    'title' => 'Архивные спектакли',
                    'model' => PerformanceModel::isPublished(),
                    'query' => [['state', '=', 'archived']],
                    'sort'  => [['premiere_date', 'desc']],
                    'with'  => ['repertoire'],
                ],
            ],
            'performance' => [],
            'troupe' => [
                'default' => [
                    'title'   => 'Художественный руководитель',
                    'content' => 'hudozhestvennyj-rukovoditel',
                    'model'   => Content::get('0'), // TODO: Rewrite!
                ],
                'state' => [
                    'title' => 'Актёры',
                    'model' => PersonModel::isPublished(),
                    'query' => [['state', '=', 'state']],
                    'group' => 'grade',
                    'sort'  => [['grade', 'desc'], ['family_name', 'asc']],
                    'with'  => ['portrait'],
                ],
                'cooperate' => [
                    'title' => 'Приглашенные артисты',
                    'model' => PersonModel::isPublished(),
                    'query' => [['state', '=', 'cooperate']],
                    'sort'  => [['grade', 'desc'], ['family_name', 'asc']],
                    'with'  => ['portrait'],
                ],
                'administration' => [
                    'title' => 'Администрация',
                    'model' => PersonModel::isPublished(),
                    'query' => [['state', '=', 'administration']],
                    'sort'  => [['family_name', 'asc']],
                    'with'  => ['portrait'],
                ],
                'stage' => [
                    'title' => 'Постановочный цех',
                    'model' => PersonModel::isPublished(),
                    'query' => [['state', '=', 'stage']],
                    'sort'  => [['family_name', 'asc']],
                    'with'  => ['portrait'],
                ],
            ],
            'single' => [
                'performance' => [
                    'model' => PerformanceModel::isPublished(),
                    'nav' => 'repertoire',
                ],
                'person' => [
                    'model' => PersonModel::isPublished(),
                    'nav' => 'troupe',
                ],
                'press' => [
                    'model' => PressModel::isPublished(),
                    'nav' => 'press',
                ],
                'news' => [
                    'model' => NewsModel::isPublished(),
                    'nav' => 'news',
                ],
            ],
        ];

        if (
            array_key_exists($section = $this->section, $options) &&
            array_key_exists($list = $this->list, $options[$section])
        ) {

            /**
             * Gen Submenu
             */
            $submenu = [];
            foreach ($options[$section] as $slug => $list) {

                if (preg_match('/^\#/', $slug)) {
                    $url = '/' . $section . $slug;
                } elseif ($slug != 'default') {
                    $url = '/' . $section . '/' . $slug;
                } else {
                    $url = '/' . $section;
                }

                $submenu[$slug] = [
                    'title'  => $list['title'],
                    'slug'   => $slug,
                    'url'    => $url,
                    'active' => ($slug == $this->list) ? 'active' : '',
                ];
            }
            $this->submenu = $this->page['submenu'] = $submenu;

            /**
             * Extract Options
             */
            $params = $options[$this->section][$this->list];
            extract($params);

            /**
             * Assign Title
             */
            $this->title = $title;



            if (isset($with)) {
                $model = $model->with($with);
            }

            if (isset($query)) {
                foreach ($query as $key => $q) {
                    $model = $model->where($q[0], $q[1], $q[2]);
                }
            }

            if (isset($sort)) {
                foreach ($sort as $key => $s) {
                    $model = $model->orderBy($s[0], $s[1]);
                }
            }


            /*  */
            if (isset($content)) {
                return $model;
            }
            /*  */

            /**
             * Group Events by Months
             */
            if ($section == 'playbill') {

                $model = $model->get();

                $byGroup = $model
                    ->groupBy(function ($event) {
                        return $event->performance->type;
                    })
                ;

                $this->byGroup = $this->page['byGroup'] = $byGroup;
                return $this->page[$section] = $model;

            }


            return $this->page[$section] = $model->get();
        }
    }



    protected function localeCurrentMonth()
    {
        setlocale(LC_ALL, 'Russian');
        $month = \Carbon\Carbon::now()->startOfMonth()->formatLocalized('%B');
        return mb_convert_encoding($month, "UTF-8", "CP1251");
    }
    protected function localeNextMonth()
    {
        setlocale(LC_ALL, 'Russian');
        $month = \Carbon\Carbon::now()->startOfMonth()->addMonth()->formatLocalized('%B');
        return mb_convert_encoding($month, "UTF-8", "CP1251");
    }

    // protected function getSingle()
    // {
    //     /**
    //      * Get Single
    //      */
    //     $data = 'Это контент и ниибёт!';

    //     /**
    //      * Return
    //      */
    //     return $data;
    // }

    // protected function getList()
    // {
    //     /**
    //      * Get List
    //      */
    //     $data = 'Это контент и ниибёт!';

    //     /**
    //      * Return
    //      */
    //     return $data;
    // }

}