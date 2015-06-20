<?php namespace Abnmt\Theater\Updates;

// use DirectoryIterator;
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

            echo $performance->slug;
            print_r($images);

            foreach ($images as $key => $filePath)
            {

                if ( !is_array($filePath) )
                {

                    $file = new File();
                    $file->fromFile($filePath);
                    // $file->save();

                    switch ($key) {
                        case 'playbill':
                            $performance->playbill()->save($file);
                            break;
                        case 'playbill_flat':
                            $performance->playbill_flat()->save($file);
                            break;
                        case 'playbill_mask':
                            $performance->playbill_mask()->save($file);
                            break;
                        case 'video':
                            $performance->video()->save($file);
                            break;
                        case 'repertoire':
                            $performance->repertoire()->save($file);
                            break;
                        case 'cover':
                            $performance->background_mobile()->save($file);
                            break;

                        default:
                            # code...
                            break;
                    }
                }
                elseif ( is_array($filePath) )
                {
                    foreach ($filePath as $filename => $filePath) {
                        $file = new File();
                        $file->fromFile($filePath);
                        // $file->save();

                        if ( $key == 'bg' && preg_match('/.+?_flat/', $filename) )
                            $performance->background_flat()->save($file);
                        elseif ( $key == 'bg' && preg_match('/.+?_mask/', $filename) )
                            $performance->background_mask()->save($file);
                        elseif ( $key == 'bg' )
                            $performance->background()->save($file);
                        elseif ( $key == 'gallery' )
                            $performance->featured()->save($file);

                    }
                }
            }
        }

    }
}
