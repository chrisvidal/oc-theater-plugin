<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * PeopleTermGroup Model
 */
class PeopleTermGroup extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_people_term_groups';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['title', 'slug', 'description'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'peopleterms' => ['Abnmt\Theater\Models\PeopleTerm'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}