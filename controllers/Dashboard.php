<?php namespace Abnmt\Theater\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Dashboard Back-end Controller
 */
class Dashboard extends Controller
{

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Abnmt.Theater', 'theater', 'dashboard');
    }

    public function index()
    {

    }
}