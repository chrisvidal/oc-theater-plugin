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

    // protected $jsonable = ['authors', 'roles'];

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
        'participations' => ['Abnmt\Theater\Models\Participation'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [
    ];
    public $attachOne = [
        'playbill' => ['System\Models\File'],
    ];
    public $attachMany = [
        'repertoire' => ['System\Models\File'],
        'background' => ['System\Models\File'],
        'featured' => ['System\Models\File'],
    ];

    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', '=', 1)
        ;
    }
    public function scopeIsNormal($query)
    {
        return $query
            ->where('state', '<>', 'archived')
            ->where('type', '=', 'normal')
        ;
    }
    public function scopeIsChild($query)
    {
        return $query
            ->where('state', '<>', 'archive')
            ->where('type', '=', 'child')
        ;
    }
    public function scopeIsArchive($query)
    {
        return $query
            ->where('state', '=', 'archived')
        ;
    }

    public function getDropdownOptions($fieldName = null, $keyValue = null)
    {
        if ($fieldName == 'entracte')
            return [
                0 => 'Без антракта',
                1 => 'С одним антрактом',
                2 => 'С двумя антрактами',
            ];
        elseif ($fieldName == 'state')
            return [
                'normal'   => 'Обычное',
                'premiere' => 'Премьера',
                'archived' => 'В архиве',
            ];
        elseif ($fieldName == 'type')
            return [
                'normal' => 'Обычный',
                'child'  => 'Детский',
                'event'  => 'Событие',
            ];
        elseif ($fieldName == 'rate')
            return [
                0  => '0+',
                6  => '6+',
                12 => '12+',
                16 => '16+',
                18 => '18+',
            ];
        else
            return ['' => '—'];
    }


    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id' => $this->id,
            'slug' => $this->slug,
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }

}