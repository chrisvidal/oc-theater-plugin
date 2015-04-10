<?php namespace Abnmt\Theater;

use System\Classes\PluginBase;

/**
 * Theater Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */


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
            'icon'        => 'icon-university'
        ];
    }

    public function registerNavigation()
    {
        return [
            'theater' => [
                'label'       => 'Театр',
                'url'         => \Backend::url('abnmt/theater/events'),
                'icon'        => 'icon-university',
                'order'       => 500,
                'sideMenu' => [
                    'events' => [
                        'label'       => 'События',
                        'icon'        => 'icon-calendar',
                        'url'         => \Backend::url('abnmt/theater/events'),
                    ],
                    'performances' => [
                        'label'       => 'Спектакли',
                        'icon'        => 'icon-clipboard',
                        'url'         => \Backend::url('abnmt/theater/performances'),
                    ],
                    'persons' => [
                        'label'       => 'Персоналии',
                        'icon'        => 'icon-users',
                        'url'         => \Backend::url('abnmt/theater/persons'),
                    ],
                ],
            ]
        ];
    }

    public function registerComponents()
    {
        return [
            'Abnmt\Theater\Components\Playbill' => 'playbill',
            'Abnmt\Theater\Components\Calendar' => 'calendar',
            'Abnmt\Theater\Components\Performance' => 'performance',
            'Abnmt\Theater\Components\Performances' => 'repertoire',
            'Abnmt\Theater\Components\Person' => 'person',
            'Abnmt\Theater\Components\Troupe' => 'troupe',
        ];
    }
}
