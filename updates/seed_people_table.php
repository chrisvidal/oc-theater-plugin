<?php namespace Abnmt\Theater\Updates;

use System\Models\File as File;
use Abnmt\Theater\Models\Person;
use Abnmt\Theater\Models\PersonCategory;
use October\Rain\Database\Updates\Seeder;

class SeedPeopleTable extends Seeder
{

    public $fileData = [];

    public function run()
    {

        $this->fileData = $this->fillArrayWithFileNodes( new \DirectoryIterator( 'D:\Dropbox\OpenServer\domains\komedianty.abnmt.com\storage\app\media\_images\person' ) );

        require_once 'data/people.php';

        foreach ($people as $key => $person) {
            $person = $this->createPerson($person);

            // $this->assignImages($person);
        }

    }

    private function createPerson($person)
    {

        $categories = [
            'state' => array_key_exists('state', $person) ? $person['state'] : null,
        ];

        unset($person['state']);

        $model = Person::create($person);

        // echo $model->;

        if ( array_key_exists('state', $categories) ) {
            $category = PersonCategory::where('slug', '=', $categories['state'])->first();

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

    private function assignImages($person)
    {

        if ( array_key_exists($person->slug, $this->fileData) ) {

            $images = $this->fileData[$person->slug];

            echo $person->slug . "\n";
            // print_r($images);

            foreach ($images as $key => $filePath)
            {

                if ( !is_array($filePath) )
                {

                    $file = new File();
                    $file->fromFile($filePath);
                    // $file->save();

                    switch ($key) {
                        case 'troupe':
                            $person->portrait()->save($file);
                            break;
                        default:
                            echo 'Image ' . $filePath . ' not saved.' . "\n";
                            break;
                    }
                }
                elseif ( is_array($filePath) )
                {
                    foreach ($filePath as $filename => $filePath) {
                        $file = new File();
                        $file->fromFile($filePath);
                        // $file->save();

                        if ( $key == 'gallery' )
                            $person->featured()->save($file);
                        else
                            echo 'Image ' . $filePath . ' not saved.' . "\n";

                    }
                }
            }
        }

    }
}