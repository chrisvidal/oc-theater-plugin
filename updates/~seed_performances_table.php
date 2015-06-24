<?php namespace Abnmt\Theater\Updates;

use System\Models\File as File;
use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Models\PerformanceCategory;
use October\Rain\Database\Updates\Seeder;

class SeedPerformancesTable extends Seeder
{

    public $fileData = [];

    public function run()
    {

        $this->fileData = $this->fillArrayWithFileNodes( new \DirectoryIterator( 'D:\Dropbox\OpenServer\domains\komedianty.abnmt.com\storage\app\media\_images\performance' ) );

        require_once 'data/performances.php';

        foreach ($performances as $key => $performance) {
            $performance = $this->createPerformance($performance);

            $this->assignImages($performance);
        }
    }

    private function createPerformance($performance)
    {
        $categories = $performance['category'];

        unset($performance['category']);

        $model = Performance::create($performance);

        foreach ($categories as $key => $category_slug) {
            $category = PerformanceCategory::where('slug', '=', $category_slug)->first();
            if (!is_null($category)) {
                $model->categories()->save($category);
            }
        }

        return $model;

    }

    private function fillArrayWithFileNodes( \DirectoryIterator $dir )
    {
        $data = array();
        foreach ( $dir as $node )
        {
            if ( $node->isDir() && !$node->isDot() )
            {
                $data[$node->getFilename()] = $this->fillArrayWithFileNodes( new \DirectoryIterator( $node->getPathname() ) );
            }
            else if ( $node->isFile() )
            {
                $data[$node->getBasename('.' . $node->getExtension())] = $node->getPathname();
            }
        }
        return $data;
    }

    private function assignImages($performance)
    {

        if ( array_key_exists($performance->slug, $this->fileData) ) {

            $images = $this->fileData[$performance->slug];

            echo $performance->slug . "\n";

            // print_r($performance->video);

            foreach ($images as $key => $filePath)
            {

                if ( !is_array($filePath) )
                {

                    $file = new File();
                    $file->fromFile($filePath);
                    // $file->save();

                    if ($key == 'playbill'){
                        if ($performance->playbill->getFilename() == $file->getFilename())
                            echo "File " . $file->getFilename() . "EXIST\n";
                        else
                            $performance->playbill()->save($file);
                    }
                    elseif ($key ==  'playbill_flat'){
                        if ($performance->playbill->getFilename() == $file->getFilename())
                            echo "File " . $file->getFilename() . "EXIST\n";
                        else
                            $performance->playbill_flat()->save($file);
                    }
                    elseif ($key ==  'playbill_mask'){
                        if ($performance->playbill->getFilename() == $file->getFilename())
                            echo "File " . $file->getFilename() . "EXIST\n";
                        else
                            $performance->playbill_mask()->save($file);
                    }
                    elseif ($key ==  'video'){
                        if ($performance->playbill->getFilename() == $file->getFilename())
                            echo "File " . $file->getFilename() . "EXIST\n";
                        else
                            $performance->video()->save($file);
                    }
                    elseif ($key ==  'repertoire'){
                        if ($performance->playbill->getFilename() == $file->getFilename())
                            echo "File " . $file->getFilename() . "EXIST\n";
                        else
                            $performance->repertoire()->save($file);
                    }
                    elseif ($key ==  'cover'){
                        if ($performance->playbill->getFilename() == $file->getFilename())
                            echo "File " . $file->getFilename() . "EXIST\n";
                        else
                            $performance->background_mobile()->save($file);
                    }
                    else
                        echo 'Image ' . $filePath . ' not saved.' . "\n";
                }
                elseif ( is_array($filePath) )
                {
                    foreach ($filePath as $filename => $filePath) {
                        $file = new File();
                        $file->fromFile($filePath);
                        // $filename = $file->getFilename;
                        // $file->save();

                        if ( $key == 'bg' && preg_match('/.+?_flat/', $filename) ){
                            print_r( $performance->background_flat );
                            // echo "File " . $file->getFilename() . "EXIST\n";
                            // $performance->background_flat()->save($file);
                        }
                        elseif ( $key == 'bg' && preg_match('/.+?_mask/', $filename) ){
                            // $performance->background_mask()->save($file);
                        }
                        elseif ( $key == 'bg' ){
                            // $performance->background()->save($file);
                        }
                        elseif ( $key == 'gallery' ){
                            // $performance->featured()->save($file);
                        }
                        else
                            echo 'Image ' . $filePath . ' not saved.' . "\n";

                    }
                }
            }
        }

    }
}
