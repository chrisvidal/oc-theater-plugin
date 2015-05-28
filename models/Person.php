<?php namespace Abnmt\Theater\Models;

use Model;

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
    public $hasOne = [
        'terms' => ['Abnmt\Theater\Models\PeopleTerm'],
    ];
    public $hasMany = [
        'participation' => ['Abnmt\Theater\Models\Participation'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [];

    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $morphToMany = [
        'press' => ['Abnmt\Theater\Models\Press',
            'table' => 'abnmt_theater_press_relations',
            'name'  => 'relation',
        ],
    ];
    public $morphedByMany = [];

    public $attachOne = [
        'portrait' => ['System\Models\File'],
    ];
    public $attachMany = [];




    /**
     * SCOPES
     */

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
     * Scope IsGrade
     */
    public function scopeIsGrade($query)
    {
        return $query
            ->whereNotNull('grade')
        ;
    }

    /**
     * Scope IsState
     */
    public function scopeIsState($query)
    {
        return $query
            ->where('state', '=', 'state')
        ;
    }

    /**
     * Scope IsCooperate
     */
    public function scopeIsCooperate($query)
    {
        return $query
            ->where('state', '=', 'cooperate')
        ;
    }

    /**
     * Scope IsAdministration
     */
    public function scopeIsAdministration($query)
    {
        return $query
            ->where('state', '=', 'administration')
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

    /**
     * Make title from given and family names
     */
    public function beforeSave()
    {
        $this->title = $this->given_name . " " . $this->family_name;
    }

}
