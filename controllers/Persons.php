<?php namespace Abnmt\Theater\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Persons Back-end Controller
 */
class Persons extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Abnmt.Theater', 'theater', 'persons');
    }




    public function index($userId = null)
    {
        $this->asExtension('ListController')->index();
    }

    public function listExtendQuery($query)
    {
        $query->with(['portrait']);
    }
}