<?php

return [
    'plugin' => [
        'name' => 'Театр',
        'description' => 'Платформа для театра.',
    ],
    'dates' => [
        'dateFormat' => '%e %h %Y г.',
        'months_nom' => '|январь|февраль|март|апрель|май|июнь|июль|август|сентябрь|октябрь|ноябрь|декабрь',
        'months_shrt' => '|янв.|февр.|март|апр.|май|июнь|июль|авг.|сент.|окт.|нояб.|дек.',
        'months_gen' => '|января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря',
        'weekdays_nom' => '|понедельник|вторник|среда|четверг|пятница|суббота|воскресенье',
        'weekdays_shrt' => '|пн|вт|ср|чт|пт|сб|вс',
    ],
    'components' => [
        'backgrounds' => [
            'name' => 'Фоновые изображения',
            'description' => 'Выводит изображения на фон страницы',
        ],
    ],
    'background' => [
        'label' => 'abnmt.theater::lang.background.label',
        'create_title' => 'abnmt.theater::lang.background.create_title',
        'update_title' => 'abnmt.theater::lang.background.update_title',
        'preview_title' => 'abnmt.theater::lang.background.preview_title',
        'list_title' => 'abnmt.theater::lang.background.list_title',
        'new' => 'abnmt.theater::lang.background.new',
    ],
    'backgrounds' => [
        'menu_label' => 'abnmt.theater::lang.backgrounds.menu_label',
        'return_to_list' => 'abnmt.theater::lang.backgrounds.return_to_list',
        'delete_confirm' => 'abnmt.theater::lang.backgrounds.delete_confirm',
        'delete_selected_confirm' => 'abnmt.theater::lang.backgrounds.delete_selected_confirm',
        'delete_selected_success' => 'abnmt.theater::lang.backgrounds.delete_selected_success',
        'delete_selected_empty' => 'abnmt.theater::lang.backgrounds.delete_selected_empty',
    ],
];