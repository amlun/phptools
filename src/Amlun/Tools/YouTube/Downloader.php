<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 15/12/16
 * Time: 下午2:56
 */

namespace YouTube;

class Downloader
{
	const INFO_URL = 'http://www.youtube.com/get_video_info?video_id=%s';
	private static $_instances;
	private $_id = null;
	private $_info = null;

	/**
	 * @param $video_id
	 * @return Downloader
	 */
	public static function instance($video_id)
	{
		if (!isset(self::$_instances[$video_id])) {
			self::$_instances[$video_id] = new self($video_id);
		}
		return self::$_instances[$video_id];
	}

	private function __construct($video_id)
	{
		$this->_id = $video_id;
		$this->_init();
	}

	private function _init()
	{
		$ch = curl_init();
		$url = sprintf(self::INFO_URL, $this->_id);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=value');
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			throw new \Exception('read error when curl');
		}
		curl_close($ch);
		parse_str($response, $this->_info);
	}

	public function get_video_info()
	{
		return $this->_info;
	}

	public function get_avail_formats()
	{
		if (!isset($this->_info['url_encoded_fmt_stream_map'])) {
			throw new \Exception('No encoded format stream found.');
		}
		$my_formats_array = explode(',', $this->_info['url_encoded_fmt_stream_map']);
		if (count($my_formats_array) == 0) {
			throw new \Exception('No format stream map found - was the video id correct?');
		}
		$avail_formats[] = '';
		$i = 0;
		$ipbits = $ip = $itag = $sig = $quality = $type = $url = '';
		$expire = time();
		foreach ($my_formats_array as $format) {
			// $url, $itag, $type, $quality ...
			parse_str($format);
			$avail_formats[$i]['itag'] = $itag;
			$avail_formats[$i]['quality'] = $quality;
			$type = explode(';', $type);
			$avail_formats[$i]['type'] = $type[0];
			$avail_formats[$i]['url'] = urldecode($url) . '&signature=' . $sig;
			// $expire, $ipbits, $ip ...
			parse_str(urldecode($url));
			$avail_formats[$i]['expires'] = date("G:i:s T", $expire);
			$avail_formats[$i]['ipbits'] = $ipbits;
			$avail_formats[$i]['ip'] = $ip;
			$i++;
		}
		return $avail_formats;
	}
}