<?php namespace Abnmt\Theater\Controllers;

use Backend\Classes\Controller;
use \Clockwork\Support\Laravel\Facade as CW;

/**
 * Playbill Back-end Controller
 */
class Playbill extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function index()
    {

    }

    public function onTest()
    {

        $now  = new \DateTime;
        $from = $now->format('Y-m-d');
        $to   = date("Y-m-d", strtotime("+2 month"));

        $url = "http://komedianty.apit.bileter.ru/d98a3e8ab87ecd3721a78a273bd9146a/afisha/?from=" . $from . "&to=" . $to . "&json=true";

        // $contents = file_get_contents($url, false, null, 62, 8);
        $contents = file_get_contents($url);

        $convert = iconv("UTF-8", "UTF-8//IGNORE", $contents);

        $convert = preg_replace('/(?>[\x00-\x1F]|\xC2[\x80-\x9F]|\xE2[\x80-\x8F]{2}|\xE2\x80[\xA4-\xA8]|\xE2\x81[\x9F-\xAF])/', ' ', $convert);

        $json = json_decode($convert, true);

        $result = [];

        foreach ($json as $month => $days) {
            foreach ($days as $day => $events) {
                foreach ($events as $key => $event) {

                    $check = $this->checkEvent($event);

                    // CW::info($check);

                    if (is_null($check)) {
                        continue;
                    }

                    $result[] = $check;

                }
            }
        }

        foreach ($result as $key => $data) {
            $result[$key] = $this->makePartial('bileter', ['data' => $data]);
        }

        CW::info($result);

        return [
            '#result' => $result,
        ];

    }

    private function checkEvent($event)
    {

        $model = 'Abnmt\Theater\Models\Event';

        $event_date = new \DateTime($event['PerfDate']);
        $bileter_id = $event['IdPerformance'];

        $search = $model::where('bileter_id', '=', $bileter_id)->first();

        $relation = $this->findRelation($event['Name']);

        $result = [
            'event_date' => $event_date,
            'bileter_id' => $bileter_id,
            'relation'   => $relation,
            'changed'    => 0,
        ];

        if (!is_null($relation)) {
            $result['title']       = $relation['title'];
            $result['description'] = $relation['description'];
        }

        $model = $model::make($result);

        // if (!is_null($relation)) {
        //     $relation->events()->add($model, null);
        // }

        $result['model'] = $model;

        CW::info(['Model' => $model]);

        if (is_null($search)) {

            if (is_null($relation)) {
                $result['search_string'] = $event['Name'];
                return $result; // New, not found relation
            }

            $relation->events()->add($model, null);
            return $result; // New, relation find
        }

        if ($search['relation_id'] != $relation['id']) {

            $result['changed'] = $search['relation_id'];

            $relation->events()->add($model, null);

            return $result; // Exist, changed
        }

        return; // Exist, not changed

    }

    private function findRelation($name)
    {

        $models = [
            'Abnmt\Theater\Models\Performance',
            // 'Abnmt\Theater\Models\Article',
            // 'Abnmt\Theater\Models\Person',
        ];

        $exclusion = [
            "Если поженились,значит,жить прид тся" => "Если поженились, значит, жить придётся!",
            "Кыцик,Мыцик и т тушка Мари"                     => "Кыцик, Мыцик и тётушка Мари",
            "Марлен,рожд нная для любви"                    => "Марлен, рождённая для любви",
            "Не всякий вор - грабитель"                       => "Не всякий вор — грабитель",
            " В Париж !"                                                    => "В Париж!",
        ];

        if (array_key_exists($name, $exclusion)) {
            $name = $exclusion[$name];
        }

        foreach ($models as $model) {
            $post = $model::where('title', '=', $name)->first();
            if (!is_null($post)) {
                return $post;
            }
        }

        // echo "Not find relation for " . $name . "\n";
        return;
    }

    public function onSave()
    {

        // CW::info(post());

        // $data = post('data');

        // $relation = $data['relation'];
        // $model    = $data['model'];

        // $relation->events()->add($model, null);
        // \Flash::info('Saved!');
    }

}
