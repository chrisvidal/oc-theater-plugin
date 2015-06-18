<?php namespace Abnmt\Theater;


use System\Classes\PluginBase;

use Abnmt\Theater\Models\Event as EventModel;
use Abnmt\Theater\Models\Playbill as PlaybillModel;
use Abnmt\Theater\Models\Performance as PerformanceModel;
use Abnmt\Theater\Models\Person as PersonModel;
use Abnmt\Theater\Models\News as NewsModel;
use Abnmt\Theater\Models\Press as PressModel;

use Abnmt\Theater\Models\EventCategory as EventCategoryModel;
use Abnmt\Theater\Models\PerformanceCategory as PerformanceCategoryModel;
use Abnmt\Theater\Models\PersonCategory as PersonCategoryModel;
use Abnmt\Theater\Models\NewsCategory as NewsCategoryModel;
use Abnmt\Theater\Models\PressCategory as PressCategoryModel;

use Abnmt\Theater\Controllers\Performances as PerformancesController;
use Abnmt\Theater\Controllers\People as PeopleController;

use Illuminate\Support\Facades\App;
use Illuminate\Foundation\AliasLoader;

use Laravelrus\LocalizedCarbon\LocalizedCarbon as LocalizedCarbon;

use Event;

/**
 * Theater Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'abnmt.theater::lang.plugin.name',
            'description' => 'abnmt.theater::lang.plugin.description',
            'author'      => 'Abnmt',
            'icon'        => 'icon-university',
        ];
    }

    public function registerNavigation()
    {
        return [
            'theater' => [
                'label' => 'Театр',
                'url' => \Backend::url('abnmt/theater/panel'),
                'icon' => 'icon-university',
                'order' => 500,
                'sideMenu' => [
                    'news' => [
                        'label' => 'Новости',
                        'icon'  => 'icon-newspaper-o',
                        'url'   => \Backend::url('abnmt/theater/news'),
                    ],
                    'events' => [
                        'label' => 'Афиша',
                        'icon'  => 'icon-calendar',
                        'url'   => \Backend::url('abnmt/theater/playbill'),
                    ],
                    'performances' => [
                        'label' => 'Спектакли',
                        'icon'  => 'icon-clipboard',
                        'url'   => \Backend::url('abnmt/theater/performances'),
                    ],
                    'persons' => [
                        'label' => 'Люди',
                        'icon'  => 'icon-users',
                        'url'   => \Backend::url('abnmt/theater/people'),
                    ],
                    'press' => [
                        'label' => 'Пресса',
                        'icon'  => 'icon-newspaper-o',
                        'url'   => \Backend::url('abnmt/theater/press'),
                    ],
                    'partners' => [
                        'label' => 'Партнёры',
                        'icon'  => 'icon-ticket',
                        'url'   => \Backend::url('abnmt/theater/partners'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Register Components
     * @return array
     */
    public function registerComponents()
    {
        return [
            // 'Abnmt\Theater\Components\Theater'      => 'theater',
            'Abnmt\Theater\Components\Playbill'     => 'theaterPlaybill',
            'Abnmt\Theater\Components\Repertoire'   => 'theaterRepertoire',
            'Abnmt\Theater\Components\Troupe'       => 'theaterTroupe',
            'Abnmt\Theater\Components\News'         => 'theaterNews',
            'Abnmt\Theater\Components\Press'        => 'theaterPress',
            'Abnmt\Theater\Components\Person'       => 'theaterPerson',
            'Abnmt\Theater\Components\Performance'  => 'theaterPerformance',
            'Abnmt\Theater\Components\Partners'     => 'theaterPartners',
        ];
    }
    /**
     * Register Components
     * @return array
     */
    // public function registerPageSnippets()
    // {
    //     return [
    //         'Abnmt\Theater\Components\Theater' => 'theater',
    //     ];
    // }



    public function boot()
    {
        App::register( 'Laravelrus\LocalizedCarbon\LocalizedCarbonServiceProvider' );

        $alias = AliasLoader::getInstance();
        $alias->alias( 'LocalizedCarbon', 'Laravelrus\LocalizedCarbon\LocalizedCarbon' );
        $alias->alias( 'DiffFormatter'  , 'Laravelrus\LocalizedCarbon\DiffFactoryFacade' );


        /*
         * Register menu items for the RainLab.Pages plugin
         */
        Event::listen('pages.menuitem.listTypes', function() {
            return [
                'news-category' => 'Новости по категориям',
                'performance-category' => 'Спектакли по категориям',
                // 'press-category' => 'Архив прессы',
                'playbill-now' => 'Афиша на текущий месяц',
                'playbill-next' => 'Афиша на следующие месяцы',
                'person-category' => 'Люди по категориям',
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function($type) {
            if ($type == 'news-category')
                return NewsCategoryModel::getMenuTypeInfo($type);
            if ($type == 'performance-category')
                return PerformanceCategoryModel::getMenuTypeInfo($type);
            if ($type == 'person-category')
                return PersonCategoryModel::getMenuTypeInfo($type);
            if ($type == 'playbill-now' || $type == 'playbill-next')
                return PlaybillModel::getMenuTypeInfo($type);
            // if ($type == 'press-category')
            //     return PressCategoryModel::getMenuTypeInfo($type);
        });

        Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
            if ($type == 'news-category')
                return NewsCategoryModel::resolveMenuItem($item, $url, $theme);
            if ($type == 'performance-category')
                return PerformanceCategoryModel::resolveMenuItem($item, $url, $theme);
            if ($type == 'person-category')
                return PersonCategoryModel::resolveMenuItem($item, $url, $theme);
            if ($type == 'playbill-now' || $type == 'playbill-next')
                return PlaybillModel::resolveMenuItem($item, $url, $theme);
            // if ($type == 'press-category')
            //     return PressCategoryModel::resolveMenuItem($item, $url, $theme);
        });
    }

    /**
     * Register Twig tags
     * @return array
     */
    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'weekday' => function ( $datetime ) {
                    setlocale(LC_ALL, 'Russian');
                    $weekday = \Carbon\Carbon::parse($datetime)->formatLocalized('%a');
                    return mb_convert_encoding($weekday, "UTF-8", "CP1251");
                },
                'weekday_long' => function ( $datetime ) {
                    setlocale(LC_ALL, 'Russian');
                    $weekday = \Carbon\Carbon::parse($datetime)->formatLocalized('%A');
                    return mb_convert_encoding($weekday, "UTF-8", "CP1251");
                },
                'month' => function ( $datetime ) {
                    setlocale(LC_ALL, 'Russian');
                    return $month = LocalizedCarbon::parse($datetime)->formatLocalized('%f');
                },
                'humanDate' => function ( $datetime ) {
                    setlocale(LC_ALL, 'Russian');
                    return $date = LocalizedCarbon::parse($datetime)->formatLocalized('%e %f %Y года');
                    // return $date = LocalizedCarbon::parse($datetime)->formatLocalized('%G');
                    // return mb_convert_encoding($date, "UTF-8", "CP1251");
                },
                'duration' => function ( $datetime ) {
                    $interval = preg_split('/\:|\:0/', $datetime);
                    $diff = new \DateInterval('PT' . $interval[0] . 'H' . $interval[1] . 'M');
                    \Carbon\CarbonInterval::setLocale('ru');
                    return \Carbon\CarbonInterval::instance($diff);
                },
            ]
        ];
    }

}
