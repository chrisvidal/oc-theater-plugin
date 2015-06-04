<?php namespace Abnmt\Theater\Models;

use Model;

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
    protected $fillable = [];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['datetime'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        "performance" => ['Abnmt\Theater\Models\Performance'],
        "playbill"    => ['Abnmt\Theater\Models\Playbill'],
    ];
    public $belongsToMany = [
        'categories' => ['Abnmt\Theater\Models\EventCategory', 'table' => 'abnmt_theater_events_categories', 'order' => 'title']
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];



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
        'datetime asc'      => 'Datetime (ascending)',
        'datetime desc'     => 'Datetime (descending)',
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
            'categories' => null,
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
        if ($categories !== null) {
            if (!is_array($categories)) $categories = [$categories];
            $query->whereHas('playbill', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        return $query->get();
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