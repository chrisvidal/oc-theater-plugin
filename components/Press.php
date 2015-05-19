<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Abnmt\Theater\Models\Performance as TheaterPerformance;
use Abnmt\Theater\Models\Person as TheaterPerson;
use Abnmt\Theater\Models\Press as TheaterPress;

class Press extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Пресса',
            'description' => 'Выводит страницу прессы'
        ];
    }

    /*
     * Define Properties
     */
    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Заголовок',
                'description' => 'Имя',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'performancePage' => [
                'title'       => 'Страница спектакля',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'single/performance',
            ],
            'personPage' => [
                'title'       => 'Страница биографии',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'single/person',
            ],
            'pressPage' => [
                'title'       => 'Страница прессы',
                'description' => 'Название страницы для ссылки "перейти". Это свойство используется по умолчанию компонентом.',
                'type'        => 'dropdown',
                'default'     => 'single/press',
            ],
        ];
    }



    /*
     * Registered Page Template Links Properties
     */
    public $performancePage;
    public $personPage;
    public $pressPage;

    /*
     * Get Page Template Links Properties
     */
    public function getPerformancePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getPersonPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getPressPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }



    /*
     * Prepared Page vars
     */
    protected function prepareVars()
    {
        /*
         * Template Links
         */
        $this->performancePage = $this->page['performancePage'] = $this->property('performancePage');
        $this->personPage      = $this->page['personPage']      = $this->property('personPage');
        $this->pressPage       = $this->page['pressPage']       = $this->property('pressPage');

        /*
         * Prepare Slug
         */
        $this->slug            = $this->page['slug']            = $this->property('slug');

    }



    /*
     * onRun Main function
     */
    public function onRun()
    {

        $this->prepareVars();

        $this->content = $this->page['content'] = $this->loadContent();

        $this->page['test'] = json_encode($this->content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }


    protected function loadContent()
    {

        $slug = $this->property('slug');

        $content = TheaterPress::isPublished()
            ->where('slug', '=', $slug)
            ->with(['performances', 'persons'])
            ->first()
        ;




        return $content;
    }




}