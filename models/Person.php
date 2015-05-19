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

    // protected $jsonable = ['roles'];

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
        'participations' => ['Abnmt\Theater\Models\Participation']
    ];
    public $belongsTo = [];
    public $belongsToMany = [
        // 'presses' => ['Abnmt\Theater\Models\Press',
        //     'table' => 'abnmt_theater_press_relations',
        //     'name' => 'relation',
        // ],
    ];
    public $morphTo = [];
    public $morphOne = [];
    // public $morphMany = [
    //     // 'press' => ['Abnmt\Theater\Models\Press', 'name' => 'press_relation']
    // ];
    public $attachOne = [
        'portrait' => ['System\Models\File'],
    ];
    public $attachMany = [];


    public $morphToMany = [
        'presses' => ['Abnmt\Theater\Models\Press',
            'table' => 'abnmt_theater_press_relations',
            'name' => 'relation',
        ],
    ];



    public function relationExtendQuery($query, $field, $manageMode=null)
    {
        return $query->where('type', '=', 'roles');
    }

    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
        ;
    }
    public function scopeIsGrade($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
            ->whereNotNull('grade')
            ->where('state', '<>', 'director')
            ->where('state', '<>', 'cooperate')
            ->where('state', '<>', 'not')
        ;
    }
    public function scopeIsState($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
            ->where('grade', '=', NULL)
            ->where('state', '<>', 'director')
            ->where('state', '<>', 'cooperate')
            ->where('state', '<>', 'not')
        ;
    }
    public function scopeIsCooperate($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
            ->where('state', '=', 'cooperate')
        ;
    }
    public function scopeIsDirector($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
            ->where('state', '=', 'director')
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


    public function beforeSave()
    {
        $this->title = $this->given_name . " " . $this->family_name;
    }

}