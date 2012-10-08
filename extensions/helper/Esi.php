<?php
namespace li3_varnish\extensions\helper;

class Esi extends \lithium\template\Helper {


	public function _render($type, $template, array $data = array(), array $options = array()) {
		$library = $this->_context->request()->library;
		$options += compact('library');
		$options['esi'] = true;
		return $this->_context->view()->render($type, $data + $this->_context->data(), compact('template') + $options);
	}


}