<?php

require 'connect.php';

$res = $GLOBALS['cons'][$dbIndex]->query("
        select
            COLUMN_NAME as 'Field',
            COLUMN_TYPE as 'Type',
            IS_NULLABLE as 'Null',
            COLUMN_KEY as 'Key',
            COLUMN_DEFAULT as 'Default',
            EXTRA as 'Extra'
        from
            INFORMATION_SCHEMA.COLUMNS
        where
            TABLE_NAME = '$table' and
            TABLE_SCHEMA = '".$GLOBALS['dbs'][$dbIndex]."'
        
        order by Field;
    ");

return $res->fetch_all();