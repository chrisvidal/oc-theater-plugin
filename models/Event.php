<?php namespace Abnmt\Theater\Models;

use Abnmt\Theater\Plugin as Plugin;
use Carbon;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use Model;
use Str;

/**
 * Event Model
 */
class Event extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_events';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['title', 'description', 'event_date', 'bileter_id'];

    /**
     * @var array With fields
     */
    protected $with = ['relation'];

    /**
     * @var array Relations
     */
    public $hasOne        = [];
    public $hasMany       = [];
    public $belongsTo     = [];
    public $belongsToMany = [];
    public $morphTo       = [
        'relation' => [],
    ];
    public $morphOne   = [];
    public $morphMany  = [];
    public $attachOne  = [];
    public $attachMany = [];

    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = [
        'event_date asc'  => 'По дате (вверх)',
        'event_date desc' => 'По дате (вниз)',
    ];

    /**
     * The attributes of posts Scopes
     * @var array
     */
    public static $allowedScopingOptions = [
        'Now'      => 'Афиша на текущий месяц',
        'Next'     => 'Афиша на следующий месяц',
        'Playbill' => 'Афиша (:date)',
        'Calendar' => 'Календарь',
        'Current'  => 'Афиша на крайние месяцы',
    ];

    /**
     * The attributes of posts Menus
     * @var array
     */
    public static $allowedMenuOptions = [
        'auto' => 'Афиша на крайние месяцы',
        'now'  => 'Афиша на текущий месяц',
        'next' => 'Афиша на следующий месяц',
    ];

    // public function beforeCreate()
    // {
    //     // Generate a URL slug for this model
    //     $this->slug = Str::slug($this->title);
    // }

    /**
     * Scope Playbill
     */
    public function scopePlaybill($query, $date = 'now')
    {
        switch ($date) {
            case 'now':
                $query->Now();
                break;
            case 'next':
                $query->Next();
                break;
            default:
                $query->Date($date);
                break;
        }
        return $query;
    }

    /**
     * Scope Now
     */
    public function scopeNow($query)
    {
        return $query
            ->where('event_date', '>=', Carbon::now()->startOfMonth())
            ->where('event_date', '<', Carbon::now()->startOfMonth()->addMonth())
        ;
    }

    /**
     * Scope Next
     */
    public function scopeNext($query)
    {
        return $query
            ->where('event_date', '>=', Carbon::now()->startOfMonth()->addMonths(1))
            ->where('event_date', '<', Carbon::now()->startOfMonth()->addMonths(2))
        ;
    }

    /**
     * Scope Date
     */
    public function scopeDate($query, $date)
    {
        return $query
            ->where('event_date', '>=', Carbon::parse($date)->startOfMonth())
            ->where('event_date', '<', Carbon::parse($date)->startOfMonth()->addMonths(1))
        ;
    }

    /**
     * Scope Calendar
     */
    public function scopeCalendar($query)
    {
        return $query
            ->where('event_date', '>=', Carbon::now()->subDays(3))
            // ->take(10)
        ;
    }

    /**
     * Lists posts for the front end
     * @param  array $options Display options
     * @return self
     */
    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'sort'   => 'event_date asc',
            'scopes' => null,
        ], $options));

        /*
         * With
         */
        if (isset($scopes)) {
            if (!is_array($scopes)) {
                $scopes = array_map('trim', explode(',', $scopes));
            }

            foreach ($scopes as $scope) {
                switch ($scope) {
                    case 'Now':
                        $query->Now();
                        break;
                    case 'Next':
                        $query->Next();
                        break;
                    case 'Playbill':
                        $query->Playbill($date);
                        break;
                    case 'Calendar':
                        $query->Calendar();
                        break;
                    default:
                        // $query->with($scope);
                        break;
                }
            }
        }

        /*
         * Sorting
         */
        if (isset($sort)) {
            if (!is_array($sort)) {
                $sort = array_map('trim', explode(',', $sort));
            }

            foreach ($sort as $_sort) {
                if (in_array($_sort, array_keys(self::$allowedSortingOptions))) {
                    $parts = explode(' ', $_sort);
                    if (count($parts) < 2) {
                        array_push($parts, 'desc');
                    }

                    list($sortField, $sortDirection) = $parts;

                    $query->orderBy($sortField, $sortDirection);
                }
            }
        }

        return $query->get();
    }

    /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'playbill') {
            $result = [
                'dynamicItems' => true,
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages    = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('theaterEvents')) {
                    continue;
                }

                /*
                 * Component must use a category filter with a routing parameter
                 * eg: categoryFilter = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('theaterEvents');
                if (!isset($properties['date']) || !preg_match('/{{\s*:/', $properties['date'])) {
                    continue;
                }

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {

        $result = null;

        if ($item->type == 'playbill') {

            $result = [
                'items' => [],
            ];

            $date         = Carbon::parse('now');
            $lastDateInDb = Carbon::parse(self::max('event_date'));

            // CW::info(['last' => $lastDateInDb]);

            if (preg_match('~/afisha/\d+-\d+~', $url)) {
                $slug      = explode('/', $url);
                $slug_date = Carbon::parse(array_pop($slug))->startOfMonth();
                // CW::info(['slug' => $slug]);
                if ($slug_date->lt($date->startOfMonth())) {
                    $result['items'][] = [
                        'title'    => Plugin::dateLocale($slug_date->format('Y-m'), '%B %Y'),
                        'isActive' => true,
                    ];
                }
            }

            $i      = 1;
            $limit  = 3;
            $counts = self::Date($date->format('Y-m'))->count();

            while ($i <= $limit) {

                // CW::info(['date' => $date, 'counts' => $counts]);

                $reference = [
                    'title' => Plugin::dateLocale($date->format('Y-m'), '%B'),
                    'url'   => self::getReferencePageUrl($item->cmsPage, $date->format('Y-m'), $theme),
                ];

                $reference['isActive'] = $reference['url'] == $url;

                if ($counts > 0) {
                    $result['items'][] = $reference;
                    $i++;
                }

                $date   = $date->addMonth();
                $counts = self::Date($date->format('Y-m'))->count();

                if ($date->gt($lastDateInDb)) {
                    break;
                }

            }

        }
        // CW::info(['url' => $url]);
        // CW::info(['result' => $result]);

        return $result;
    }

    /**
     * Returns URL of a referenced page.
     */
    protected static function getReferencePageUrl($pageCode, $slug, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) {
            return;
        }

        $properties = $page->getComponentProperties('theaterEvents');
        if (!isset($properties['date'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the category filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['date'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        $url       = CmsPage::url($page->getBaseFileName(), [$paramName => $slug]);

        return $url;
    }

    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id'   => $this->id,
            'slug' => $this->slug,
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }
}
