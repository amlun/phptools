<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 16/9/5
 * Time: 下午6:51
 */

/**
 * @param $str
 * @return int
 */
function atoi($str)
{
    $arr = str_split($str);
    $sign = null;
    $result = 0;
    foreach ($arr AS $item) {
        if (!isset($sign) && in_array($item, ['-', '+'])) {
            $sign = ($item == '-');
        }

        if (is_numeric($item)) {
            $result = $result * 10 + $item;
        }
    }
    return $sign ? -$result : $result;
}


$res = atoi('1278561');
var_dump($res);