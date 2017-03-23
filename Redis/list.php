<?php

$redis = new \Redis();

$redis->connect("127.0.0.1", "6379");

// order list to arrange

$redis->delete("list1");

$redis->lPush("list1", "A");
$redis->lPush("list1", "B");
$redis->lPush("list1", "C");

$value = $redis->rPop("list1");

var_dump($value);
