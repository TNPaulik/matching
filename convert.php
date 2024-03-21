<?php

$jsonDesc = file_get_contents('matches/karat.json');
$desc = json_decode($jsonDesc);

$newDesc = new stdClass();

foreach ($desc as $key => $val) {
    $newKey = '{'.$key.'}';
    $newDesc->$key = '{'.$val.'}';
}

file_put_contents('matches/karat.json', json_encode($newDesc));

