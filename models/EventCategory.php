<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * EventCategory Model
 */
class EventCategory extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_event_categories';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['title', 'slug', 'code', 'description', 'created_at', 'updated_at'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [
        'events' => ['Abnmt\Theater\Models\Event', 'table' => 'abnmt_theater_events_categories', 'order' => 'datetime desc', 'scope' => 'isPublished']
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}