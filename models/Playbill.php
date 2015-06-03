<?php namespace Abnmt\Theater\Models;

use Model;


// use Laravelrus\LocalizedCarbon\LocalizedCarbon as LocalizedCarbon;
/**
 * Playbill Model
 */
class Playbill extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_playbills';

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




    /**
     * Make title from given and family names
     */
    // public function beforeSave()
    // {
    //     setlocale(LC_ALL, 'Russian');
    //     $month = LocalizedCarbon::parse($this->date)->formatLocalized('%B');
    //     $year  = LocalizedCarbon::parse($this->date)->format('Y');
    //     $this->title = $month . ' ' . $year;
    // }

}