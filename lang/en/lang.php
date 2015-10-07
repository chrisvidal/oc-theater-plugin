<?php

return [
    'plugin' => [
        'name' => 'Theater',
        'description' => 'A theater platform.',
    ],
    'dates' => [
        'dateFormat' => '%c',
        'months_nom' => '|january|febrary|marth|april|may|june|july|august|september|october|november|december',
        'months_shrt' => '|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec',
        'months_gen' => '|january|febrary|marth|april|may|june|july|august|september|october|november|december',
        'weekdays_nom' => '|monday|tuesday|wednesday|thursday|friday|saturday|sunday|',
        'weekdays_shrt' => '|mon|tue|wed|thu|fri|sat|sun|',
    ],
    'components' => [
        'backgrounds' => [
            'name' => 'Backgrounds Component',
            'description' => 'No description provided yet...',
        ],
    ],
    'background' => [
        'label' => 'Background',
        'create_title' => 'Create Background',
        'update_title' => 'Edit Background',
        'preview_title' => 'Preview Background',
        'list_title' => 'Manage Backgrounds',
        'new' => 'New Background',
    ],
    'backgrounds' => [
        'menu_label' => 'Backgrounds',
        'return_to_list' => 'Return to Backgrounds',
        'delete_confirm' => 'Do you really want to delete this background?',
        'delete_selected_confirm' => 'Delete the selected backgrounds?',
        'delete_selected_success' => 'Successfully deleted the selected backgrounds.',
        'delete_selected_empty' => 'There are no selected :name to delete.',
    ],
];