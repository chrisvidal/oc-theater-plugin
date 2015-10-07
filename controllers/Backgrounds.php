<?php namespace Abnmt\Theater\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Flash;
use Lang;

/**
 * Backgrounds Back-end Controller
 */
class Backgrounds extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Abnmt.Theater', 'theater', 'backgrounds');
    }

    /**
     * Deleted checked backgrounds.
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $backgroundId) {
                if (!$background = Background::find($backgroundId)) continue;
                $background->delete();
            }

            Flash::success(Lang::get('abnmt.theater::lang.backgrounds.delete_selected_success'));
        }
        else {
            Flash::error(Lang::get('abnmt.theater::lang.backgrounds.delete_selected_empty'));
        }

        return $this->listRefresh();
    }
}