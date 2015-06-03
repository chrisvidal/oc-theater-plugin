<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Event;
use Abnmt\Theater\Models\Playbill;
use October\Rain\Database\Updates\Seeder;
use Str;

use Laravelrus\LocalizedCarbon\LocalizedCarbon as LocalizedCarbon;

class SeedPlaybillsTable extends Seeder
{

    public function run()
    {

        $events = Event::all();

        $events->each(function($event) {
            // $year = $event->datetime->format('Y');
            $month = \Carbon\Carbon::parse($event->datetime)->startOfMonth();

            $playbill = Playbill::where('date', '=', $month)->first();

            if (is_null($playbill)) {

                $_playbill = [
                    'date'      => $month,
                    'published' => true,
                ];

                // print_r($_playbill);

                $playbill = Playbill::create($_playbill);
            }

            $event->playbill()->associate($playbill);
            $event->save();

        });

    }

    // private function genPlaybillTitle($date)
    // {
    //     setlocale(LC_ALL, 'Russian');
    //     $month = LocalizedCarbon::parse($date)->formatLocalized('%B');
    //     $year  = LocalizedCarbon::parse($date)->format('Y');
    //     // return $month . ' ' . $year;
    //     return join(' ', [$month, $year]);
    // }
}
