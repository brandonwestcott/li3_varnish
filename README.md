# [Lithium PHP](http://lithify.me) Plugin for easy Varnish support

~~~ php
Libraries::add('li3_varnish', array(
	'development' => array(
		'esiUrl' => '/esi',
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

In the view, you have two options to render items as ESI partials. Either pass in esi = true into the options of a view render, or use the esi helper as a short cut.
~~~ php
<?=$this->_render("element", "login", array(), array('esi' => true)); ?>

or

<?=$this->esi->_render("element", "login"); ?>
~~~
