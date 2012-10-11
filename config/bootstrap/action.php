<?php
use lithium\core\Libraries;
use lithium\action\Dispatcher;
use li3_varnish\extensions\util\Varnish;
use lithium\net\http\Media;
use lithium\net\http\Router;

// filter to set varnish headers
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
		$varnishHeaders = Varnish::headers($cache);
		foreach($varnishHeaders as $key => $val){
			$response->headers($key, $val);
		}
	}

	return $response;
});

// filter to set esi includes around partials
Media::applyFilter('view', function($self, $params, $chain) {	

	$view = $chain->next($self, $params, $chain);

	$view->applyFilter('_step', function($self, $params, $chain) {	

		$content = $chain->next($self, $params, $chain);

		if(isset($params['options']['esi']) && $params['options']['esi'] == true){
			if(!empty($content)){
				$content = \lithium\util\String::insert(Varnish::config('template'), array(
					'url' => Router::match(array('controller' => 'Esi', 'action' => 'show', 'type' => $params['step']['path'], 'name' => $params['options']['template'])),
					'content' => $content,
				));
			}
		}

		return $content;
	});

	return $view;
});

