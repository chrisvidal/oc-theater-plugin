<?php namespace Abnmt\Theater\Models;

use Model;

use Str;
use URL;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
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



    /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * with the following elements:
     * - references - a list of the item type reference options. The options are returned in the
     *   ["key"] => "title" format for options that don't have sub-options, and in the format
     *   ["key"] => ["title"=>"Option title", "items"=>[...]] for options that have sub-options. Optional,
     *   required only if the menu item type requires references.
     * - nesting - Boolean value indicating whether the item type supports nested items. Optional,
     *   false if omitted.
     * - dynamicItems - Boolean value indicating whether the item type could generate new menu items.
     *   Optional, false if omitted.
     * - cmsPages - a list of CMS pages (objects of the Cms\Classes\Page class), if the item type requires a CMS page reference to
     *   resolve the item URL.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'repertoire-category') {

            // $references = [];
            // $categories = self::orderBy('name')->get();
            // foreach ($categories as $category) {
            //     $references[$category->id] = $category->name;
            // }

            $references = [
                'normal'   => 'Спектакли',
                'child'    => 'Детские спектакли',
                'archive'  => 'Архивные спектакли',
            ];

            $result = [
                'references'   => $references,
                'nesting'      => false,
                'dynamicItems' => false
            ];
        }

        if ($type == 'all-repertoire-category') {
            $result = [
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('theaterRepertoire'))
                    continue;

                /*
                 * Component must use a category filter with a routing parameter
                 * eg: categoryFilter = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('theaterRepertoire');
                if (!isset($properties['categoryFilter']) || !preg_match('/{{\s*:/', $properties['categoryFilter']))
                    continue;

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * Returns information about a menu item. The result is an array
     * with the following keys:
     * - url - the menu item URL. Not required for menu item types that return all available records.
     *   The URL should be returned relative to the website root and include the subdirectory, if any.
     *   Use the URL::to() helper to generate the URLs.
     * - isActive - determines whether the menu item is active. Not required for menu item types that
     *   return all available records.
     * - items - an array of arrays with the same keys (url, isActive, items) + the title key.
     *   The items array should be added only if the $item's $nesting property value is TRUE.
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;

        if ($item->type == 'repertoire-category') {
            if (!$item->reference || !$item->cmsPage)
                return;

            $category = self::find($item->reference);
            if (!$category)
                return;

            $pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl)
                return;

            $pageUrl = URL::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $category->updated_at;
        }
        elseif ($item->type == 'all-blog-categories') {
            $result = [
                'items' => []
            ];

            $categories = self::orderBy('name')->get();
            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => $category->name,
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

        $properties = $page->getComponentProperties('theaterRepertoire');
        if (!isset($properties['categoryFilter'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the category filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['categoryFilter'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $category->slug]);

        return $url;
    }
}
