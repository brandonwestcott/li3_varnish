<?php
namespace li3_varnish\extensions\util;

use lithium\core\Libraries;
use lithium\core\Environment;
use li3_varnish\extensions\util\Varnish;

class Varnish extends \lithium\core\StaticObject {

	protected static $_config = array();

	protected static $_defaults = array(
		'defaults' => array(
			'esi' => false,
			'expire' => null,
		)
	);

	public static function config() {
		if(empty(self::$_config)){
			$config = Libraries::get('li3_varnish');

			$env = Environment::get();

			if(isset($config[$env])){
				$config += $config[$env];
				unset($config[$env]);
			}

			self::$_config = $config + self::$_defaults;
		}

		return self::$_config;
	}

	// TODO add support for Controller only lookup
	public static function cache($name = null, $default = false){
		$config = self::config();

		if(isset($name)){
			if($config['cache'] === true || in_array($name, $config['cache']) || $unique = isset($config['cache'][$name])){
				if(isset($unique) && $unique == true){
					return $config['cache'][$name] + $config['defaults'];
				} else {
					return $config['defaults'];
				}
			}
			if($default === true){
				return $config['defaults'];
			}
			return null;			
		}

		return $config['cache'];
	}


	// Cache-Control: max-age=3600
	public static function cacheControl($time){
		if(!is_int($time)){
			$time = strtotime($time) - strtotime('now');
		}
		return 'max-age='.$time;
	}

	// Expires: Tue, 15 May 2007 07:19:00 GMT
	public static function expires($time){
		if(is_int($time)){
			$time = strtotime('now') + $time;
		} else {
			$time = strtotime($time);
		}
		return gmdate('D, d M Y H:i:s', $time);
	}

}