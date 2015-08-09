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
    public static $allowedSortingOptions = array(
        'published_at desc'  => 'По дате публикации (вниз)',
        'published_at asc'   => 'По дате публикации (вверх)',
    );

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