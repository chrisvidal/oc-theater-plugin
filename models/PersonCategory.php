<?php namespace Abnmt\Theater\Models;

use Str;
use Model;
use URL;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

use \Clockwork\Support\Laravel\Facade as CW;

/**
 * PersonCategory Model
 */
class PersonCategory extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'abnmt_theater_person_categories';

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
        'persons' => ['Abnmt\Theater\Models\Person', 'table' => 'abnmt_theater_persons_categories', 'order' => 'family_name desc', 'scope' => 'isPublished']
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];





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

        if ($type == 'person-category') {

            $references = [];
            $categories = self::orderBy('title')->get();
            foreach ($categories as $category) {
                $references[$category->id] = $category->title;
            }

            $result = [
                'references'   => $references,
                'nesting'      => false,
                'dynamicItems' => false
            ];
        }

        if ($type == 'all-person-categories') {
            $result = [
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('theaterTroupe'))
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

        if ($item->type == 'person-category') {
            if (!$item->reference || !$item->cmsPage)
                return;

            $category = self::with('persons')->find($item->reference);
            if (!$category)
                return;


            /*  */
            $category->persons->each(function($person) {
                $person->url = CmsPage::url('single/person', ['slug' => $person->slug]);
            });
            $postUrls = $category->persons->lists('url', 'slug');
            /*  */

            $pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl)
                return;

            $pageUrl = URL::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url || in_array($url, $postUrls);
            $result['mtime'] = $category->updated_at;

            CW::info($postUrls);
        }
        elseif ($item->type == 'all-person-categories') {
            $result = [
                'items' => []
            ];

            $categories = self::orderBy('title')->get();
            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => $category->title,
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

        $paramName = 'category';
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $category->slug]);

        return $url;
    }
    /**
     * Returns URL of a page.
     */
    protected static function getPostPageUrl($pageCode, $post, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return;

        $paramName = 'slug';
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $post->slug]);

        return $url;
    }
}