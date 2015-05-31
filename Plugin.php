<?php namespace Abnmt\Theater;

use System\Classes\PluginBase;

use Abnmt\Theater\Models\Performance as PerformanceModel;
use Abnmt\Theater\Models\Person as PersonModel;
use Abnmt\Theater\Models\TaxonomyGroup as TaxonomyGroupModel;

use Abnmt\Theater\Controllers\Performances as PerformancesController;
use Abnmt\Theater\Controllers\People as PeopleController;

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

}
