<?php

return array(
    'name' => 'Orders Report',
    'description' => 'Statistics on orders',
    'vendor'=>670917,
    'version'=>'1.1.0',
    'shop_settings' => FALSE,
    'frontend'    => FALSE,
    'icons'=>array(
        16 => 'img/actions-office-chart-line-percentage-icon.png'
        ),
    'handlers' => array(
        'backend_reports' => 'backendReports',
    ),
    'locale' => array('en_US', 'ru_RU')
);
