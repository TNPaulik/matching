<?php

$tablename = $_POST['tablename'];
$fieldname = $_POST['fieldname'];

require 'connect.php';

$sql = "SELECT $fieldname FROM $tablename WHERE $fieldname != '' ORDER BY RAND() LIMIT 1;";
$res = $GLOBALS['cons'][1]->query($sql);
$data = $res->fetch_all();
echo json_encode($data);
