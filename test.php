<?php
    $alb='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz !@#$%^&*()_+-=/;';
    $int1=[42, 40, 55, 25, 36, 38, 46, 36, 42, 40, 23, 36, 48, 40];
    $int2=[71, 72, 21, 45, 36, 57, 36, 77, 47, 36, 49, 42, 77, 28, 55, 53, 44, 49, 42, 78];
foreach($int1 as $int){
	echo $alb[$int];
}
echo "\n";
foreach($int2 as $int){
        echo $alb[$int];
}
echo "\n";
?>
