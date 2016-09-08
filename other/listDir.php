<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 16/9/5
 * Time: 下午6:31
 */

/**
 * @param $dir
 */
function listDir($dir)
{
    echo $dir . PHP_EOL;
    if (is_dir($dir)) {
        $sub_dirs = scandir($dir);
        foreach ($sub_dirs AS $sub_dir) {
            if ($sub_dir == '.' || $sub_dir == '..') {
                continue;
            }
            listDir($dir . DIRECTORY_SEPARATOR . $sub_dir);
        }
    }
}

listDir('/data/logs');