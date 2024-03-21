<?php
$our = $_POST['our'];
$their = $_POST['their'];

if (empty($our) || empty($their))
    return;
$tms = json_decode(file_get_contents(__DIR__ . '/matches/karat/info/tms.json'));
if (!is_object($tms))
    $tms = new stdClass();
$tms->$our = $their;
$tms = (array)$tms;
ksort($tms);
$tms = (object)$tms;
$json = json_encode($tms, JSON_PRETTY_PRINT);
file_put_contents(__DIR__ . '/matches/karat/info/tms.json', $json);
echo $json;
