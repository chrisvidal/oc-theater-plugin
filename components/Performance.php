<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;
use Abnmt\Theater\Models\Performance as PerformanceModel;

use \Clockwork\Support\Laravel\Facade as CW;
// use Laravelrus\LocalizedCarbon\LocalizedCarbon as Carbon;
use \Carbon\Carbon as Carbon;

class Performance extends ComponentBase
{

    /**
     * A Post object
     * @var Model
     */
    public $post;

    /**
     * A Post slug
     * @var string
     */
    public $slug;

    /**
     * A Roles in Performance
     * @var string
     */
    public $roles;

    /**
     * A Participation
     * @var string
     */
    public $participation;

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $personPage = 'theater/person';

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $pressPage = 'theaterPress/article';


    public function componentDetails()
    {
        return [
            'name'        => 'Спектакль',
            'description' => 'Выводит одиночный спектакль'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->prepareVars();

        if ($this->slug = $this->param('slug'))
            $this->post = $this->page['post'] = $this->loadPost();

        // $this->page['roles'] = $this->roles;

    }

    protected function prepareVars()
    {
        // $this->categoryFilter = $this->param('category');
    }

    protected function loadPost()
    {
        $post = PerformanceModel::isPublished()
            ->Performance()
            ->whereSlug($this->slug)
            ->first()
        ;

        /*
         * Add a "URL" helper attribute for linking to each performance and person press
         */

        $this->roles = [];
        $this->participation = [];

        $post->participation->each(function($role)
        {
            $role->person->setUrl($this->personPage, $this->controller);

            if ($role['group'] != NULL) {
                $this->roles[$role['group']][] = $role;
                return;
            }

            $this->participation[$role['title']]['title'] = $role->title;
            $this->participation[$role['title']]['type'] = $role->type;
            $this->participation[$role['title']]['description'] = $role->description;
            $this->participation[$role['title']]['group'] = $role->group;
            $this->participation[$role['title']]['persons'][] = $role->person;
        });

        $post->press->each(function($press){
            $press->setUrl($this->pressPage, $this->controller);
        });


        // BACKGROUNDS

        $post->background->each(function($image) {
            $image['sizes'] = $sizes = getimagesize('./' . $image->getPath());
        });

        $bg_layouts = $post->meta['layouts'];


        foreach ($bg_layouts as &$layout) {
            $layout['positions'] = $this->processBgs($layout['bgs'], $post->background);
        }
        // CW::info(['Layouts' => $bg_layouts]);

        $post->bg_layouts = $bg_layouts;


        // Roles
        $post->roles = $this->roles;
        $post->roles_ng = $this->participation;

        CW::info(['Performance' => $post]);

        return $post;
    }

    protected function processBgs($params, $backgrounds)
    {
        $return = [];

        foreach ($params as $param) {
            $_param = explode(' ', $param['position']);

            $bg = $param['bg']-1;

            $background = $backgrounds->slice($bg,1)->first();
            if (is_null($background)) {
                CW::info('Background 5 is null');
                // $positions = [
                //     'hidden' = 'true';
                // ];
                continue;
            }

            $sizes = $background['sizes'];

            if ( count($_param) == 1 && $_param[0] == 'hidden' ) {
                $positions = [
                    'hidden' => 'true',
                ];
            } else {
                $class = [
                    'column' => (count($_param) > 1) ? $_param[1] : null,
                    'valign' => (count($_param) > 2) ? $_param[2] : null,
                    'halign' => (count($_param) > 3) ? $_param[3] : null,
                ];
                $positions = [
                    'bg' => $bg,
                    'orig_width' => $sizes[0],
                    'orig_height' => $sizes[1],
                    'width'  => (count($_param) > 0) ? intval($_param[0], 10) : null,
                    'height' => (count($_param) > 0) ? round($_param[0]/$sizes[0]*$sizes[1]) : null,
                    'class' => join(' ', $class),
                    'inc' => (count($_param) > 4) ? $_param[4] : null,
                    // 'column' => (count($_param) > 1) ? $_param[1] : null,
                    // 'valign' => (count($_param) > 2) ? $_param[2] : null,
                    // 'halign' => (count($_param) > 3) ? $_param[3] : null,
                ];
                $positions += $class;
            }

            // CW::info(['Param' => $positions]);
            $return[] = $positions;

        }

        $collection = collect($return);
        $columns = $collection->groupBy('column')->toArray();

        $return = [];

        if (array_key_exists('right', $columns)) {
            $right = $columns['right'];

            $right_height = $this->defineHeight($right, 'right');

            // CW::info(['Right' => $right_height]);

            foreach ($right_height as $key => $item) {
                $return[] = $item;
            }
        }

        if (array_key_exists('left', $columns)) {
            $left  = array_reverse($columns['left']);

            $left_height = $this->defineHeight($left, 'left');

            // CW::info(['Left' => $left_height]);

            foreach ($left_height as $key => $item) {
                $return[] = $item;
            }
        }

        if (array_key_exists('side', $columns)) {
            $side  = $columns['side'];

            foreach ($side as $key => $item) {
                $item += [
                    'height_perc' => '475px',
                ];
                $return[] = $item;
            }
        }


        // CW::info(['Cols' => $return]);

        return $return;
    }

    protected function defineHeight($list, $column)
    {

        CW::info(['List' => $list]);

        $summ = [0];
        if ($column == 'left' & $list[0]['width'] == '592')
            $summ = [635];
        if ($column == 'left' & $list[0]['width'] == '443')
            $summ = [475];

        foreach ($list as $pos => $bg) {
            $summ[$pos+1] = $summ[$pos] + $bg['height'];
            // if (array_key_exists('inc', $bg))
            //     $summ[$pos+1] += $bg['inc'];
        }

        foreach ($list as $pos => &$bg) {
            $bg += [
                'top'    => $summ[$pos],
                'bottom' => end($summ) - $bg['height'] - $summ[$pos],
                'top_perc'    => $summ[$pos]/end($summ)*100 . '%',
                'height_perc' => $bg['height']/end($summ)*100 . '%',
                'bottom_perc' => (end($summ) - $bg['height'] - $summ[$pos])/end($summ)*100 . '%',
                'summ' => $summ,
            ];
        }

        return $list;
    }
}