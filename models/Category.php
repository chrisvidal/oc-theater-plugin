<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * Category Model
 */
class Category extends Model
{

    use \October\Rain\Database\Traits\Sluggable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_categories';

    /**
     * @var No timestamps needed here
     */
    // public $timestamps = false;

    /**
     * @var Sluggable fields
     */
    public $slugs = ['slug' => 'name'];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    public $rules = [
        'name' => 'required',
    ];

    /**
     * @var array Relations
     */
    // public $hasOne = [];
    // public $hasMany = [];
    // public $belongsTo = [];
    // public $belongsToMany = [];
    // public $morphTo = [];
    // public $morphOne = [];
    // public $morphMany = [];
    // public $attachOne = [];
    // public $attachMany = [];

    public $morphMany = [
        'objects' => ['table' => 'abnmt_theater_object_categories'],
    ];

}