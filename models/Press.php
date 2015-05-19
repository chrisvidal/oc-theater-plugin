<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * Press Model
 */
class Press extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_press';

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
    // public $morphTo = [
    //     'relations' => ['table' => 'abnmt_theater_press_relations'],
    // ];
    public $morphOne = [];
    // public $morphMany = [
    //     'relations' => ['table' => 'abnmt_theater_press_relations'],
    //     // 'performance' => ['Abnmt\Theater\Models\Performance', 'name' => 'press_relation'],
    //     // 'person' => ['Abnmt\Theater\Models\Person', 'name' => 'press_relation'],
    // ];
    public $attachOne = [];
    public $attachMany = [];



    // public $morphToMany = [
    //     // 'relations' => ['table' => 'abnmt_theater_press_relations'],
    //     // 'performance' => ['Abnmt\Theater\Models\Performance', 'name' => 'press_relation'],
    //     // 'person' => ['Abnmt\Theater\Models\Person', 'name' => 'press_relation'],
    // ];
    // public $morphedByMany = [
    //     'performances' => ['Abnmt\Theater\Models\Performance', 'table' => 'abnmt_theater_press_relations', 'name' => 'relation'],
    //     'persons' => ['Abnmt\Theater\Models\Person', 'table' => 'abnmt_theater_press_relations', 'name' => 'relation'],
    // ];
    // public $morphedByMany = [
    //     // 'relations' => ['table' => 'abnmt_theater_press_relations'],
    // ];

    // public $morphMany = [
    //     // 'relations' => ['table' => 'abnmt_theater_press_relations'],
    //     'performances' => ['Abnmt\Theater\Models\Performance',
    //         'table' => 'abnmt_theater_press_relations',
    //         'name' => 'press_relation'
    //     ],
    //     'persons' => ['Abnmt\Theater\Models\Person',
    //         'table' => 'abnmt_theater_press_relations',
    //         'name' => 'press_relation'
    //     ],
    // ];

    public $morphToMany = [
        'categories'    => ['Abnmt\Theater\Models\Category',
            'name'  => 'object',
            'table' => 'abnmt_theater_object_categories',
            'order' => 'name',
        ],
    ];
    public $morphedByMany = [
        'performances' => ['Abnmt\Theater\Models\Performance',
            'name' => 'relation',
            'table' => 'abnmt_theater_press_relations',
        ],
        'persons' => ['Abnmt\Theater\Models\Person',
            'name' => 'relation',
            'table' => 'abnmt_theater_press_relations',
        ],
    ];

}