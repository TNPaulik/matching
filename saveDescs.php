<?php
$matches = (array)json_decode($_POST['matches']);
$theirr = $_POST['their'];
foreach($matches AS $our => $their) {
    if ($our === '' || $their === '') {
        unset($matches[$our]);
    }
}
ksort($matches);
$data = new stdClass();
$data->matches = (object)$matches;
$json = json_encode($data, JSON_PRETTY_PRINT);
file_put_contents(__DIR__ . '/matches/karat/'.$theirr.'.json', $json);
echo $json;