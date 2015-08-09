<?php namespace Abnmt\Theater\Models;

use Model;

use Str;
use URL;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

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
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'participation' => ['Abnmt\Theater\Models\Participation'],
        // 'events'        => ['Abnmt\Theater\Models\Event'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [
        // 'categories' => ['Abnmt\Theater\Models\PerformanceCategory', 'table' => 'abnmt_theater_performances_categories', 'order' => 'title']
    ];

    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [
        'events' => ['Abnmt\Theater\Models\Event', 'name' => 'relation']
    ];
    public $morphToMany = [
        'relation' => ['Abnmt\Theater\Models\Article',
            'table' => 'abnmt_theater_articles_relations',
            'name'  => 'relation',
        ],
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
        'background_mobile' => ['System\Models\File'],
    ];
    public $attachMany = [
        'background'      => ['System\Models\File'],
        'background_flat' => ['System\Models\File'],
        'background_mask' => ['System\Models\File'],
        'featured'        => ['System\Models\File'],
    ];



    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = array(
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
    );


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
            'sort'       => 'created_at',
            // 'categories' => null,
            'search'     => '',
            'published'  => true
        ], $options));

        $searchableFields = ['title', 'slug'];

        if ($published)
            $query->isPublished();

        /*
         * Sorting
         */
        if (!is_array($sort)) $sort = [$sort];
        foreach ($sort as $_sort) {

            if (in_array($_sort, array_keys(self::$allowedSortingOptions))) {
                $parts = explode(' ', $_sort);
                if (count($parts) < 2) array_push($parts, 'desc');
                list($sortField, $sortDirection) = $parts;

                $query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Search
         */
        $search = trim($search);
        if (strlen($search)) {
            $query->searchWhere($search, $searchableFields);
        }

        /*
         * Categories
         */
        // if ($categories !== null) {
        //     if (!is_array($categories)) $categories = [$categories];
        //     $query->whereHas('categories', function($q) use ($categories) {
        //         $q->whereIn('id', $categories);
        //     });
        // }

        return $query->get();
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

    public function beforeCreate()
    {
        // Generate a URL slug for this model
        $this->slug = Str::slug($this->title);
    }
}
