<?php

return [
    'name' => 'Additional Reports',
    'description' => 'More reports',
    'vendor' => 670917,
    'version' => '2.2.2',
    'shop_settings' => true,
    'frontend' => false,
    'icons' => [
        16 => 'img/actions-office-chart-line-percentage-icon.png'
    ],
    'handlers' => [
        'backend_reports' => 'backendReports',
    ],
    'locale' => ['en_US', 'ru_RU']
];
