<?php

$redis = new \Redis();

$redis->connect("127.0.0.1", "6379");

$redis->delete("zset");

$redis->zAdd("zset", 100, "Jim");
$redis->zAdd("zset", 90, "Lily");
$redis->zAdd("zset", 95, "Andrew");

$value = $redis->zRange("zset", 0, -1); // low to high
var_dump($value);

$value = $redis->zRevRange("zset", 0, -1); // high to low
var_dump($value);
