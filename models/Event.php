<?php namespace Abnmt\Theater\Models;

use Model;

use Str;

/**
 * Event Model
 */
class Event extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_events';

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
    public $morphTo = [
        // 'relation' => [],
    ];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = array(
        'event_date desc'    => 'По дате (вниз)',
        'event_date asc'     => 'По дате (вверх)',
    );

    // public function beforeCreate()
    // {
    //     // Generate a URL slug for this model
    //     $this->slug = Str::slug($this->title);
    // }

}