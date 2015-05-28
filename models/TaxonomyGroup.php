<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * TaxonomyGroup Model
 */
class TaxonomyGroup extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_taxonomy_groups';

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
        'taxonomies' => ['Abnmt\Theater\Models\Taxonomy']
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}