<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 16/8/30
 * Time: 上午11:15
 */

$source = [-1, 1, 2, -5, 7];

function maxSub($source = [])
{
    $max_item = max($source);
    if ($max_item <= 0) {
        return $max_item;
    }

    $max_sub_sum = 0;
    $sum = 0;
    $count = count($source);

    for ($i = 0; $i < $count; $i++) {
        if ($sum <= 0) {
            $sum = $source[$i];
        } else {
            $sum += $source[$i];
        }
        if ($max_sub_sum < $sum) {
            $max_sub_sum = $sum;
        }
    }

    return $max_sub_sum;
}

$max_sub_sum = maxSub($source);
var_dump($max_sub_sum);