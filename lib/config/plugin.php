<?php

return [
    'name' => 'Additional Reports',
    'description' => 'More reports',
    'vendor' => 670917,
    'version' => '2.3.0',
    'shop_settings' => true,
    'frontend' => false,
    'icons' => [
        16 => 'img/actions-office-chart-line-percentage-icon.png'
    ],
    'handlers' => [
        'backend_reports' => 'backendReports',
        'backend_extended_menu' => 'backendExtendedMenu'
    ],
    'locale' => ['en_US', 'ru_RU']
];
