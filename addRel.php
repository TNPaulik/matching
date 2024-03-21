<?php
$mt = $_POST['maintable'];
$pk = $_POST['primarykey'];
$ft = $_POST['foreigntable'];
$fk = $_POST['foreignkey'];

if (empty($mt) || empty($pk) || empty($ft) || empty($fk))
    return;

$rels = json_decode(file_get_contents(__DIR__ . '/matches/karat/info/rels.json'));
if (!is_object($rels))
    $rels = new stdClass();
if (!is_object($rels->$mt))
    $rels->$mt = new stdClass();
if (!is_object($rels->$mt->$ft))
    $rels->$mt->$ft = new stdClass();

$rels->$mt->$ft->$pk = $fk;

//ksort($rels);
$json = json_encode($rels, JSON_PRETTY_PRINT);
file_put_contents(__DIR__ . '/matches/karat/info/rels.json', $json);
echo $json;
