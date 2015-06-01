<?php namespace Abnmt\Theater;

use System\Classes\PluginBase;

use Abnmt\Theater\Models\Performance as PerformanceModel;
use Abnmt\Theater\Models\Person as PersonModel;

use Abnmt\Theater\Controllers\Performances as PerformancesController;
use Abnmt\Theater\Controllers\People as PeopleController;

use Illuminate\Support\Facades\App;
use Illuminate\Foundation\AliasLoader;

use Laravelrus\LocalizedCarbon\LocalizedCarbon as LocalizedCarbon;

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
                        'label' => 'События',
                        'icon'  => 'icon-calendar',
                        'url'   => \Backend::url('abnmt/theater/events'),
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
            'Abnmt\Theater\Components\Data'    => 'data',
            'Abnmt\Theater\Components\Theater' => 'theater',
    //         'Abnmt\Theater\Components\Playbill'     => 'playbill',
    //         'Abnmt\Theater\Components\Calendar'     => 'calendar',
    //         'Abnmt\Theater\Components\Performance'  => 'performance',
    //         'Abnmt\Theater\Components\Performances' => 'repertoire',
    //         'Abnmt\Theater\Components\Person'       => 'person',
    //         'Abnmt\Theater\Components\Troupe'       => 'troupe',
    //         'Abnmt\Theater\Components\Press'        => 'press',
    //         'Abnmt\Theater\Components\PressArchive' => 'pressArchive',
        ];
    }
    /**
     * Register Components
     * @return array
     */
    public function registerPageSnippets()
    {
        return [
            'Abnmt\Theater\Components\Theater' => 'theater',
        ];
    }



    public function boot()
    {
        App::register( 'Laravelrus\LocalizedCarbon\LocalizedCarbonServiceProvider' );

        $alias = AliasLoader::getInstance();
        $alias->alias( 'LocalizedCarbon', 'Laravelrus\LocalizedCarbon\LocalizedCarbon' );
        $alias->alias( 'DiffFormatter'  , 'Laravelrus\LocalizedCarbon\DiffFactoryFacade' );
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
                'month' => function ( $datetime ) {
                    setlocale(LC_ALL, 'Russian');
                    return $month = LocalizedCarbon::parse($datetime)->formatLocalized('%f');
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
