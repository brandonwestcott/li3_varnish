<?php
namespace li3_varnish\extensions\util;

use lithium\core\Libraries;
use lithium\core\Environment;
use li3_varnish\extensions\util\Varnish;

class Varnish extends \lithium\core\StaticObject {

	protected static $_config = array();

	protected static $_defaults = array(
		'esiUrl' => '/esi',
		'template' => '<esi:include src="{:url}"/><esi:remove>{:content}</esi:remove>',
		'headers' => array(
			'cache' => 'Cache-Control',
			'expires' => 'Expires',
			'esi' => 'Esi-Enabled',
			'ttl' => 'TTL',
			'passthrough' => 'Passthrough-Cache',
		),	
		'defaults' => array(
			'esi' => false,
			'expire' => null,
			'ttl' => null,
			'passthrough' => false,
		)
	);

	public static function config($name = null) {
		if(empty(self::$_config)){
			$config = Libraries::get('li3_varnish');

			$env = Environment::get();

			if(isset($config[$env])){
				$config += $config[$env];
				unset($config[$env]);
			}

			foreach($config as $k => $v){
				if(isset(self::$_defaults[$k]) && is_array(self::$_defaults[$k])){
					$config[$k] += self::$_defaults[$k];
				}
			}
			self::$_config = $config + self::$_defaults;
		}

		if(isset($name)){
			if(isset(self::$_config[$name])){
				return self::$_config[$name];
			} else {
				return null;
			}
		}

		return self::$_config;
	}

	// TODO add support for Controller only lookup
	public static function cache($name = null, $default = false){
		$config = self::config();

		if(isset($name)){
			$controllerName = array_slice(explode('::', $name), -1, 1);
			foreach(array($name, $controllerName[0]) as $key){
				if($config['cache'] === true || in_array($key, $config['cache']) || $unique = isset($config['cache'][$key])){
					if(isset($unique) && $unique == true){
						return $config['cache'][$key] + $config['defaults'];
					} else {
						return $config['defaults'];
					}
				}
			}

			if($default === true){
				return $config['defaults'];
			}
			return null;			
		}

		return $config['cache'];
	}

	// get all Varnish headers
	public static function headers($cache = array()){
		$headers = array();

		if(!empty($cache)){
			$headerKeys = array_filter(self::config('headers'));

			if($cache['esi'] == true && isset($headerKeys['esi'])){
				$headers[$headerKeys['esi']] =  $cache['esi'];
			}
			if($cache['passthrough'] == false && isset($headerKeys['passthrough'])){
				$headers[$headerKeys['passthrough']] = false;
			}
			if(isset($cache['ttl']) && isset($headerKeys['ttl'])){
				$headers[$headerKeys['ttl']] = $cache['ttl'];
			}
			if(isset($headerKeys['cache'])){
				$headers[$headerKeys['cache']] =  self::cacheControl($cache['expire']);
			}
			if(isset($headerKeys['expires'])){
				$headers[$headerKeys['expires']] =  self::expires($cache['expire']);		
			}
			if(isset($cache['headers']) && !empty($cache['headers'])){
				$headers += (array) $cache['headers'];
			}
		}

		return $headers;

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