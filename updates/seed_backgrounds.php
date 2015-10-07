<?php namespace Abnmt\Theater\Updates;

use Abnmt\Theater\Models\Background;
use October\Rain\Database\Updates\Seeder;
use System\Models\File as File;

class SeedBackgroundsTable extends Seeder
{

    public function run()
    {

        // Layouts
        $layouts = require_once 'data/backgrounds.php';

        $path     = "./storage/app/media/backgrounds";
        $fileData = $this->fillArrayWithFileNodes(new \DirectoryIterator($path), ["jpg"]);

        foreach ($layouts as $layout) {
            $model = Background::create($layout);
            $this->assignImages($model, $fileData);
        }

    }

    private function assignImages($model, $fileData)
    {

        if (array_key_exists($model->slug, $fileData)) {

            $images = $fileData[$model->slug];
            // print_r($images);

            echo $model->slug . " [";
            // echo get_class($model) . "\n";

            foreach ($images as $key => $filePath) {

                if (!is_array($filePath)) {
                    $pathinfo = pathinfo($filePath);
                    $check    = File::where('attachment_id', '=', $model->id)
                        ->where('attachment_type', '=', get_class($model))
                        ->where('file_name', '=', $pathinfo['basename'])
                        // ->where('field', '=', $pathinfo['filename'])
                        ->first();

                    if (!is_null($check)) {
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

                    $model->images()->save($file, null, ['title' => $model->title]);
                }

            }
            echo "]\n";
        }

    }

    /**
     * @param \DirectoryIterator $dir
     * @param $ext
     * @return mixed
     */
    private function fillArrayWithFileNodes(\DirectoryIterator $dir, $ext = ["jpg", "png"])
    {
        $data = array();
        foreach ($dir as $node) {
            if ($node->isDir() && !$node->isDot()) {
                $data[$node->getFilename()] = self::fillArrayWithFileNodes(new \DirectoryIterator($node->getPathname()));
            } elseif ($node->isFile() && in_array($node->getExtension(), $ext)) {
                $data[$node->getBasename('.' . $node->getExtension())] = $node->getPathname();
            }
        }
        return $data;
    }
}
