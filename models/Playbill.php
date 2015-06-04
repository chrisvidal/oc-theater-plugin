<?php namespace Abnmt\Theater\Models;

use Str;
use Model;
use URL;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

setlocale(LC_ALL, 'Russian');
use Laravelrus\LocalizedCarbon\LocalizedCarbon as Carbon;
// use \Carbon\Carbon as Carbon;




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
    public $hasMany = [
        'events' => ['Abnmt\Theater\Models\Event'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

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
     * Make title from given and family names
     */
    // public function beforeSave()
    // {
    //     setlocale(LC_ALL, 'Russian');
    //     $month = LocalizedCarbon::parse($this->date)->formatLocalized('%B');
    //     $year  = LocalizedCarbon::parse($this->date)->format('Y');
    //     $this->title = $month . ' ' . $year;
    // }




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

    /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'playbill-now') {

            $references = [];
            // $categories = self::orderBy('title')->get();

            $categories = self::isPublished()
                ->where('date', '>=', Carbon::now()->startOfMonth())
                ->get()
            ;

            foreach ($categories as $category) {
                $references[$category->id] = Carbon::parse($category->date)->formatLocalized('%B');
            }

            $result = [
                'references'   => $references,
                'nesting'      => false,
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('theaterPlaybill'))
                    continue;

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;

        if ($item->type == 'playbill-now') {
            $result = [
                'items' => []
            ];

            $categories = self::isPublished()
                ->where('date', '>=', Carbon::now()->startOfMonth()->subMonth(2))
                ->get()
            ;

            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => mb_convert_encoding( Carbon::parse($category->date)->formatLocalized('%B'), "UTF-8", "CP1251" ),
                    'url'   => self::getCategoryPageUrl($item->cmsPage, $category, $theme),
                    'mtime' => $category->updated_at,
                ];

                $categoryItem['isActive'] = $categoryItem['url'] == $url;

                $result['items'][] = $categoryItem;
            }
        }

        return $result;
    }

    /**
     * Returns URL of a category page.
     */
    protected static function getCategoryPageUrl($pageCode, $category, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return;

        $slug = Carbon::now()->startOfMonth()->format('Y-m') == Carbon::parse($category->date)->format('Y-m') ? 'now' : Carbon::parse($category->date)->format('Y-m');

        $paramName = 'category';
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $slug]);

        return $url;
    }

}