<?php namespace Abnmt\Theater\Components;

use Cms\Classes\ComponentBase;

use Cms\Classes\Page;
use Cms\Classes\Content;

use Abnmt\Theater\Models\Event         as EventModel;
use Abnmt\Theater\Models\Article       as ArticleModel;
use Abnmt\Theater\Models\Performance   as PerformanceModel;
use Abnmt\Theater\Models\Person        as PersonModel;
use Abnmt\Theater\Models\Taxonomy      as TaxonomyModel;
use Abnmt\Theater\Models\Participation as ParticipationModel;

class Theater extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Theater Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}