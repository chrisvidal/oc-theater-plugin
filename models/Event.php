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
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        "performance" => ['Abnmt\Theater\Models\Performance']
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
        ;
    }
    public function scopeIsNormal($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
            ->where('state', '<>', 'archive')
            ->where('type', '=', 'normal')
        ;
    }
    public function scopeIsChild($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
            ->where('state', '<>', 'archive')
            ->where('type', '=', 'child')
        ;
    }
    public function scopeIsArchive($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
            ->where('state', '=', 'archive')
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