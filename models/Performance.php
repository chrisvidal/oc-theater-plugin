<?php namespace Abnmt\Theater\Models;

use Model;

use Str;
use URL;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

use Abnmt\Theater\Models\Taxonomy as TaxonomyModel;

use CW;
use Carbon;

/**
 * Performance Model
 */
class Performance extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_performances';

    /**
     * @var array JSONable fields
     */
    protected $jsonable = [];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array With fields
     */
    protected $with = ['taxonomy', 'playbill'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'participation' => ['Abnmt\Theater\Models\Participation'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [
        'press' => [
            'Abnmt\TheaterPress\Models\Article',
            'table' => 'abnmt_theaterpress_articles_relations',
            'key' => 'relation_id',
            // 'otherKey' => 'article_id',
        ],
        // 'roles' => [
        //     'Abnmt\Theater\Models\Person',
        //     'table' => 'abnmt_theater_participations',
        //     'pivot' => ['title', 'type', 'group', 'description'],
        // ],
    ];

    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [
        'events' => ['Abnmt\Theater\Models\Event', 'name' => 'relation']
    ];
    public $morphToMany = [
        // 'relation' => ['Abnmt\Theater\Models\Article',
        //     'table' => 'abnmt_theater_articles_relations',
        //     'name'  => 'relation',
        // ],
        'taxonomy' => ['Abnmt\Theater\Models\Taxonomy',
            'table' => 'abnmt_theater_taxonomies_relations',
            'name'  => 'relation',
        ],
    ];
    public $morphedByMany = [];

    public $attachOne = [
        'playbill'          => ['System\Models\File'],
        'playbill_flat'     => ['System\Models\File'],
        'playbill_mask'     => ['System\Models\File'],
        'video'             => ['System\Models\File'],
        'repertoire'        => ['System\Models\File'],
        'cover'             => ['System\Models\File'],
    ];
    public $attachMany = [
        'background'        => ['System\Models\File'],
        'background_flat'   => ['System\Models\File'],
        'background_mask'   => ['System\Models\File'],
        'featured'          => ['System\Models\File'],
    ];



    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = [
        'title asc'          => 'Название (asc)',
        'title desc'         => 'Название (desc)',
        'created_at asc'     => 'Дата создания (asc)',
        'created_at desc'    => 'Дата создания (desc)',
        'updated_at asc'     => 'Дата обновления (asc)',
        'updated_at desc'    => 'Дата обновления (desc)',
        'published_at asc'   => 'Дата публикации (asc)',
        'published_at desc'  => 'Дата публикации (desc)',
        'premiere_date asc'  => 'Дата премьеры (asc)',
        'premiere_date desc' => 'Дата премьеры (desc)',
    ];

    /**
     * The attributes of posts Scopes
     * @var array
     */
    public static $allowedScopingOptions = [
        'Performance' => 'Спектакль',
        'Repertoire'  => 'Репертуар',
    ];


    public function beforeCreate()
    {
        // Generate a URL slug for this model
        $this->slug = Str::slug($this->title);
    }


    /**
     * Scope IsPublished
     */
    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
        ;
    }

    /**
     * Scope Performance
     */
    public function scopePerformance($query)
    {
        return $query
            ->with(['background', 'cover', 'background_flat', 'background_mask', 'featured', 'video', 'participation.person'])
            ->with(
                ['events' => function($q) {
                    $q
                        ->where('event_date', '>=', Carbon::now())
                        ->take(2)
                    ;
                }]
            )
            ->with(
                ['press' => function($q) {
                    $q
                        ->orderBy('published_at', 'desc')
                        ->take(3)
                    ;
                }]
            )
        ;
    }

    /**
     * Scope Repertoire
     */
    public function scopeRepertoire($query)
    {
        return $query
            ->with(['repertoire'])
        ;
    }

    /**
     * Scope byCategories
     */
    public function scopeByCategories($query, $categories)
    {
        if (!is_array($categories)) $categories = array_map('trim', explode(',', $categories));
        return $query->whereHas('taxonomy', function($q) use ($categories) {
            foreach ($categories as $key => $category) {
                if ($key == 0)
                    $q->where('slug', '=', $category);
                else
                    $q->orWhere('slug', '=', $category);
            }
        });
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
            'sort'       => 'premiere_date desc',
            'categories' => null,
            'search'     => null,
            'scopes'     => null,
            'published'  => true,
        ], $options));

        $searchableFields = ['title', 'slug'];

        if (isset($published) && $published)
            $query->isPublished();

        /*
         * With
         */
        if (isset($scopes)) {
            if (!is_array($scopes)) $scopes = array_map('trim', explode(',', $scopes));
            foreach ($scopes as $scope) {
                switch ($scope) {
                    case 'Performance':
                        $query->Performance();
                        break;
                    case 'Repertoire':
                        $query->Repertoire();
                        break;

                    default:
                        $query->with($scope);
                        break;
                }
            }
        }

        /*
         * Sorting
         */
        if (isset($sort)) {
            if (!is_array($sort)) $sort = array_map('trim', explode(',', $sort));
            foreach ($sort as $_sort) {
                if (in_array($_sort, array_keys(self::$allowedSortingOptions))) {
                    $parts = explode(' ', $_sort);
                    if (count($parts) < 2) array_push($parts, 'desc');
                    list($sortField, $sortDirection) = $parts;

                    $query->orderBy($sortField, $sortDirection);
                }
            }
        }

        /*
         * Search
         */
        if (isset($search)) {
            $search = trim($search);
            if (strlen($search)) {
                $query->searchWhere($search, $searchableFields);
            }
        }

        /*
         * Categories
         */
        if (isset($categories)) {
            $query->ByCategories($categories);
        }

        return $query->get();
    }

    /**
     * Lists posts for the front end
     * @param  array $options Display options
     * @return self
     */
    public function scopeLoadPost($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'sort'       => 'published_at desc',
            'categories' => null,
            'search'     => null,
            'with'       => null,
            'published'  => true,
            'slug'       => null,
        ], $options));

        if (isset($published) && $published)
            $query->isPublished();

        if (isset($slug))
            $query->where('slug', '=', $slug);

        return $query->first();
    }


    /**
     * Dropdown options
     */
    public function getDropdownOptions($fieldName = null, $keyValue = null)
    {
        if ($fieldName == 'entracte') {
            return [
                0 => 'Без антракта',
                1 => 'С одним антрактом',
                2 => 'С двумя антрактами',
            ];
        } elseif ($fieldName == 'rate') {
            return [
                '0+'  => '0+',
                '6+'  => '6+',
                '12+' => '12+',
                '16+' => '16+',
                '18+' => '18+',
            ];
        } else {
            return ['' => '—'];
        }

    }


    public static function getCategories()
    {
        $categories = TaxonomyModel::where('model', get_class())->select('id', 'title', 'slug')->get();

        // CW::info($categories);
        return $categories;
    }

    /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'repertoire') {

            // Сюда все возможные ссылки (категории или scoupe)
            $references = [];
            $categories = self::getCategories();
            foreach ($categories as $category) {
                $references[$category->slug] = $category->title;
            }

            $result = [
                'references'   => $references,
                'nesting'      => false,
                'dynamicItems' => false
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('theater'))
                    continue;

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

        // CW::info($item);

        if ($item->type == 'repertoire') {
            if (!$item->reference || !$item->cmsPage)
                return;

            $category = [
                'slug' => $item->reference,
            ];

            $posts = self::ByCategories($item->reference)
                ->select('title', 'slug')
                ->get();
            ;

            // CW::info($posts);

            $posts->each(function ($post) {
                $post->url = CmsPage::url('theater/performance', ['slug' => $post->slug]);
            });
            $postUrls = $posts->lists('url', 'slug');


            $pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl)
                return;

            $pageUrl = URL::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url || in_array($url, $postUrls);
            // $result['mtime'] = $category->updated_at;
        }
        // CW::info($result);
        return $result;
    }

    /**
     * Returns URL of a category page.
     */
    protected static function getCategoryPageUrl($pageCode, $category, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return;

        $paramName = 'category';
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $category['slug']]);

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
