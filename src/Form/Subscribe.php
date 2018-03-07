<?php

namespace Svbk\WP\Widgets\Form;

use Svbk\WP\Helpers;
use Svbk\WP\Widgets\Base;

class Subscribe extends Form {

	public $id_base = 'svbk_form_subscribe';

	public $action = 'wsubscribe';
	public $formClass = '\Svbk\WP\Helpers\Form\Subscribe';

	/**
	 * @inheritdoc
	 */
	protected function title() {
		return __( 'Subscribe', 'svbk-widgets' );
	}

	/**
	 * @inheritdoc
	 */
	protected function args() {
		$args = parent::args();
		$args['description'] =  __( 'Subscribe to a marketing list', 'svbk-widgets' );
		
		return $args;
	}

}
