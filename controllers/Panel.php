<?php namespace Abnmt\Theater\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Panel Back-end Controller
 */
class Panel extends Controller
{

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Abnmt.Theater', 'theater', 'panel');
    }

    public function index()
    {

    }
}
