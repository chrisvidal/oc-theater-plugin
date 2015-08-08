<?php namespace Abnmt\Theater;


use System\Classes\PluginBase;

use Abnmt\Theater\Models\Event         as EventModel;
use Abnmt\Theater\Models\Article       as ArticleModel;
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
			'Abnmt\Theater\Components\Theater' => 'theater',
			// 'Abnmt\Theater\Components\Playbill'     => 'theaterPlaybill',
			// 'Abnmt\Theater\Components\Repertoire'   => 'theaterRepertoire',
			// 'Abnmt\Theater\Components\Troupe'       => 'theaterTroupe',
			// 'Abnmt\Theater\Components\News'         => 'theaterNews',
			// 'Abnmt\Theater\Components\Press'        => 'theaterPress',
			// 'Abnmt\Theater\Components\Person'       => 'theaterPerson',
			// 'Abnmt\Theater\Components\Performance'  => 'theaterPerformance',
			// 'Abnmt\Theater\Components\Partners'     => 'theaterPartners',
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

		// $alias = AliasLoader::getInstance();
		// $alias->alias( 'LocalizedCarbon', 'Laravelrus\LocalizedCarbon\LocalizedCarbon' );
		// $alias->alias( 'DiffFormatter'  , 'Laravelrus\LocalizedCarbon\DiffFactoryFacade' );


		/*
		 * Register menu items for the RainLab.Pages plugin
		 */
		Event::listen('pages.menuitem.listTypes', function() {
			return [
				// 'news-category' => 'Новости по категориям',
				// 'performance-category' => 'Спектакли по категориям',
				// 'press-category' => 'Архив прессы',
				// 'playbill-now' => 'Афиша на текущий месяц',
				// 'playbill-next' => 'Афиша на следующие месяцы',
				// 'person-category' => 'Люди по категориям',
			];
		});

		Event::listen('pages.menuitem.getTypeInfo', function($type) {
			// if ($type == 'news-category')
			// 	return NewsCategoryModel::getMenuTypeInfo($type);
			// if ($type == 'performance-category')
			// 	return PerformanceCategoryModel::getMenuTypeInfo($type);
			// if ($type == 'person-category')
			// 	return PersonCategoryModel::getMenuTypeInfo($type);
			// if ($type == 'playbill-now' || $type == 'playbill-next')
			// 	return PlaybillModel::getMenuTypeInfo($type);
			// if ($type == 'press-category')
			//     return PressCategoryModel::getMenuTypeInfo($type);
		});

		Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
			// if ($type == 'news-category')
			// 	return NewsCategoryModel::resolveMenuItem($item, $url, $theme);
			// if ($type == 'performance-category')
			// 	return PerformanceCategoryModel::resolveMenuItem($item, $url, $theme);
			// if ($type == 'person-category')
			// 	return PersonCategoryModel::resolveMenuItem($item, $url, $theme);
			// if ($type == 'playbill-now' || $type == 'playbill-next')
			// 	return PlaybillModel::resolveMenuItem($item, $url, $theme);
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
					// extract(date_parse($datetime));
					// $months = explode(',', Lang::get('abnmt.theater::lang.dates.month_gen', compact('month')));
					// $month = $months[$months[0]];
					// $format = '%1$d %2$s %3$d года';
					// $string = sprintf($format, $day, $month, $year);
					setlocale(LC_ALL, 'Russian');
					$month = \Carbon\Carbon::parse($datetime)->formatLocalized('%B');
					return $month;
				},
				'humanDate' => function ( $datetime ) {
					// setlocale(LC_ALL, 'Russian');
					// return $date = LocalizedCarbon::parse($datetime)->formatLocalized('%e %f %Y года');
					extract(date_parse($datetime));
					$months = explode(',', Lang::get('abnmt.theater::lang.dates.month_gen', compact('month')));
					$month = $months[$months[0]];
					$format = '%1$d %2$s %3$d года';
					$string = sprintf($format, $day, $month, $year);
					return $string;
				},
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


	public function dateLocale($dateString, $dateFormat = '%c') {
		if (class_exists('RainLab\Translate\Behaviors\TranslatableModel')){
			$locale = Translator::instance()->getLocale();
			$locales = [
				'ru' => "ru_RU.UTF-8",
				'en' => "en_US.UTF-8",
			];
			setlocale(LC_ALL, $locales[$locale]);
		} else {
			$locale = 'ru';
			setlocale(LC_ALL, 'ru_RU.UTF-8');
		}

		$dateString = strtotime($dateString);

		if ($locale == 'ru') {
			if ($dateFormat === '%c'){
				$dateFormat = '%e %h %Y г.';
			}
			$months = explode("|", '|января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря');
			$dateFormat = preg_replace("~\%h~", $months[date('n', $dateString)], $dateFormat);

		}

		return strftime($dateFormat, $dateString );
	}

}
