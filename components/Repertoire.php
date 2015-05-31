<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Performance as PerformanceModel;

class Repertoire extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Репертуар',
            'description' => 'Компонент для вывода спектаклей',
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Слаг',
                'description' => 'Переменная для URL',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'section' => [
                'title'       => 'Тип',
                'description' => 'Переменная для URL',
                'default'     => '{{ :section }}',
                'type'        => 'string',
            ],
        ];
    }

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
        $this->pressPage       = $this->page['pressPage']       = 'single/press';
        $this->newsPage        = $this->page['newsPage']        = 'single/news';

        /**
         * Prepare Slug
         */
        $this->slug            = $this->page['slug']            = $this->property('slug');
        $this->section         = $this->page['section']         = $this->property('section');

    }

    /**
     * Run
     */
    public function onRun()
    {
        $this->prepareVars();

        $this->single = $this->page['single'] = $this->getSingle();
        $this->list   = $this->page['list']   = $this->getList();

        /**
         * Test
         */
        $this->test = $this->page['test'] = json_encode([$this->list,$this->single], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }


    protected function getList()
    {
        /**
         * Get List with relations
         */
        $list = PerformanceModel::getList(['section' => $this->section])->get();

        /**
         * Add a "URL" helper attribute for linking
         */
        $list->each(function($item)
        {
            $item->setUrl($this->performancePage, $this->controller);
        });

        /**
         * Return
         */
        return $list;
    }

    protected function getSingle()
    {
        /**
         * Get Single with relations
         */
        $single = PerformanceModel::getSingle(['slug' => $this->slug])->first();

        if (is_null($this->section)) {
            if ($single->type == 'normal' && $single->state == 'normal') {
                $this->section = 'normal';
            } elseif ($single->state == 'archived') {
                $this->section = 'archive';
            } elseif ($single->type == 'child' && $single->state == 'normal' ) {
                $this->section = 'child';
            }
        }

        /**
         * Return
         */
        return $single;
    }


}
