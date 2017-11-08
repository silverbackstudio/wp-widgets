<?php

namespace Svbk\WP\Widgets\Form;

use Svbk\WP\Helpers;
use Svbk\WP\Widgets\Base;

class Contact extends Form {

	public $id_base = 'svbk_contact';
	public $action = 'wContact';
	public $formClass = '\Svbk\WP\Helpers\Form\Contact';


	/**
	 * @inheritdoc
	 */
	protected function title() {
		return __( 'Contact', 'svbk-widgets' );
	}

	/**
	 * @inheritdoc
	 */
	protected function args() {
		$args = parent::args();
		$args['description'] =  __( 'Send a mail to recipient', 'svbk-widgets' );
		
		return $args;
	}


}
