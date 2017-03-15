<?php
date_default_timezone_set("Asia/Shanghai");
$validTime = "1458264408";

echo $validTime;
echo "<br>";
$validTime = strtotime("+1 year", $validTime);
echo $validTime;
echo "<br>";
echo date('Y-m-d', $validTime);
