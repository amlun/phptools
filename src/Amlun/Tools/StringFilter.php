<?php

namespace Amlun\Tools;

/**
 * 字符串过滤器
 *
 * @author lunweiwei
 * @package package_name
 *         
 */
class StringFilter implements IStringFilter {
	/**
	 * 要搜索的模式,可以是一个字符串或字符串数组
	 *
	 * @var string
	 */
	protected $_pattern;
	/**
	 * 回调函数
	 *
	 * @var callable
	 */
	protected $_callback;
	public function __construct($pattern, callable $callback) {
		$this->_pattern = $pattern;
		$this->_callback = $callback;
	}
	/**
	 *
	 * @see \Amlun\Tools\IStringFilter::run()
	 */
	public function run($str) {
		return preg_replace_callback ( $this->_pattern, $this->_callback, $str );
	}
}