<?php
/**
 * @var shopSyrrepordersPlugin $this
 */

$files = [
    '/js/jqplot.barRenderer.min.js',
    '/js/jqplot.categoryAxisRenderer.min.js',
    '/js/jqplot.pointLabels.min.js',
    '/lib/actions/shopSyrrepordersPluginBackendSetreppref.controller.php',
    '/contributors.txt',
    '/README.md'
];

foreach ($files as $file) {
    try {
        waFiles::delete($this->path . $file);
    } catch (Exception $e) {

    }
}
