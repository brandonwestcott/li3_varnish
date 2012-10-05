# [Lithium PHP](http://lithify.me) Plugin for easy Varnish support


~~~ php
Libraries::add('li3_varnish', array(
	'development' => array(
		'defaults' => array(
			'esi' => true,
			'expire' => '+1 days'
		),
		'cache' => array(
			'SomeController::index' => array(
				'expire' => '+2 days',
				'esi' => true,
			),
			'SomeController::show'
		),
	)
));
~~~