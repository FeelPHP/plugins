<?php

$redis = new \Redis();

$redis->connect("127.0.0.1", "6379");

$redis->delete("driver1");

$redis->hSet("driver1", "name", "Jim");
$redis->hSet("driver1", "age", 25);
$redis->hSet("driver1", "gender", 1);

$value = $redis->hGet("driver1", "name");
var_dump($value);

$value = $redis->hMGet("driver1", array("name", "age"));
var_dump($value);

