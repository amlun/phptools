<?php
namespace Amlun\Tools;

use Amlun\Tools\Bloom\Hash;

/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 16/2/26
 * Time: 下午6:10
 */
class Bloom
{
    /**
     * Bloom集合
     * @var mixed
     */
    private $_set;

    /**
     * hash数组
     * @var Hash[]
     */
    private $_hashes;

    /**
     * 允许的错误率
     * @var float
     */
    private $_error_chance = 0.001;

    /**
     * Bloom集合的大小
     * @var int
     */
    private $_set_size;

    /**
     * hash数量
     * @var int
     */
    private $_hash_count;

    /**
     * 条目的数量
     * @var int
     */
    private $_entries_count;

    /**
     * 最大的条目数量
     * @var int
     */
    private $_entries_max = 100;

    /**
     * 是否统计条目数量
     * @var bool
     */
    private $_counter = false;

    /**
     * 统计数量,可以添加字符支持最多重复量
     * @var string
     */
    public $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct($setup = null)
    {
        isset($setup['entries_max']) && $this->_entries_max = $setup['entries_max'];
        isset($setup['error_chance']) && $this->_error_chance = $setup['error_chance'];
        isset($setup['set_size']) && $this->_set_size = $setup['set_size'];
        isset($setup['hash_count']) && $this->_hash_count = $setup['hash_count'];
        isset($setup['counter']) && $this->_counter = $setup['counter'];

        if (!$this->_set_size)
            $this->_set_size = -round(($this->_entries_max * log($this->_error_chance)) / pow(log(2), 2));

        if (!$this->_hash_count)
            $this->_hash_count = round($this->_set_size * log(2) / $this->_entries_max);

        for ($i = 0; $i < $this->_hash_count; $i++) {
            $this->_hashes[] = new Hash($this->_hashes);
        }

        $this->_set = str_repeat('0', $this->_set_size);

        return $this;
    }

    public function set($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed AS $value) {
                $this->set($value);
            }
        } else {
            for ($i = 0; $i < $this->_hash_count; $i++) {
                if ($this->_counter === false) {
                    $this->_set[$this->_hashes[$i]->invoke($mixed, $this->_set_size)] = 1;
                } else {
                    $this->counter($this->_hashes[$i]->invoke($mixed, $this->_set_size), 1);
                }
                $this->_entries_count++;
            }
        }
    }

    public function has($mixed, $boolean = true)
    {
        $result = [];
        /**
         *    In case of array given will be returned array,
         * and method call's itself recursively with array's elements
         */
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value)
                $result[$key] = $this->has($value, $boolean);
            return $result;
        } else {
            $c = 0;
            for ($i = 0; $i < $this->_hash_count; $i++) {
                if ($this->_counter === false)
                    $value = $this->_set[$this->_hashes[$i]->invoke($mixed, $this->_set_size)];
                else
                    $value = $this->counter($this->_hashes[$i]->invoke($mixed, $this->_set_size), 0, true);
                /**
                 *    $boolean parameter allows to choose what to return
                 * boolean or the percent of entries pass
                 */
                if ($boolean && !$value)
                    return false;
                elseif ($boolean === false)
                    $c += ($value) ? 1 : 0;
            }
            return ($boolean === true) ? true : $c / $this->_hash_count;
        }
    }

    /**
     * Works with special string in counter mode
     *
     * @param int $position
     * @param int $add
     * @param boolean $get
     * @return mixed
     */
    public function counter($position, $add = 0, $get = false)
    {
        /**
         *    Return value or recalculate with alphabet
         */
        if ($get === true)
            return $this->_set[$position];
        else {
            $in_a = strpos($this->alphabet, $this->_set[$position]);
            $this->_set[$position] = ($this->alphabet[$in_a + $add] != null) ? $this->alphabet[$in_a + $add] : $this->_set[$position];
        }
        return true;
    }
}