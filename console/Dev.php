<?php namespace Abnmt\Theater\Console;


use File;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Dev extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'theater:dev';

    /**
     * @var string The console command description.
     */
    protected $description = 'Does something cool.';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        $this->output->writeln('Hello world!');

        $data = $this->MultiSort( require_once 'plugins/abnmt/theater/updates/data/journal.php', [ 'published_at'  => [SORT_ASC, SORT_REGULAR] ] );

        File::put('./temp.php', $this->VarExportMin($data, true));
        File::put('./temp.json', json_encode($data));
        File::put('./col.php', $this->DumpCollection($data));


    }

    /**
     * Get the console command arguments.
     * @return array
     */
    // protected function getArguments()
    // {
    //     return [
    //         ['example', InputArgument::REQUIRED, 'An example argument.'],
    //     ];
    // }

    /**
     * Get the console command options.
     * @return array
     */
    // protected function getOptions()
    // {
    //     return [
    //         ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
    //     ];
    // }


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

    private function VarExportMin($var, $return = false) {
        if (is_array($var)) {
            $toImplode = array();
            foreach ($var as $key => $value) {
                $toImplode[] = var_export($key, true).'=>'.self::VarExportMin($value, true);
            }
            $code = 'array('.implode(',', $toImplode).')';
            if ($return) return $code;
            else echo $code;
        } else {
            return var_export($var, $return);
        }
    }

    private function DumpCollection($col)
    {
        $return = "<?php\n\nreturn [\n";

        foreach ($col as $el) {
            $return .= "\t[\n";

            uksort($el, [$this, "kc"]);

            foreach ($el as $key => $value) {
                switch (gettype($value)) {
                    case 'string':
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        $return .= "\t\t\"$key\" => $value,\n";
                        break;
                    case 'array':
                        // $array = "[\"" . implode("\", \"", $value) . "\"]";
                        // $return .= "\t\t\"$key\" => $array,\n";
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        $return .= "\t\t\"$key\" => $value,\n";
                        break;
                    case 'bool':
                    case 'integer':
                        $return .= "\t\t\"$key\" => $value,\n";
                        break;
                }
            }

            $return .= "\t],\n";
        }

        $return .="];";

        return $return;
    }

    var $scheme = [
        "title",
        "slug",
        "excerpt",
        "content",
        "published_at",
        "updated_at",
        "author",
        "category",
        "releases",
        "published",
    ];

    private function kc($a, $b)
    {

        $keys = $this->scheme;
        $position_a = array_search( $a, $keys );
        $position_b = array_search( $b, $keys );
        return  $position_a < $position_b ? -1 : 1;
    }

}
