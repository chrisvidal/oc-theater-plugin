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
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    // public $morphedByMany = [
    //     'performance' => ['Abnmt\Theater\Models\Performance', 'name' => 'press_relation'],
    //     'person' => ['Abnmt\Theater\Models\Person', 'name' => 'press_relation'],
    // ];

}