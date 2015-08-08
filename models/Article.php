<?php namespace Abnmt\Theater\Models;

use Model;

use Str;

/**
 * Article Model
 */
class Article extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_articles';

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
    public $morphMany = [
        'events' => ['Abnmt\Theater\Models\Event', 'name' => 'relation']
    ];
    public $morphToMany = [
        'taxonomy' => ['Abnmt\Theater\Models\Taxonomy',
            'table' => 'abnmt_theater_taxonomies_relations',
            'name'  => 'relation',
        ],
    ];
    public $attachOne = [];
    public $attachMany = [];

    public function beforeCreate()
    {
        // Generate a URL slug for this model
        $this->slug = Str::slug($this->title);
    }

}