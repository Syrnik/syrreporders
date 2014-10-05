<?php

return array(
    'name' => 'Additional Reports',
    'description' => 'More reports',
    'vendor'=>670917,
    'version'=>'2.1.0',
    'shop_settings' => TRUE,
    'frontend'    => FALSE,
    'icons'=>array(
        16 => 'img/actions-office-chart-line-percentage-icon.png'
        ),
    'handlers' => array(
        'backend_reports' => 'backendReports',
    ),
    'locale' => array('en_US', 'ru_RU')
);
