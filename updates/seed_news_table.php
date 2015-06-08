<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Models\Person;
use Abnmt\Theater\Models\News;
use October\Rain\Database\Updates\Seeder;

class SeedNewsTable extends Seeder
{

    public function run()
    {
        require_once 'data/news.php';

        foreach ($news as $article) {

            $relations = '';

            if (array_key_exists('relations', $article)) {
                $relations = $article['relations'];
                unset($article['relations']);
            }

            $post = News::create($article);

            if ($relations != '') {
                foreach ($relations as $key => $relation) {
                    $model = $this->findRecord($relation);

                    if (!is_null($model)) {
                        // echo $relation['slug'] . "\n";

                        $model->news()->add($post, null, ['description' => $post->title . " => " . $model->title]);
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
