<?php

namespace li3_varnish\controllers;
use lithium\template\View;

class EsiController extends \lithium\action\Controller {

	public function show(){

		$View = new View(array(
			'paths' => array(
				'element'  => '{:library}/views/elements/{:template}.{:type}.php'
			),
			'request' => $this->request,
		));

		echo $View->render($this->request->params['type'], array(), array('template' => $this->request->params['name']));
		die();
	}

}

?>