<?php

$redis = new \Redis();

$redis->connect("127.0.0.1", "6379");

// string op
// cache json
// clear to confirm it is null
$redis->delete("string1");

$redis->set("string1", "value1");

$value1 = $redis->get("string1");

var_dump($value1); // value1


$redis->set("string1", 4);
$redis->incr("string1", 2);
$value = $redis->get("string1");
var_dump($value); // 6
