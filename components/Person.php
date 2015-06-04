<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Person as PersonModel;

use \Clockwork\Support\Laravel\Facade as CW;

class Person extends ComponentBase
{

    /**
     * A Post object
     * @var Model
     */
    public $post;

    /**
     * A Post slug
     * @var string
     */
    public $slug;

    /**
     * A Person Roles
     * @var string
     */
    public $roles;

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $performancePage = 'single/performance';

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $pressPage = 'single/press';



    public function componentDetails()
    {
        return [
            'name'        => 'Персоналия',
            'description' => 'Выводит страницу биографии'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->prepareVars();

        if ($this->slug = $this->param('slug'))
            $this->post = $this->page['post'] = $this->loadPost();

        $this->page['roles'] = $this->roles;

    }

    protected function prepareVars()
    {
        // $this->categoryFilter = $this->param('category');
    }

    protected function loadPost()
    {
        $post = PersonModel::isPublished()
            ->with(['portrait', 'participation.performance', 'press'])
            ->whereSlug($this->slug)
            ->first()
        ;

        $post->participation->each(function($participation){

            $participation->performance->setUrl($this->performancePage, $this->controller);

            if ($participation->type == 'roles'){
                $this->roles[$participation->performance->id]['url'] = $participation->performance->url;
                $this->roles[$participation->performance->id]['performance'] = $participation->performance->title;
                $this->roles[$participation->performance->id]['roles'][] = $participation->title;
            }

        });

        $post->press->each(function($press){

            $press->setUrl($this->pressPage, $this->controller);

        });

        CW::info($post->press);

        return $post;
    }

}