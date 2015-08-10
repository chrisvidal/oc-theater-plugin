<?php namespace Abnmt\Theater\Models;

use Model;

use Str;

use \Carbon\Carbon as Carbon;

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
     * @var array With fields
     */
    protected $with = ['relation'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [
        'relation' => [],
    ];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = [
        'event_date asc'     => 'По дате (вверх)',
        'event_date desc'    => 'По дате (вниз)',
    ];

    /**
     * The attributes of posts Scopes
     * @var array
     */
    public static $allowedScopingOptions = [
        'Now'      => 'Афиша на текущий месяц',
        'Next'     => 'Афиша на следующий месяц',
        'Playbill' => 'Афиша (:date)',
        'Calendar' => 'Календарь',
    ];

    // public function beforeCreate()
    // {
    //     // Generate a URL slug for this model
    //     $this->slug = Str::slug($this->title);
    // }

    /**
     * Scope Playbill
     */
    public function scopePlaybill($query, $date = 'now')
    {
        switch ($date) {
            case 'now':
                $query->Now();
                break;
            case 'next':
                $query->Next();
                break;
            default:
                $query->Date($date);
                break;
        }
        return $query;
    }

    /**
     * Scope Now
     */
    public function scopeNow($query)
    {
        return $query
            ->where('event_date', '>=', Carbon::now()->startOfMonth())
            ->where('event_date', '<', Carbon::now()->startOfMonth()->addMonth())
        ;
    }

    /**
     * Scope Next
     */
    public function scopeNext($query)
    {
        return $query
            ->where('event_date', '>=', Carbon::now()->startOfMonth()->addMonths(1))
            ->where('event_date', '<', Carbon::now()->startOfMonth()->addMonths(2))
        ;
    }

    /**
     * Scope Date
     */
    public function scopeDate($query, $date)
    {
        return $query
            ->where('event_date', '>=', Carbon::parse($date)->startOfMonth())
            ->where('event_date', '<', Carbon::parse($date)->startOfMonth()->addMonths(1))
        ;
    }

    /**
     * Scope Calendar
     */
    public function scopeCalendar($query)
    {
        return $query
            ->where('event_date', '>=', Carbon::now()->subDays(3))
        ;
    }

    /**
     * Lists posts for the front end
     * @param  array $options Display options
     * @return self
     */
    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'sort'       => 'event_date asc',
            'scopes'     => null,
        ], $options));

        /*
         * With
         */
        if (isset($scopes)) {
            if (!is_array($scopes)) $scopes = array_map('trim', explode(',', $scopes));
            foreach ($scopes as $scope) {
                switch ($scope) {
                    case 'Now':
                        $query->Now();
                        break;
                    case 'Next':
                        $query->Next();
                        break;
                    case 'Playbill':
                        $query->Playbill($date);
                        break;
                    case 'Calendar':
                        $query->Calendar();
                        break;
                    default:
                        // $query->with($scope);
                        break;
                }
            }
        }

        /*
         * Sorting
         */
        if (isset($sort)) {
            if (!is_array($sort)) $sort = array_map('trim', explode(',', $sort));
            foreach ($sort as $_sort) {
                if (in_array($_sort, array_keys(self::$allowedSortingOptions))) {
                    $parts = explode(' ', $_sort);
                    if (count($parts) < 2) array_push($parts, 'desc');
                    list($sortField, $sortDirection) = $parts;

                    $query->orderBy($sortField, $sortDirection);
                }
            }
        }

        return $query->get();
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