<?php

include "database.php";

$db = new DB();
$db->open('dat');

$startTime = microtime(true);

// insert 
//for ($index = 0; $index < 10; $index++) {
    //$db->add('key'.$index, 'value'.$index);
//}

// search
for ($index = 0; $index < 10; $index++) {
    $data = $db->get('key'.$index);

    echo $data.'<br>';
}

$endTime = microtime(true);

echo 'Cost Time : ' . ($endTime - $startTime);

$db->close();

