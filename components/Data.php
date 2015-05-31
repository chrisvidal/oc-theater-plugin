<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

class Data extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Data Component',
            'description' => 'No description provided yet...',
        ];
    }

    /**
     * Define Component Properties
     */
    // public function defineProperties()
    // {
    //     return [
    //         'section' => [
    //             'title'       => 'Раздел',
    //             'description' => 'Переменная для URL',
    //             'default'     => '{{ :section }}',
    //             'type'        => 'string',
    //         ],
    //         'subsection' => [
    //             'title'       => 'Подраздел',
    //             'description' => 'Переменная для URL',
    //             'default'     => '{{ :subsection }}',
    //             'type'        => 'string',
    //         ],
    //         'item' => [
    //             'title'       => 'Страница',
    //             'description' => 'Переменная для URL',
    //             'default'     => '{{ :item }}',
    //             'type'        => 'string',
    //         ],
    //     ];
    // }

    /**
     * Prepared Page vars
     */
    protected function prepareVars()
    {
        /**
         * Template Links
         */
        $this->performancePage = $this->page['performancePage'] = 'single/performance';
        $this->personPage      = $this->page['personPage']      = 'single/person';
        $this->newsPage        = $this->page['newsPage']        = 'single/news';
        $this->pressPage       = $this->page['pressPage']       = 'single/press';

        /**
         * Models
         */
        $this->performance = Performance::isPublished();
        $this->person      = Person::isPublished();
        $this->news        = News::isPublished();
        $this->press       = Press::isPublished();

        /**
         * Prepare Slug
         */
        $this->section = $this->page['section'] = $this->param('section');
        $this->list    = $this->page['list']    = $this->param('list');
        $this->single  = $this->page['single']  = $this->param('single');

    }

    /**
     * Run
     */
    public function onRun()
    {
        $this->prepareVars();

        if ($this->single) {
            $this->data = $this->page['data'] = $this->getSingle();
        }

        if ($this->list) {
            $this->list = $this->page['list'] = $this->getList();
        }

        /**
         * Test
         */
        $this->test = $this->page['test'] = '<pre>' . json_encode(['#SINGLE#' => $this->single, '#LIST#' => $this->list], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . '</pre>';
    }

    protected function getData()
    {
        $schema = [
            'sections' => [
                'news' => '',
                'press' => '',
                'repertoire' => '',
                'performance' => '',
                'troupe' => '',
                'person' => '',
            ],
        ];

        return $schema;
    }


    protected function getSingle()
    {
        /**
         * Get Single
         */
        $data = 'Это контент и ниибёт!';

        /**
         * Return
         */
        return $data;
    }
    protected function getList()
    {
        /**
         * Get List
         */
        $data = 'Это контент и ниибёт!';

        /**
         * Return
         */
        return $data;
    }

}
