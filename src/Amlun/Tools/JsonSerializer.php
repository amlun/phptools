<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 15/12/31
 * Time: 下午3:36
 */

namespace Amlun\Tools;

use Amlun\Tools\JsonSerializer\Exception;

/**
 * Class JsonSerializer
 * @package Amlun\Tools
 */
class JsonSerializer
{
	const CLASS_IDENTIFIER_KEY = '@type';
	const FLOAT_ADAPTER = 'JsonSerializerFloatAdapter';
	/**
	 * Storage for object, Used for recursion
	 *
	 * @var \SplObjectStorage
	 */
	protected $objectStorage;
	/**
	 * Object mapping for recursion
	 *
	 * @var array
	 */
	protected $objectMapping = array();
	/**
	 * Object mapping index
	 *
	 * @var int
	 */
	protected $objectMappingIndex = 0;

	/**
	 * Support PRESERVE_ZERO_FRACTION json option
	 *
	 * @var boolean
	 */
	protected $preserveZeroFractionSupport;

	public function __construct()
	{
		$this->preserveZeroFractionSupport = defined('JSON_PRESERVE_ZERO_FRACTION');
	}

	/**
	 * 序列化
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function serialize($value)
	{
		$this->reset();
		$serializeData = $this->serializeData($value);
		$options = $this->jsonOptions();
		$encoded = json_encode($serializeData, $options);
		$encoded = $this->processEncode($encoded);
		return $encoded;
	}

	/**
	 * 序列化数据,迭代处理
	 *
	 * @param mixed $data
	 * @return mixed
	 * @throws Exception
	 */
	protected function serializeData($data)
	{
		if (is_scalar($data) || is_null($data)) {
			return $data;
		}
		if (is_array($data)) {
			return array_map(array($this, __FUNCTION__), $data);
		}
		if (is_resource($data)) {
			throw new Exception('Resource is not support');
		}
		if ($data instanceof \Closure) {
			throw new Exception('Closure is not support');
		}
		// 如果是Object,特殊处理
		return $this->serializeObject($data);
	}

	/**
	 * 返回json encode 选项
	 *
	 * @return int
	 */
	protected function jsonOptions()
	{
		$options = JSON_UNESCAPED_UNICODE;
		if ($this->preserveZeroFractionSupport) {
			$options |= JSON_PRESERVE_ZERO_FRACTION;
		}
		return $options;
	}

	/**
	 * 序列化对象
	 *
	 * @param object $object
	 * @return array
	 */
	protected function serializeObject($object)
	{
		$ref = new \ReflectionClass($object);
		if ($this->objectStorage->contains($object)) {
			return array(static::CLASS_IDENTIFIER_KEY => '@' . $this->objectStorage[$object]);
		}
		$this->objectStorage->attach($object, $this->objectMappingIndex++);
		$paramsToSerialize = $this->getObjectProperties($ref, $object);
		$data = array(static::CLASS_IDENTIFIER_KEY => $ref->getName());
		$data += array_map(array($this, 'serializeData'), $this->extractObjectData($object, $ref, $paramsToSerialize));
		return $data;
	}

	/**
	 * 返回对象的所有属性
	 *
	 * @param \ReflectionClass $ref
	 * @param object $object
	 * @return array
	 */
	protected function getObjectProperties($ref, $object)
	{
		if (method_exists($object, '__sleep')) {
			return $object->__sleep();
		}
		$props = array();
		foreach ($ref->getProperties() as $prop) {
			$props[] = $prop->getName();
		}
		return array_unique(array_merge($props, array_keys(get_object_vars($object))));
	}

	/**
	 * 解析对象数据
	 *
	 * @param object $value
	 * @param \ReflectionClass $ref
	 * @param array $properties
	 * @return array
	 */
	protected function extractObjectData($value, $ref, $properties)
	{
		$data = array();
		foreach ($properties as $property) {
			try {
				$propRef = $ref->getProperty($property);
				$propRef->setAccessible(true);
				$data[$property] = $propRef->getValue($value);
			} catch (\ReflectionException $e) {
				$data[$property] = $value->$property;
			}
		}
		return $data;
	}

	/**
	 * 处理encode之后的数据
	 *
	 * @param $encoded
	 * @return mixed
	 */
	protected function processEncode($encoded)
	{
		if (!$this->preserveZeroFractionSupport) {
			$encoded = preg_replace('/"' . static::FLOAT_ADAPTER . '\((.*?)\)"/', '\1', $encoded);
		}
		return $encoded;
	}

	/**
	 * 反序列化
	 *
	 * @param string $string
	 * @return mixed
	 */
	public function unserialize($string)
	{
		$this->reset();
		$data = json_decode($string, true);
		return $this->unserializeData($data);
	}

	/**
	 * 反序列化数据
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	protected function unserializeData($value)
	{
		if (is_scalar($value) || is_null($value)) {
			return $value;
		}
		// 如果是object,则特殊处理
		if (isset($value[static::CLASS_IDENTIFIER_KEY])) {
			return $this->unserializeObject($value);
		}
		return array_map(array($this, __FUNCTION__), $value);
	}

	/**
	 * 反序列化为对象
	 *
	 * @param array $value
	 * @return object
	 * @throws Exception
	 */
	protected function unserializeObject($value)
	{
		$className = $value[static::CLASS_IDENTIFIER_KEY];
		unset($value[static::CLASS_IDENTIFIER_KEY]);
		if ($className[0] === '@') {
			$index = substr($className, 1);
			return $this->objectMapping[$index];
		}
		if (!class_exists($className)) {
			throw new Exception('Unable to find class ' . $className);
		}
		if ($className === 'DateTime') {
			$obj = $this->restoreUsingUnserialize($className, $value);
			$this->objectMapping[$this->objectMappingIndex++] = $obj;
			return $obj;
		}
		$ref = new \ReflectionClass($className);
		$obj = $ref->newInstanceWithoutConstructor();
		$this->objectMapping[$this->objectMappingIndex++] = $obj;
		foreach ($value as $property => $propertyValue) {
			try {
				$propRef = $ref->getProperty($property);
				$propRef->setAccessible(true);
				$propRef->setValue($obj, $this->unserializeData($propertyValue));
			} catch (\ReflectionException $e) {
				$obj->$property = $this->unserializeData($propertyValue);
			}
		}
		if (method_exists($obj, '__wakeup')) {
			$obj->__wakeup();
		}
		return $obj;
	}

	/**
	 * 用serialize和unserialize处理一些特殊对象
	 *
	 * @param $className
	 * @param $attributes
	 * @return mixed
	 */
	protected function restoreUsingUnserialize($className, $attributes)
	{
		$obj = (object)$attributes;
		$serialized = preg_replace('|^O:\d+:"\w+":|', 'O:' . strlen($className) . ':"' . $className . '":', serialize($obj));
		return unserialize($serialized);
	}

	/**
	 * reset the object info
	 */
	protected function reset()
	{
		$this->objectStorage = new \SplObjectStorage();
		$this->objectMapping = array();
		$this->objectMappingIndex = 0;
	}
}