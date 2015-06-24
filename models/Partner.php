<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * Partner Model
 */
class Partner extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_partners';

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

}