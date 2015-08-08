<?php namespace Abnmt\Theater\Updates;

use System\Models\File as File;

use Abnmt\Theater\Models\Person;
use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Models\Article;
use Abnmt\Theater\Models\Participation;
use Abnmt\Theater\Models\Event;
use Abnmt\Theater\Models\Taxonomy;

use October\Rain\Database\Updates\Seeder;

class SeedPeopleTable extends Seeder
{

    public function run()
    {

        // Models
        $data = [
            // 'Abnmt\Theater\Models\Person'      => $this->MultiSort( require_once 'data/people.php',       [ 'published'     => [SORT_DESC, SORT_NUMERIC], 'family_name' => [SORT_ASC, SORT_STRING] ] ),
            'Abnmt\Theater\Models\Person'      => $this->Shuffle( require_once 'data/people.php' ),
            'Abnmt\Theater\Models\Performance' => $this->MultiSort( require_once 'data/performances.php', [ 'premiere_date' => [SORT_ASC, SORT_NUMERIC] ] ),
            'Abnmt\Theater\Models\Article'     => $this->MultiSort( require_once 'data/articles.php',     [ 'published_at'  => [SORT_ASC, SORT_REGULAR] ] ),
        ];

        foreach ($data as $modelName => $models) {
            foreach ($models as $model) {
                $model = $this->createModel($modelName, $model);
            }
        }

        // Participations
        $participations = require_once 'data/participations.php';

        foreach ($participations as $participation) {
            $performance_id = $this->findPerformance($participation['title'])->id;
            $this->createParticipation($participation, $performance_id);
        }

        // Events
        $events = require_once 'data/events.php';

        foreach ($events as $event) {
            $relation = $this->findModel($event['title']);
            $this->createEvent($event, $relation);
        }
    }







    private function createModel($modelName, $model)
    {

        unset($relations);
        unset($taxonomies);

        if (array_key_exists('relations', $model)) {
            $relations = $model['relations'];
            unset($model['relations']);
        }

        if (array_key_exists('category', $model)) {
            $categories = $model['category'];
            unset($model['category']);
        }
        if (array_key_exists('state', $model)) {
            $categories = $model['state'];
            unset($model['state']);
        }


        unset($model['source']);
        // unset($model['source_author']);
        unset($model['source_date']);
        unset($model['source_link']);

        // unset($model['author']);
        unset($model['releases']);

        $model = $modelName::create($model);

        if (isset($relations))
            $this->createRelation($relations, $model);

        if (isset($categories))
            $this->addTaxonomy($categories, $model);

        return $model;
    }


    private function createParticipation($participation, $performance_id)
    {

        $sort_order = 1;

        foreach ($participation as $type => $roles) {
            if ($type != 'title') {
                foreach ($roles as $key => $item) {

                    $person_id = $this->findPerson($item['name'])->id;

                    $role = [
                        'title'             => array_key_exists('role', $item)        ? $item['role'] : '',
                        'performance_id'    => $performance_id,
                        'person_id'         => $person_id,
                        'description'       => array_key_exists('description', $item) ? $item['description'] : NULL,
                        'group'             => array_key_exists('group', $item)       ? $item['group'] : NULL,
                        'type'              => $type,
                        'sort_order'        => $sort_order,
                    ];

                    Participation::create($role);
                    $sort_order++;
                }
            }
        }
    }

    private function createRelation($relations, $model)
    {

        foreach ($relations as $key => $relation) {
            $relation = $this->findModel($relation);

            if (!is_null($relation)) {
                // echo $relation['slug'] . "\n";

                $relation->relation()->add($model, null, ['description' => $model->title . " => " . $relation->title]);
                // $post->save();
            }
        }

    }

    private function addTaxonomy($categories, $model)
    {
        if (!is_array($categories)) $categories = [$categories];

        foreach ($categories as $key => $category) {
            $taxonomy = Taxonomy::where('slug', '=', $category)->first();

            // echo $category . "\n";

            if (is_null($taxonomy)) {
                $taxonomy = Taxonomy::create(['slug' => $category, 'model' => get_class($model)]);
            }

            if (!is_null($taxonomy)) {
                $model->taxonomy()->add($taxonomy, null);
            }
        }

    }


    private function findPerson($name)
    {

        $post = Person::where('title', '=', $name)->first();

        if (is_null($post)) {
            // echo "NOT FIND! Create...\n";
            return $this->createPerson($name);
        } else {
            // echo "FIND! Id: " . $post->id . "\n";
            return $post;
        }
    }

    private function createPerson($name)
    {
        // echo "Create post: " . mb_convert_encoding($name, "CP866") . "\n";
        $data = [
            'family_name' => explode(' ', $name)[1],
            'given_name'  => explode(' ', $name)[0],
        ];

        $post = Person::create($data);
        return $post;
    }

    private function findPerformance($name)
    {
        $post = Performance::where('title', '=', $name)->first();

        // echo "\n\n\n  " . mb_convert_encoding($name, "CP866") . ": " . $post->id . "\n\n";

        return $post;
    }

    private function findModel($name)
    {
        $models = [
            'Abnmt\Theater\Models\Performance',
            'Abnmt\Theater\Models\Article',
            'Abnmt\Theater\Models\Person',
        ];

        foreach ($models as $model) {
            $post = $model::where('title', '=', $name)->first();
            if (!is_null($post)){
                return $post;
            }
        }

        echo "Not find relation for " . $name . "\n" ;
        return NULL;
    }

    private function createEvent($event, $relation)
    {
        // echo "Create post: " . mb_convert_encoding($name, "CP866") . "\n";
        $data = [
            'title'       => $event['title'],
            'event_date'  => $event['event_date'],
            // 'description' => array_key_exists('description', $event) ? $event['description'] : NULL,
            'description' => array_key_exists('description', $event) ? $event['description'] : $relation->description,
            // 'relation'    => $relation,
        ];

        $post = new Event($data);

        if (!is_null($relation)) {
            $relation->events()->add($post, null);
        }
        else {
            echo "Not find relation for " . $event['title'] . "\n" ;
            $post->save();
        }

        return $post;
    }







    /**
     *  Utils
     */

    private function MultiSort($data, $sortCriteria, $caseInSensitive = true)
    {
        if( !is_array($data) || !is_array($sortCriteria))
            return false;
        $args = array();
        $i = 0;
        foreach($sortCriteria as $sortColumn => $sortAttributes)
        {
            $colList = array();
            foreach ($data as $key => $row)
            {
                $convertToLower = $caseInSensitive && (in_array(SORT_STRING, $sortAttributes) || in_array(SORT_REGULAR, $sortAttributes));
                $rowData = $convertToLower ? strtolower($row[$sortColumn]) : $row[$sortColumn];
                $colLists[$sortColumn][$key] = $rowData;
            }
            $args[] = &$colLists[$sortColumn];

            foreach($sortAttributes as $sortAttribute)
            {
                $tmp[$i] = $sortAttribute;
                $args[] = &$tmp[$i];
                $i++;
             }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return end($args);
    }

    private function Shuffle($data) {
        $keys = array_keys($data);

        shuffle($keys);

        foreach($keys as $key) {
            $new[$key] = $data[$key];
        }

        $data = $new;

        return $data;
    }

}