<?php

$GLOBALS['con'] = new mysqli('localhost', 'root', 'CXJTQVPEE5L5G6758ATVKK9D37A67NS6');
$GLOBALS['tables'] = [];
$db = 'db_guh_sample';

function getTables($name = 'db_guh_sample') {
    $res = $GLOBALS['con']->query("USE $name");
    $res = $GLOBALS['con']->query("SHOW TABLES");
    $data = $res->fetch_all();
    $GLOBALS['tables'] = [];
    foreach($data AS $table) {
        if (!empty($table[0])) {
            $GLOBALS['tables'][] = $table[0];
        }
    }
}

getTables($db);

foreach($GLOBALS['tables'] AS $table) {
    try {
        $res = $GLOBALS['con']->query("DELETE FROM $table WHERE ID > 10");#
        print_r($res);
    } catch (Exception $e) {
        print_r($e);
    }
}