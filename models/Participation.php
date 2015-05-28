<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * Participation Model
 */
class Participation extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_participation';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['title', 'performance', 'person'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'performance' => ['Abnmt\Theater\Models\Performance'],
        'person'      => ['Abnmt\Theater\Models\Person'],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}