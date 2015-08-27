<?php namespace Abnmt\Theater;


use System\Classes\PluginBase;

use Abnmt\Theater\Models\Event         as EventModel;
// use Abnmt\Theater\Models\Article       as ArticleModel;
use Abnmt\Theater\Models\Performance   as PerformanceModel;
use Abnmt\Theater\Models\Person        as PersonModel;
use Abnmt\Theater\Models\Taxonomy      as TaxonomyModel;
use Abnmt\Theater\Models\Participation as ParticipationModel;

// use Abnmt\Theater\Controllers\Performances as PerformancesController;
// use Abnmt\Theater\Controllers\People as PeopleController;

use Illuminate\Support\Facades\App;
use Illuminate\Foundation\AliasLoader;

// use Laravelrus\LocalizedCarbon\LocalizedCarbon as LocalizedCarbon;

use Event;
use Lang;

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
				'url' => \Backend::url('abnmt/theater/dashboard'),
				'icon' => 'icon-university',
				'order' => 400,
				'sideMenu' => [
					// 'news' => [
					// 	'label' => 'Новости',
					// 	'icon'  => 'icon-newspaper-o',
					// 	'url'   => \Backend::url('abnmt/theater/news'),
					// ],
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
					// 'press' => [
					// 	'label' => 'Пресса',
					// 	'icon'  => 'icon-newspaper-o',
					// 	'url'   => \Backend::url('abnmt/theater/press'),
					// ],
					// 'partners' => [
					// 	'label' => 'Партнёры',
					// 	'icon'  => 'icon-ticket',
					// 	'url'   => \Backend::url('abnmt/theater/partners'),
					// ],
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
			'Abnmt\Theater\Components\Theater'      => 'theater',
			'Abnmt\Theater\Components\Events'       => 'theaterEvents',
			'Abnmt\Theater\Components\Person'       => 'theaterPerson',
			'Abnmt\Theater\Components\Performance'  => 'theaterPerformance',
		];
	}


	/**
	 * Register Page snippets
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
		// App::register( 'Laravelrus\LocalizedCarbon\LocalizedCarbonServiceProvider' );

		$alias = AliasLoader::getInstance();
		// $alias->alias( 'LocalizedCarbon', 'Laravelrus\LocalizedCarbon\LocalizedCarbon' );
		// $alias->alias( 'DiffFormatter'  , 'Laravelrus\LocalizedCarbon\DiffFactoryFacade' );

		$alias->alias( 'Carbon', '\Carbon\Carbon' );
		$alias->alias( 'CW'  ,   '\Clockwork\Support\Laravel\Facade' );


		/*
		 * Register menu items for the RainLab.Pages plugin
		 */
		Event::listen('pages.menuitem.listTypes', function() {
			return [
				'repertoire' => 'Репертуар',
				'troupe'     => 'Труппа',
				'playbill'   => 'Афиша',
			];
		});

		Event::listen('pages.menuitem.getTypeInfo', function($type) {
			if ($type == 'repertoire')
				return PerformanceModel::getMenuTypeInfo($type);
			if ($type == 'troupe')
				return PersonModel::getMenuTypeInfo($type);
			if ($type == 'playbill')
				return EventModel::getMenuTypeInfo($type);
		});

		Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
			if ($type == 'repertoire')
				return PerformanceModel::resolveMenuItem($item, $url, $theme);
			if ($type == 'troupe')
				return PersonModel::resolveMenuItem($item, $url, $theme);
			if ($type == 'playbill')
				return EventModel::resolveMenuItem($item, $url, $theme);
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
				'duration' => function ( $datetime ) {
					$interval = preg_split('/\:|\:0/', $datetime);
					$diff = new \DateInterval('PT' . $interval[0] . 'H' . $interval[1] . 'M');
					\Carbon\CarbonInterval::setLocale('ru');
					return \Carbon\CarbonInterval::instance($diff);
				},
				'dateLocale' => [ $this, 'dateLocale'],
			]
		];
	}


	public static function dateLocale($dateString, $dateFormat = '%c') {

		$dateString = strtotime($dateString);

		if ($dateFormat === '%c') $dateFormat   = Lang::get('abnmt.theater::lang.dates.dateFormat');

		$months_nom    = explode("|", Lang::get('abnmt.theater::lang.dates.months_nom') );
		$months_shrt   = explode("|", Lang::get('abnmt.theater::lang.dates.months_shrt') );
		$months_gen    = explode("|", Lang::get('abnmt.theater::lang.dates.months_gen') );
		$weekdays_nom  = explode("|", Lang::get('abnmt.theater::lang.dates.weekdays_nom') );
		$weekdays_shrt = explode("|", Lang::get('abnmt.theater::lang.dates.weekdays_shrt') );

		$dateFormat = preg_replace("~\%B~", $months_nom[date('n', $dateString)], $dateFormat);
		$dateFormat = preg_replace("~\%h~", $months_gen[date('n', $dateString)], $dateFormat);
		$dateFormat = preg_replace("~\%A~", $weekdays_nom[date('N', $dateString)], $dateFormat);
		$dateFormat = preg_replace("~\%a~", $weekdays_shrt[date('N', $dateString)], $dateFormat);


		return strftime($dateFormat, $dateString );
	}


	public function register()
	{
		$this->registerConsoleCommand('theater.dev', 'Abnmt\Theater\Console\Dev');
	}

}
