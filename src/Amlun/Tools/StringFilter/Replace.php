<?php

namespace Amlun\Tools\StringFilter;

use Amlun\Tools\StringFilter;

/**
 * 字符串替换
 *
 * @author lunweiwei
 * @package package_name
 *         
 */
class Replace extends StringFilter {
	public function __construct($pattern, $replacement) {
		$this->_pattern = $pattern;
		$this->_callback = function ($matches) use($replacement) {
			return $replacement;
		};
	}
}