<?php
use lithium\core\Libraries;
use lithium\action\Dispatcher;
use li3_varnish\extensions\util\Varnish;

Dispatcher::applyFilter('_call', function($self, $params, $chain) {
	$response = $chain->next($self, $params, $chain);

	$cacheKey = $params['request']->params['controller'].'Controller::'.$params['request']->params['action'];

	if(isset($response->varnish) && !empty($response->varnish)){
		$cache = Varnish::cache($cacheKey, true);
		if(is_array($response->varnish)){
			$cache += $response->varnish;
		}
	} else {
		$cache = Varnish::cache($cacheKey);
	}

	if(!empty($cache)){
		if($cache['esi'] == true){
			$response->headers('Esi-Enabled', '1');
		}
		$response->headers('Cache-Control', Varnish::cacheControl($cache['expire']));
		$response->headers('Pragma', 'public');
		$response->headers('Expires', Varnish::expires($cache['expire']));
	}

	return $response;
});