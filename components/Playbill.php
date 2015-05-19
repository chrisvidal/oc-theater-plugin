<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Event;
use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Classes\Date;

class Playbill extends ComponentBase
{

    public $playbill;

    public $active;

    public $performancePage;

    public $playbillMonths;

    public $test;

    public function componentDetails()
    {
        return [
            'name'        => 'Афиша',
            'description' => 'Выводит афишу'
        ];
    }

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
                'default'     => 'performance',
            ],
        ];
    }

    public function getPerformancePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    protected function prepareVars()
    {
        /*
         * Performance links
         */
        $this->performancePage = $this->page['performancePage'] = $this->property('performancePage');
    }

    public function onRun()
    {

        $this->prepareVars();

        $events = Event::orderBy('datetime', 'asc')
            ->join('abnmt_theater_performances', 'abnmt_theater_events.performance_id', '=', 'abnmt_theater_performances.id')
            ->with('performance')
            // ->where('date', '>=', date_create('now'))
            ->get()
        ;

        $this->active = 'active';

        $events->each(function($event)
        {
            // $performance = Performance::whereId($event->performance_id)->first();

            // $event->title = $performance->title;
            $event->class = $event->datetime . '-' . $event->slug;
            // $event->slug = $performance->slug;
            // $event->author = $performance->author;

            $event->time = date('G:i', strtotime($event->datetime));
            $event->duration = date('g:i', strtotime($event->performance->duration));

            $event->day = date( 'j', strtotime($event->datetime));
            $event->day2 = date( 'd', strtotime($event->datetime));
            $event->month = Date::getMonths($event->datetime);
            $event->month2 = date( 'M', strtotime($event->datetime));
            $event->month3 = Date::getNormalMonths($event->datetime);
            $event->weekday = Date::getWeekday($event->datetime);
            $event->weekday2 = date( 'D', strtotime($event->datetime));
            $event->weekday3 = Date::getWeekdayShort($event->datetime);

            // $event->playbill = $performance->playbill;
            // $event->repertoire = $performance->repertoire;

            $event->setUrl($this->performancePage, $this->controller);

            // $event->rate = $performance->rate;

            // $event->active = $this->active;
            // $this->active = '';

            $this->playbillMonths[$event->month2]['active'] = date('M') == date( 'M', strtotime($event->datetime)) ? 'active' : '';
            $this->playbillMonths[$event->month2]['name'] = $event->month3;
            $this->playbillMonths[$event->month2][$event->performance->type][] = $event;

        });

        // $events->each(function($event)
        // {
        //     // $performance = Performance::whereId($event->performance_id)->first();

        //     // $event->title = $performance->title;
        //     $event->class = $event->datetime . '-' . $event->slug;
        //     // $event->slug = $performance->slug;
        //     // $event->author = $performance->author;

        //     $event->time = date('G:i', strtotime($event->time));
        //     $event->duration = date('g:i', strtotime($event->performance->duration));

        //     $event->day = date( 'j', strtotime($event->datetime));
        //     $event->day2 = date( 'd', strtotime($event->datetime));
        //     $event->month = Date::getMonths($event->datetime);
        //     $event->month2 = date( 'M', strtotime($event->datetime));
        //     $event->weekday = Date::getWeekday($event->datetime);
        //     $event->weekday2 = date( 'D', strtotime($event->datetime));
        //     $event->weekday3 = Date::getWeekdayShort($event->datetime);

        //     // $event->playbill = $performance->playbill;
        //     // $event->repertoire = $performance->repertoire;

        //     $event->setUrl($this->performancePage, $this->controller);

        //     // $event->rate = $performance->rate;

        //     $event->active = $this->active;
        //     $this->active = '';

        // });

        $this->playbill = $events;

        $this->test = json_encode($this->playbillMonths, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }

}