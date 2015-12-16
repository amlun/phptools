<?php

namespace Amlun\Tools\StringFilter;

/**
 * 字符串移除
 *
 * @author lunweiwei
 * @package package_name
 *         
 */
class Remove extends Replace {
	public function __construct($pattern) {
		parent::__construct ( $pattern, '' );
	}
}