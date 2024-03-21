<?php

$database1 = 'potschien';
$username1 = 'root';
$password1 = '';
$database2 = 'db_guh';
$username2 = 'root';
$password2 = '';

$db1 = new PDO('mysql:host=localhost;dbname='.$database1, $username1, $password1);
$db2 = new PDO('mysql:host=localhost;dbname='.$database2, $username2, $password2);

$tables1 = array();
$tables2 = array();

// Read IDX and DICT files
$idx_file = fopen('ger_eng.idx', 'rb');
$dict_file = fopen('ger_eng.dict', 'rb');

// Read signature
$signature = fread($idx_file, 4);

// Read number of words
$num_words = unpack('N', fread($idx_file, 4))[1];

// Read word offset and size
$word_offset = array();
$word_size = array();
for ($i = 0; $i < $num_words; $i++) {
    $word_offset[] = unpack('N', fread($idx_file, 4))[1];
    $word_size[] = unpack('N', fread($idx_file, 4))[1];
}

// Read word data
$word_dict = array();
for ($i = 0; $i < $num_words; $i++) {
    fseek($dict_file, $word_offset[$i]);
    $word_data = fread($dict_file, $word_size[$i]);
    $word_dict[$word_data] = $word_data;
}
echo "here";
die();

$result = $db1->query("SHOW TABLES");
while ($row = $result->fetch()) {
    $tables1[] = $row[0];
}

$result = $db2->query("SHOW TABLES");
while ($row = $result->fetch()) {
    $tables2[] = $row[0];
}

foreach ($tables1 as $table1) {
    if(array_key_exists($table1, $word_dict)){
        $translated_table2 = $word_dict[$table1];
    }else{
        foreach ($tables2 as $table2) {
            similar_text($table1, $table2, $percent);
            if ($percent > 80) {
                $translated_table2 = $table2;
                break;
            }
        }
    }

    $columns1 = array();
    $columns2 = array();

    $result = $db1->query("SHOW COLUMNS FROM $table1");
    while ($row = $result->fetch()) {
        $columns1[] = $row['Field'];
    }

    $result = $db2->query("SHOW COLUMNS FROM $translated_table2");
    while ($row = $result->fetch()) {
        $columns2[] = $row['Field'];
    }

    foreach ($columns1 as $column1) {
        if(array_key_exists($column1, $word_dict)){
            $translated_column2 = $word_dict[$column1];
        }else{
            foreach ($columns2 as $column2) {
                similar_text($column1, $column2, $percent);
                if ($percent > 80) {
                    $translated_column2 = $column2;
                    break;
                }
            }
        }
        echo "Matched field: $table1.$column1 => $translated_table2.$translated_column2\n";
    }
}

fclose($idx_file);
fclose($dict_file);