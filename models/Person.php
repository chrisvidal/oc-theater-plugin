<?php namespace Abnmt\Theater\Models;

use Model;

use Str;
use URL;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

/**
 * Person Model
 */
class Person extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_people';

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
        // 'participation' => ['Abnmt\Theater\Models\Participation'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [
        // 'categories' => ['Abnmt\Theater\Models\PersonCategory', 'table' => 'abnmt_theater_persons_categories', 'order' => 'title']
    ];

    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
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
        'portrait' => ['System\Models\File'],
    ];
    public $attachMany = [
        'featured' => ['System\Models\File'],
    ];


    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = array(
        'title asc'         => 'Title (ascending)',
        'title desc'        => 'Title (descending)',
        'created_at asc'    => 'Created (ascending)',
        'created_at desc'   => 'Created (descending)',
        'updated_at asc'    => 'Updated (ascending)',
        'updated_at desc'   => 'Updated (descending)',
        'published_at asc'  => 'Published (ascending)',
        'published_at desc' => 'Published (descending)',
        'family_name asc'   => 'Family name (ascending)',
        'family_name desc'  => 'Family name (descending)',
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
     * Scope IsGrade
     */
    // public function scopeIsGrade($query)
    // {
    //     return $query
    //         ->whereNotNull('grade')
    //     ;
    // }

    /**
     * Scope IsState
     */
    // public function scopeIsState($query)
    // {
    //     return $query
    //         ->where('state', '=', 'state')
    //     ;
    // }

    /**
     * Scope IsCooperate
     */
    // public function scopeIsCooperate($query)
    // {
    //     return $query
    //         ->where('state', '=', 'cooperate')
    //     ;
    // }

    /**
     * Scope IsAdministration
     */
    // public function scopeIsAdministration($query)
    // {
    //     return $query
    //         ->where('state', '=', 'administration')
    //     ;
    // }



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

        // Make title from given and family names
        $this->title = $this->given_name . " " . $this->family_name;

        // Generate a URL slug for this model
        $this->slug = Str::slug($this->title);
    }

}
