<?php

namespace Amlun\Tools;

/**
 * 字符串过滤器
 *
 * @author lunweiwei
 * @package package_name
 *         
 */
interface IStringFilter {
	/**
	 * 运行字符串过滤程序
	 *
	 * @param string $str        	
	 */
	public function run($str);
}