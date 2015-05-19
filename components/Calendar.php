<?php namespace Abnmt\Theater\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Event;
use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Classes\Date;

class Calendar extends ComponentBase
{

    public $playbill;

    public $active;

    public $performancePage;

    public $test;

    public function componentDetails()
    {
        return [
            'name'        => 'Календарь',
            'description' => 'Выводит календарь'
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
            ->with('performance', 'performance.playbill')
            ->where('state', '<>', 'archive')
            ->where('type', '=', 'normal')
            ->where('datetime', '>=', date_sub(date_create('now'), date_interval_create_from_date_string('4 days')))
            ->get();

        // $test = Event::orderBy('date', 'asc')
        //     ->with('performance', 'performance.playbill')
        //     ->get();

        $this->test = json_encode($events, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // $this->test = json_encode($test, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $this->active = 'before';

        $events->each(function($event)
        {
            // $performance = Performance::whereId($event->performance_id)->first();

            // $event->title = $performance->title;
            $event->class = date( 'Y-m-d', strtotime($event->datetime)) . '-' . $event->slug;
            // $event->slug = $performance->slug;
            // $event->author = $performance->author;

            $event->time = date('G:i', strtotime($event->datetime));
            $event->duration = date('g:i', strtotime($event->duration));

            $event->day = date( 'j', strtotime($event->datetime));

            $event->dayclass = (strlen($event->day) == 2 ) ? 'double' : 'single';

            $event->day2 = date( 'd', strtotime($event->datetime));
            $event->month = Date::getMonths($event->datetime);
            $event->month2 = date( 'M', strtotime($event->datetime));
            $event->weekday = Date::getWeekday($event->datetime);
            $event->weekday2 = date( 'D', strtotime($event->datetime));
            $event->weekday3 = Date::getWeekdayShort($event->datetime);

            $event->playbill = $event->performance->playbill;
            $event->repertoire = $event->performance->repertoire;

            // $event->rate = $performance->rate;

            $event->setUrl($this->performancePage, $this->controller);

            if ($event->datetime >= date('Y-m-d') && $this->active == 'before')
            {
                $this->active = 'active';
                $event->active = $this->active;
            }
            elseif ($event->datetime >= date('Y-m-d') && $this->active == 'active')
            {
                $this->active = '';
                $event->active = $this->active;
            }
            else
            {
                $event->active = $this->active;
            }
        });

        $this->playbill = $events;
    }

}