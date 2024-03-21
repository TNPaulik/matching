<?php

$GLOBALS['dbs'] = [
    'ruder',
    'demo'
];

$GLOBALS['servers'] = [
    'localhost',
    'localhost'
];

$GLOBALS['cons'] = [
    new mysqli($GLOBALS['servers'][0], 'root', ''),
    new mysqli($GLOBALS['servers'][1], 'root', '')
];