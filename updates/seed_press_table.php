<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Models\Person;
use Abnmt\Theater\Models\Press;
use October\Rain\Database\Updates\Seeder;

class SeedPressTable extends Seeder
{

    public function run()
    {
        require_once 'data/press.php';

        foreach ($press as $article) {

            $relations = '';

            if (array_key_exists('relations', $article)) {
                $relations = $article['relations'];
                unset($article['relations']);
            }

            $post = Press::create($article);

            if ($relations != '') {
                foreach ($relations as $key => $relation) {
                    $relation = $this->findRecord($relation);

                    if (!is_null($relation)) {
                        // echo $relation['slug'] . "\n";

                        $relation->press()->add($post, null, ['description' => $post->title . " => " . $relation->title]);
                        // $post->save();
                    }
                }
            }
        }
    }

    private function findRecord($query)
    {

        if (!is_null($post = Person::where('title', '=', $query)->first())) {
            return $post;
        } elseif (!is_null($post = Person::where('slug', '=', $query)->first())) {
            return $post;
        } elseif (!is_null($post = Performance::where('title', '=', $query)->first())) {
            return $post;
        } elseif (!is_null($post = Performance::where('slug', '=', $query)->first())) {
            return $post;
        } else {
            echo "NOT FIND!\n";
            return;
        }
    }

}
