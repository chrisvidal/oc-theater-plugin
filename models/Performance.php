<?php namespace Abnmt\Theater\Models;

use Model;

/**
 * Performance Model
 */
class Performance extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_performances';

    /**
     * @var array JSONable fields
     */
    protected $jsonable = [];

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
    public $hasMany = [
        'participation' => ['Abnmt\Theater\Models\Participation'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [];

    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $morphToMany = [
        'press' => ['Abnmt\Theater\Models\Press',
            'table' => 'abnmt_theater_press_relations',
            'name' => 'relation',
        ],
    ];
    public $morphedByMany = [];

    public $attachOne = [
        'playbill' => ['System\Models\File'],
        'video' => ['System\Models\File'],
        'repertoire' => ['System\Models\File'],
    ];
    public $attachMany = [
        'background' => ['System\Models\File'],
        'featured' => ['System\Models\File'],
    ];


    /**
     * SCOPES
     */

    /**
     * Scope IsPublished
     */
    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
        ;
    }

    /**
     * Scope IsNormal
     */
    public function scopeIsNormal($query)
    {
        return $query
            ->where('state', '<>', 'archived')
            ->where('type', '=', 'normal')
        ;
    }

    /**
     * Scope IsChild
     */
    public function scopeIsChild($query)
    {
        return $query
            ->where('state', '<>', 'archive')
            ->where('type', '=', 'child')
        ;
    }

    /**
     * Scope IsArchive
     */
    public function scopeIsArchive($query)
    {
        return $query
            ->where('state', '=', 'archived')
        ;
    }

    /**
     * Scope GetList
     */
    public function scopeGetList($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'sort'    => ['premiere_date', 'desc'],
            'section' => 'normal',
        ], $options));

        $query = $query->isPublished();

        switch ($section) {
            case 'child':
                $query = $query->isChild();
                break;
            case 'archive':
                $query = $query->isArchive();
                break;
            case 'normal':
            default:
                $query = $query->isNormal();
                break;
        }

        return $query = $query
            ->with(['repertoire'])
            ->orderBy($sort[0], $sort[1])
        ;

    }

    /**
     * Scope GetSingle
     */
    public function scopeGetSingle($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([], $options));

        return $query
            ->isPublished()
            ->with(['participation.person', 'press', 'video', 'background', 'featured'])
            ->where('slug', '=', $slug)
        ;

    }




    /**
     * Dropdown options
     */
    public function getDropdownOptions($fieldName = null, $keyValue = null)
    {
        if ($fieldName == 'entracte') {
            return [
                0 => 'Без антракта',
                1 => 'С одним антрактом',
                2 => 'С двумя антрактами',
            ];
        } elseif ($fieldName == 'state') {
            return [
                'normal'   => 'Обычное',
                'premiere' => 'Премьера',
                'archived' => 'В архиве',
            ];
        } elseif ($fieldName == 'type') {
            return [
                'normal' => 'Обычный',
                'child'  => 'Детский',
                'event'  => 'Событие',
            ];
        } elseif ($fieldName == 'rate') {
            return [
                0  => '0+',
                6  => '6+',
                12 => '12+',
                16 => '16+',
                18 => '18+',
            ];
        } else {
            return ['' => '—'];
        }

    }

    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id'   => $this->id,
            'slug' => $this->slug,
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }

}
