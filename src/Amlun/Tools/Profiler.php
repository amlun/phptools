<?php

namespace Amlun\Tools;

/**
 * 性能统计
 *
 * @author lunweiwei
 * @package Amlun\Tools
 * @category Tools
 *          
 * @example <pre>
 *          $token1 = Profiler::start('resize', 'image1');
 *          resize($image1);
 *          Profile::stop($token1);
 *          $token2 = Profiler::start('resize', 'image2');
 *          resize($image2);
 *          Profile::stop($token2);
 *          $resize_stat = Profiler::group_stats ('resize');
 *          </pre>
 * @version 1.0
 *         
 */
class Profiler {
	/**
	 * 性能数据
	 *
	 * @var array
	 */
	protected static $_mark = [ ];
	/**
	 * 开始一个新的性能统计，返回唯一的token，结束的时候要使用该token
	 *
	 * @example $token1 = Profiler::start('resize', 'image1');
	 *          $token2 = Profiler::start('resize', 'image2');
	 * @param string $group
	 *        	分组名
	 * @param string $name
	 *        	标识名
	 * @return string $token
	 */
	public static function start($group, $name) {
		static $counter = 0;
		$token = 'profiler/' . base_convert ( $counter ++, 10, 32 );
		Profiler::$_mark [$token] = array (
				'group' => strtolower ( $group ),
				'name' => ( string ) $name,
				'start_time' => microtime ( TRUE ),
				'start_memory' => memory_get_usage (),
				'stop_time' => FALSE,
				'stop_memory' => FALSE 
		);
		return $token;
	}
	/**
	 * 停止性能统计
	 *
	 * @param string $token        	
	 * @return void
	 * @example Profiler::stop($token);
	 */
	public static function stop($token) {
		Profiler::$_mark [$token] ['stop_time'] = microtime ( TRUE );
		Profiler::$_mark [$token] ['stop_memory'] = memory_get_usage ();
	}
	/**
	 * 获取性能信息
	 *
	 * @param string $token        	
	 * @return array [group, name, start_time, start_memory, stop_time, stop_memory]
	 * @example $info = Profiler::info($token);
	 */
	public static function info($token) {
		return Profiler::$_mark [$token];
	}
	/**
	 * 删除某性能数据，最后结果不会出现在报表中
	 *
	 * @param string $token        	
	 * @return void
	 * @example Profiler::delete($token);
	 */
	public static function delete($token) {
		unset ( Profiler::$_mark [$token] );
	}
	/**
	 * 返回当前统计数据按分组排列对应的token
	 *
	 * @return array
	 * @example $groups = Profiler::groups();
	 */
	public static function groups() {
		$groups = array ();
		foreach ( Profiler::$_mark as $token => $mark ) {
			$groups [$mark ['group']] [$mark ['name']] [] = $token;
		}
		return $groups;
	}
	/**
	 * 返回性能统计数据
	 *
	 * @param array $tokens        	
	 * @return array [min, max, average, total]
	 * @example $stats = Profiler::stats($tokens);
	 */
	public static function stats(array $tokens) {
		$min = $max = array (
				'time' => NULL,
				'memory' => NULL 
		);
		$total = array (
				'time' => 0,
				'memory' => 0 
		);
		foreach ( $tokens as $token ) {
			list ( $time, $memory ) = Profiler::total ( $token );
			if ($max ['time'] === NULL or $time > $max ['time']) {
				$max ['time'] = $time;
			}
			if ($min ['time'] === NULL or $time < $min ['time']) {
				$min ['time'] = $time;
			}
			$total ['time'] += $time;
			if ($max ['memory'] === NULL or $memory > $max ['memory']) {
				$max ['memory'] = $memory;
			}
			if ($min ['memory'] === NULL or $memory < $min ['memory']) {
				$min ['memory'] = $memory;
			}
			$total ['memory'] += $memory;
		}
		$count = count ( $tokens );
		$average = array (
				'time' => $total ['time'] / $count,
				'memory' => $total ['memory'] / $count 
		);
		return array (
				'min' => $min,
				'max' => $max,
				'total' => $total,
				'average' => $average 
		);
	}
	/**
	 * 返回分组对应的性能统计数据
	 *
	 * @param string $groups        	
	 * @return array [min, max, average, total]
	 * @uses Profiler::groups
	 * @uses Profiler::stats
	 * @example $stats = Profiler::group_stats('resize');
	 */
	public static function group_stats($groups = NULL) {
		$groups = ($groups === NULL) ? Profiler::groups () : array_intersect_key ( Profiler::groups (), array_flip ( ( array ) $groups ) );
		$stats = array ();
		foreach ( $groups as $group => $names ) {
			foreach ( $names as $name => $tokens ) {
				$_stats = Profiler::stats ( $tokens );
				$stats [$group] [$name] = $_stats ['total'];
			}
		}
		$groups = array ();
		foreach ( $stats as $group => $names ) {
			$groups [$group] ['min'] = $groups [$group] ['max'] = array (
					'time' => NULL,
					'memory' => NULL 
			);
			$groups [$group] ['total'] = array (
					'time' => 0,
					'memory' => 0 
			);
			foreach ( $names as $total ) {
				if (! isset ( $groups [$group] ['min'] ['time'] ) or $groups [$group] ['min'] ['time'] > $total ['time']) {
					$groups [$group] ['min'] ['time'] = $total ['time'];
				}
				if (! isset ( $groups [$group] ['min'] ['memory'] ) or $groups [$group] ['min'] ['memory'] > $total ['memory']) {
					$groups [$group] ['min'] ['memory'] = $total ['memory'];
				}
				if (! isset ( $groups [$group] ['max'] ['time'] ) or $groups [$group] ['max'] ['time'] < $total ['time']) {
					$groups [$group] ['max'] ['time'] = $total ['time'];
				}
				if (! isset ( $groups [$group] ['max'] ['memory'] ) or $groups [$group] ['max'] ['memory'] < $total ['memory']) {
					$groups [$group] ['max'] ['memory'] = $total ['memory'];
				}
				$groups [$group] ['total'] ['time'] += $total ['time'];
				$groups [$group] ['total'] ['memory'] += $total ['memory'];
			}
			$count = count ( $names );
			$groups [$group] ['average'] ['time'] = $groups [$group] ['total'] ['time'] / $count;
			$groups [$group] ['average'] ['memory'] = $groups [$group] ['total'] ['memory'] / $count;
		}
		return $groups;
	}
	
	/**
	 * 获取性能统计结果
	 *
	 *
	 * @param string $token        	
	 * @return array [time, memory]
	 * @example list($time, $memory) = Profiler::total($token);
	 */
	public static function total($token) {
		$mark = Profiler::$_mark [$token];
		if ($mark ['stop_time'] === FALSE) {
			// The benchmark has not been stopped yet
			$mark ['stop_time'] = microtime ( TRUE );
			$mark ['stop_memory'] = memory_get_usage ();
		}
		$time = $mark ['stop_time'] - $mark ['start_time'];
		$memory = $mark ['stop_memory'] - $mark ['start_memory'];
		return array (
				$time,
				$memory 
		);
	}
}