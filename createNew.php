<?php

$name = $_GET['name'];

file_put_contents('matches/karat/'.$name.'.json', '{"matches":{}}');