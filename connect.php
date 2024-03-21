<?php

if (file_exists(__DIR__ . '/connectDev.php')) {
    include __DIR__ . '/connectDev.php';
} else {
    $GLOBALS['dbs'] = [
        'd1',
        'd2'
    ];

    $GLOBALS['servers'] = [
        'w.x.com',
        'w.y.com'
    ];

    $GLOBALS['cons'] = [
        new mysqli($GLOBALS['servers'][0], 'd1', ''),
        new mysqli($GLOBALS['servers'][1], 'd2', '')
    ];
}