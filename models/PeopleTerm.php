<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * PeopleTerm Model
 */
class PeopleTerm extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_people_terms';

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
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}