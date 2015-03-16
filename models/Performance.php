<?php namespace Abnmt\Theater\Models;

use Model;

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
    public $morphMany = [];
    public $attachOne = [
        'playbill' => ['System\Models\File'],
        'repertoire' => ['System\Models\File']
    ];
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
            ->where('state', '<>', 'archived')
            ->where('type', '=', 'normal')
        ;
    }
    public function scopeIsChild($query)
    {
        return $query
            ->where('state', '<>', 'archive')
            ->where('type', '=', 'child')
        ;
    }
    public function scopeIsArchive($query)
    {
        return $query
            ->where('state', '=', 'archived')
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