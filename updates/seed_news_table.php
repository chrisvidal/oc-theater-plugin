<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Models\Person;
use Abnmt\Theater\Models\News;

use Cms\Classes\MediaLibrary as Media;
use System\Models\File as File;

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

            // $this->assignCover($post);

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

    private function assignCover($post)
    {

        echo $post->slug . "\n";

        $content = $post->content;
        preg_match_all('/src\s*=\s*"(\/storage.+?[jpg|png])"/', $content, $match);


        if (count($match) != 0 and count($match[1]) != 0){
            $filePath = realpath("D:\Dropbox\OpenServer\domains\komedianty.abnmt.com" . $match[1][0]);
            $file = new File();
            $file->fromFile($filePath);
            $post->featured()->save($file);
        }

        // print_r($match);

        // $file->save();

    }

}
