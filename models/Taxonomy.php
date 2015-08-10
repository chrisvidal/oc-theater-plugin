<?php namespace Abnmt\Theater\Models;

use Model;

use Str;

/**
 * Taxonomy Model
 */
class Taxonomy extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_taxonomies';

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
    public $morphedByMany = [
        'relations' => ['Abnmt\Theater\Models\Taxonomy',
            'table' => 'abnmt_theater_taxonomies_relations',
            'name'  => 'relation',
        ],
    ];
    public $attachOne = [];
    public $attachMany = [];

    public function beforeCreate()
    {
        // Generate a URL slug for this model
        // $this->slug = Str::slug($this->title);
    }



    public function scopeListTaxonomies($query, $model)
    {
        $query->where('model', $model);
        return $query->get();
    }

}