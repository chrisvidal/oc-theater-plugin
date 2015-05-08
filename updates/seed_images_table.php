<?php namespace Abnmt\Theater\Updates;

use System\Models\File;
use Abnmt\Theater\Models\Performance;
use Abnmt\Theater\Models\Person;
use October\Rain\Database\Updates\Seeder;

class SeedImagesTable extends Seeder
{

    public function run()
    {
        $files = File::where("field", "=", NULL)->get();
        // print_r($files);

        foreach ($files as $image) {
            // echo $image->file_name . "\n";
            $names = preg_split("/_/", $image->file_name, 3);

            switch ($names[0]) {
                case 'performance':
                {
                    $this->updateFile($image, $this->findPerformance($names[1]), $names);
                    break;
                }
                case 'person':
                {
                    $this->updateFile($image, $this->findPerson($names[1]), $names);
                    break;
                }
                default:
                {
                    echo "Not find class: " . $image->file_name . "\n";
                    break;
                }
            }

            // playbill
            // repertoire
            // background
            // featured

            // $image->is_public = $fileRelation->isPublic();
            // $image->save();
        }

        // Performance::create(["title" => "Беда от нежного сердца", "slug" => "beda-ot-nezhnogo-serdtsa", "author" => "Владимир Сологуб", "content" => "<p>Вот, скажите на милость, что делать молодому человеку, симпатичному, доброму, да еще мечтающему жениться?! Скорей всего вы ответите: «Найти невесту, предложить ей руку и сердце»… И будете совершенно правы. Но как поступить юноше, если его сердце буквально тает от любви при виде любой молоденькой девушки?! Здесь и начинаются сложности, поистине, «беда от нежного сердца». Марью Петровну, Катерину Ивановну, Настасью Павловну… всех позвал замуж незадачливый герой!</p>\n<p>Русский классический водевиль — один из самых ярких, затейливых и праздничных камешков в богатейшем ожерелье отечественной драматургии ХIХ столетия. Это жанр, полный изящества, прелести, лукавства и неподражаемых любовных перипетий.</p>\n<p>Жизнерадостный, остроумный спектакль доставит немало приятных минут зрителям всех поколений.</p>", "synopsis" => "Вот, скажите на милость, что делать молодому человеку, симпатичному, доброму, да еще мечтающе­му жениться? Скорей всего вы ответите: «Найти не­весту, предложить ей руку и сердце». И будете со­вершенно правы. Но как поступить юноше, сердце которого буквально тает от любви при виде каждой девушки?\nВсех позвал замуж незадачливый герой!\nЗдесь и начинаются сложности…", "state" => "normal", "type" => "normal", "genre" => "Музыкальная комедия", "duration" => "01:30:00", "entracte" => "1", "premiere_date" => "2000-01-01", "rate" => "12", "published" => "1"]);
    }


    private function findPerson($slug)
    {

        $post = Person::where('slug', '=', $slug)->first();

        if (is_null($post))
        {
            echo "NOT FIND!\n";
            return;
            // return $this->createPerson($name);
        }
        else
        {
            // echo "FIND! Id: " . $post->id . "\n";
            return $post;
        }
    }

    private function findPerformance($slug)
    {
        $post = Performance::where('slug', '=', $slug)->first();

        if (is_null($post))
        {
            echo "NOT FIND!\n";
            return;
            // return $this->createPerson($name);
        }
        else
        {
            // echo "FIND! Id: " . $post->id . "\n";
            return $post;
        }
    }

    private function updateFile($file, $post, $names)
    {
        // echo " --\n";
        $name_in  = preg_split("/\./", $names[2]);

        $fname = $name_in[0];
        $ext   = $name_in[1];

        $field = preg_split("/_/", $fname, 2);
        $field = preg_replace("/([0-9-]+)/", "", $field[0]);

        $fname = $names[1] . "_" . $names[2];

        $file->file_name =  $fname;

        switch ($field) {
            case 'troupe':
                $field = "portrait";
                break;
            case 'bg':
                $field = "background";
                break;
            case 'playbill':
                $field = "playbill";
                break;
            case 'repertoire':
                $field = "repertoire";
                break;
            case 'featured':
                $field = "featured";
                break;
            case 'video':
                $field = "video";
                break;
            case 'gallery':
                $field = "featured";
                break;
            default:
                echo "Not find field " . $field . "\n";
                return;
        }
        $file->field = $field;

        $file->attachment_id =  $post->id;
        $file->attachment_type =  get_class($post);

        $file->save();
    }

}