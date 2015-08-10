<?php namespace Abnmt\Theater\Models;

use Model;

use Str;

/**
 * Article Model
 */
class Article extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_articles';

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
    protected $with = ['taxonomy'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [
        'events' => ['Abnmt\Theater\Models\Event', 'name' => 'relation']
    ];
    public $morphToMany = [
        'taxonomy' => ['Abnmt\Theater\Models\Taxonomy',
            'table' => 'abnmt_theater_taxonomies_relations',
            'name'  => 'relation',
        ],
    ];
    public $attachOne = [];
    public $attachMany = [];


    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = [
        'published_at desc'  => 'По дате публикации (вниз)',
        'published_at asc'   => 'По дате публикации (вверх)',
    ];

    /**
     * The attributes of posts Scopes
     * @var array
     */
    public static $allowedScopingOptions = [
        'News'    => 'Новости',
        'Press'   => 'Пресса',
        'Journal' => 'Журнал',
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
            'sort'       => 'published_at desc',
            'categories' => null,
            'search'     => null,
            'with'       => null,
            'published'  => true,
            'page'       => 1,
            'perPage'    => 10,
        ], $options));

        $searchableFields = ['title', 'slug'];

        if (isset($published) && $published)
            $query->isPublished();

        /*
         * With
         */
        if (isset($with)) {
            if (!is_array($with)) $with = array_map('trim', explode(',', $with));
            foreach ($with as $_with) {
                switch ($_with) {
                    case 'News':
                    case 'Press':
                    case 'Journal':
                        break;
                    default:
                        $query->with($_with);
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
            if (!is_array($categories)) $categories = array_map('trim', explode(',', $categories));
            $query->whereHas('taxonomy', function($q) use ($categories) {
                foreach ($categories as $key => $category) {
                    if ($key == 0)
                        $q->where('slug', '=', $category);
                    else
                        $q->orWhere('slug', '=', $category);
                }
            });
        }

        return $query->paginate($perPage, $page);
        // return $query->get();
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