<?php
namespace Amlun\Tools\Bloom;
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 16/2/26
 * Time: 下午6:28
 */

class Hash
{
    /**
     * Seed for unification every HashObject
     * @var array
     */
    private $_seed;

    public function __construct($hashes)
    {
        $seeds = [];
        if ($hashes) {
            foreach ($hashes as $hash) {
                $seeds = array_merge((array)$seeds, (array)$hash->_seed);
            }
        }

        do {
            $hash = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
        } while (in_array($hash, $seeds));

        $this->_seed[] = $hash;
    }

    public function invoke($string, $size)
    {
        $string = mb_strtolower(strval($string), 'UTF-8');
        return abs(crc32(md5($this->_seed[0] . $string))) % $size;
    }
}