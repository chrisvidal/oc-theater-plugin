<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Event;
use Abnmt\Theater\Models\Performance;
use October\Rain\Database\Updates\Seeder;

class SeedEventsTable extends Seeder
{

    public function run()
    {

        $path = "./plugins/abnmt/theater/updates/data/";
        $events = file($path . 'events.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // var_dump($events);
        $performances = Performance::all();

        foreach ($events as $key => $event) {
            $event = explode("\t", $event);
            // echo "\n\n\n  " . mb_convert_encoding($event, "CP866") . ": " . '$post->id' . "\n\n";

            $datetime = date_create($event[0]);
            $weekend = (date('N', strtotime($event[0])) >= 6);

            $post = $this->findPerformance($event[1]);
            $pid = $post->id;

            $child = ($post->type == "child") ? true : false;

            $datetime = date('Y-m-d', strtotime($event[0])) . " " . "19:00:00";

            if ($weekend) {
                $datetime = date('Y-m-d', strtotime($event[0])) . " " . "18:00:00";
            }

            if ($child) {
                $datetime = date('Y-m-d', strtotime($event[0])) . " " . "12:00:00";
            }

            // $performance = $performances->find($pid)->first();

            $title = $post->title;
            $slug = date('Y-m-d-H', strtotime($datetime)) . "-" . $post->slug;

            // echo $child . " " . $datetime . " " .  $title . " " .  $slug . " " .  $pid . "\n";

            $this->createEvent([
                'datetime'       => $datetime,
                'performance_id' => $pid,
                'title'          => $title,
                'slug'           => $slug,
                'published'      => 1,
            ]);
        }

    }

    private function createEvent($event)
    {
        Event::create($event);
    }

    private function findPerformance($name)
    {
        $post = Performance::where('title', '=', $name)->first();
        return $post;
    }
}
