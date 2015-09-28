<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * Background Model
 */
class Background extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_backgrounds';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['meta'];

    /**
     * @var array JSONable fields
     */
    protected $jsonable = ['meta'];

    /**
     * @var array Relations
     */
    public $hasOne        = [];
    public $hasMany       = [];
    public $belongsTo     = [];
    public $belongsToMany = [];
    public $morphTo       = [];
    public $morphOne      = [];
    public $morphMany     = [];
    public $attachOne     = [];
    public $attachMany    = [
        'images' => ['System\Models\File'],
    ];

    public function listImages($keyValue = null, $fieldName = null)
    {
        // CW::info(['Model' => $this->images]);
        return $this->images->lists('file_name', 'file_name');
    }

    public function listQueries($keyValue = null, $fieldName = null)
    {
        return [
            'all'  => 'Все',
            '1920' => '1920',
            '1700' => '1700',
            '1622' => '1622',
            '1440' => '1440',
            '1360' => '1360',
            '1280' => '1280',
        ];
    }

    // public function getWidthOptions()
    // {
    //     return 'none';
    // }

    // public function filterFields($fields, $context = null)
    // {

    //     $fields->width = $context;
    // }

}
