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
    public $table = 'abnmt_theater_participations';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['title', 'performance', 'person', 'type', 'group', 'description', 'sort_order'];

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