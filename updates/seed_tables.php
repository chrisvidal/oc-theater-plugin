<?php namespace Abnmt\Theater\Updates;

use System\Models\File as File;

use Abnmt\Theater\Models\Person;
use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Models\Article;
use Abnmt\Theater\Models\Participation;
use Abnmt\Theater\Models\Event;
use Abnmt\Theater\Models\Taxonomy;

use Abnmt\Theater\Models\News;
use Abnmt\Theater\Models\Press;

use October\Rain\Database\Updates\Seeder;

class SeedPeopleTable extends Seeder
{

    public function run()
    {

        // Models
        $data = [
            // 'Abnmt\Theater\Models\Person'      => $this->MultiSort( require_once 'data/people.php' ),
            'Abnmt\Theater\Models\Person'      => $this->MultiSort( require_once 'data/people.php',       [ 'family_name'   => [SORT_ASC, SORT_STRING] ] ),
            'Abnmt\Theater\Models\Performance' => $this->MultiSort( require_once 'data/performances.php', [ 'premiere_date' => [SORT_ASC, SORT_NUMERIC] ] ),
            // 'Abnmt\Theater\Models\Article'     => $this->MultiSort( require_once 'data/articles.php',     [ 'published_at'  => [SORT_ASC, SORT_REGULAR] ] ),
        ];

        $path = "./storage/app/images";
        $fileData = $this->fillArrayWithFileNodes( new \DirectoryIterator( $path ), ["jpg", "png"] );

        // print_r($fileData);

        foreach ($data as $modelName => $models) {
            foreach ($models as $model) {
                $model = $this->createModel($modelName, $model);

                $this->assignImages($model, $fileData);
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
            if (!is_null($relation)) {
                $this->createEvent($event, $relation);
            }

        }

        // Sort files
        $files = File::get();
        $files->each(function($file){

            preg_match("~^(\d+)~", $file->file_name, $matches);

            if (count($matches) > 0) {
                $file->sort_order = intval($matches[0], 10);
                // print_r($file);
                $file->save();
            }
        });
        // print_r($files);

        // News
        $news = require_once 'data/news.php';
        foreach ($news as $key => $post) {
            $this->createNews($post);
        }
        // Press
        $press = require_once 'data/press.php';
        foreach ($press as $key => $post) {
            $this->createPress($post);
        }
    }




    private function createNews($news)
    {
        News::create($news);
    }
    private function createPress($press)
    {
        Press::create($press);
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
            $taxonomy = Taxonomy::where('title', '=', $category)->first();

            // echo $category . "\n";

            if (is_null($taxonomy)) {
                $taxonomy = Taxonomy::create(['title' => $category, 'model' => get_class($model)]);
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
            'description' => array_key_exists('description', $event) ? $event['description'] : (!is_null($relation)) ? $relation->description : null,
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


    private function assignImages($model, $fileData)
    {

        if (get_class($model) == 'Abnmt\Theater\Models\Article')
            return;

        if ( array_key_exists($model->slug, $fileData) ) {

            $images = $fileData[$model->slug];

            echo $model->slug . " [";
            // echo get_class($model) . "\n";

            foreach ($images as $key => $filePath)
            {

                if ( !is_array($filePath) )
                {
                    $pathinfo = pathinfo($filePath);
                    $check = File::where('attachment_id', '=', $model->id)
                        ->where('attachment_type', '=', get_class($model))
                        ->where('file_name', '=', $pathinfo['basename'])
                        // ->where('field', '=', $pathinfo['filename'])
                        ->first();

                    if ( !is_null($check) ) {
                        // echo $filePath . " ";
                        // echo filemtime($filePath) . " ";
                        // echo $check->updated_at->timestamp . "\n";
                        if (filemtime($filePath) > $check->updated_at->timestamp) {
                            // echo "File " . $filePath . " is Newer. Update!" . "\n";
                            echo "^";
                            $check->delete();
                        } else {
                            echo "~";
                            continue;
                        }
                    } else {
                        // echo "File " . $filePath . " is New. Create!" . "\n";
                        echo "+";
                    }

                    $file = new File();
                    $file->fromFile($filePath);
                    // $file->save();
                    // echo $filePath . "\n";
                    switch ($key) {
                        case 'playbill':
                            $model->playbill()->save($file, null, ['title' => $model->title]);
                            break;
                        case 'playbill_flat':
                            $model->playbill_flat()->save($file);
                            break;
                        case 'playbill_mask':
                            $model->playbill_mask()->save($file);
                            break;
                        case 'video':
                            $model->video()->save($file, null, ['title' => $model->title]);
                            break;
                        case 'repertoire':
                            $model->repertoire()->save($file, null, ['title' => $model->title]);
                            break;
                        case 'cover':
                            $model->cover()->save($file, null, ['title' => $model->title]);
                            break;

                        case 'portrait':
                            $model->portrait()->save($file, null, ['title' => $model->title]);
                            break;

                        default:
                            echo ' Image ' . $filePath . ' not saved.' . "\n";
                            break;
                    }
                }
                elseif ( is_array($filePath) )
                {
                    foreach ($filePath as $filename => $filePath)
                    {
                        $pathinfo = pathinfo($filePath);
                        $check = File::where('attachment_id', '=', $model->id)
                            ->where('attachment_type', '=', get_class($model))
                            ->where('file_name', '=', $pathinfo['basename'])
                            // ->where('field', '=', $pathinfo['filename'])
                            ->first();

                        // preg_match("~^(\d+)~", $filename, $matches);
                        // print_r($matches);

                        if ( !is_null($check) ) {
                            // echo $filePath . " ";
                            // echo filemtime($filePath) . " ";
                            // echo $check->updated_at->timestamp . "\n";
                            if (filemtime($filePath) > $check->updated_at->timestamp) {
                                // echo "File " . $filePath . " is Newer. Update!" . "\n";
                                echo "^";
                                $check->delete();
                            } else {
                                // echo "File " . $filePath . " is Older. Skip!" . "\n";
                                echo "~";
                                continue;
                            }
                        } else {
                            // echo "File " . $filePath . " is New. Create!" . "\n";
                            echo "+";
                        }

                        $file = new File();
                        $file->fromFile($filePath);
                        // $file->save();

                        // if (count($matches) > 0) {
                        //     $file->sort_order = intval($matches[0], 10);
                        //     print_r($file);
                        // }


                        if ( $key == 'bg' && preg_match('/.+?_flat/', $filename) ) {
                            $model->background_flat()->save($file);
                        }
                        elseif ( $key == 'bg' && preg_match('/.+?_mask/', $filename) ) {
                            $model->background_mask()->save($file);
                        }
                        elseif ( $key == 'bg' ) {
                            $model->background()->save($file);
                        }
                        elseif ( $key == 'gallery' ) {
                            $model->featured()->save($file);
                        }
                        else {
                            echo $filePath . ' not saved.' . "\n";
                        }

                    }
                }
            }
            echo "]\n";
        }

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

    private function fillArrayWithFileNodes( \DirectoryIterator $dir, $ext = ["jpg", "png"] )
    {
        $data = array();
        foreach ( $dir as $node )
        {
            if ( $node->isDir() && !$node->isDot() )
            {
                $data[$node->getFilename()] = self::fillArrayWithFileNodes( new \DirectoryIterator( $node->getPathname() ) );
            }
            elseif ( $node->isFile() && in_array($node->getExtension(), $ext) )
            {
                $data[$node->getBasename('.' . $node->getExtension())] = $node->getPathname();
            }
        }
        return $data;
    }

}