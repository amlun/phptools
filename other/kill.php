<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 16/9/5
 * Time: 下午7:03
 */

$n = 10;//初始人数
$m = 2;//数到几要杀掉的人
echo "总共有" . $n . "个人\r\n";
echo "数到" . $m . "就要被杀死\r\n";
$persons = range(1, $n);//生成人员数组

while ($n > 0) {
    echo implode(",", $persons) . "\r\n";//每次队列的顺序
    if ($n >= $m) {
        $key = $m;// 当前队列人数不小于m的时候杀死第m个人
    } elseif ($n > 1) {
        $key = $m % $n == 0 ? $n : $m % $n; //当前队列中人数小于m并且大于1的时候要杀死的人
        // 如果m是n的倍数，则杀死第n个人，如果m不是n的倍数，则杀死第m%n个人
    } else {
        echo "The last person is " . $persons[$n - 1] . "\r\n";//最后活下来的人
        break;
    }
    echo "kill....." . $persons[$key - 1] . "\r\n";
    unset($persons[$key - 1]);// 把要杀死的人移除数组
    // $key-1之前的数据放到数组最后
    $persons = array_merge(array_slice($persons, $key - 1, $n - $key), array_slice($persons, 0, $key - 1));
    //重新整理数组
    array_values($persons);
    $n--;
}