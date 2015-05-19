<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * Press Model
 */
class Press extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_press';

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
    public $morphOne = [];
    public $attachOne = [];
    public $attachMany = [];
    public $morphToMany = [
        'categories'    => ['Abnmt\Theater\Models\Category',
            'name'  => 'object',
            'table' => 'abnmt_theater_object_categories',
            'order' => 'name',
        ],
    ];
    public $morphedByMany = [
        'performances' => ['Abnmt\Theater\Models\Performance',
            'name' => 'relation',
            'table' => 'abnmt_theater_press_relations',
        ],
        'persons' => ['Abnmt\Theater\Models\Person',
            'name' => 'relation',
            'table' => 'abnmt_theater_press_relations',
        ],
    ];


    /**
     * Scopes
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
            'id' => $this->id,
            'slug' => $this->slug,
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }

}