<?php

namespace Svbk\WP\Widgets\Form;

use Svbk\WP\Helpers;
use Svbk\WP\Widgets\Base;

class Optin extends Form {

	public $id_base = 'svbk_optin';

	public $action = 'wOptin';
	public $formClass = '\Svbk\WP\Helpers\Form\Download';

	/**
	 * @inheritdoc
	 */
	protected function title() {
		return __( 'Opt-in', 'svbk-widgets' );
	}

	/**
	 * @inheritdoc
	 */
	protected function args() {
		$args = parent::args();
		$args['description'] =  __( 'Subscribe to receive a file vie email', 'svbk-widgets' );
		
		return $args;
	}

	/**
	 * @inheritdoc
	 */
	public function form( $instance ) {
		
		parent::form( $instance );
		
		$this->fileField( 'file', $this->fieldValue( $instance, 'file' ), __( 'Download File ID', 'svbk-widgets' ) . ':' );
	}

	/**
	 * @inheritdoc
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = parent::update( $new_instance, $old_instance );

		$instance['file'] = $this->sanitizeField( $new_instance, 'file', 'intval' );

		return $instance;
	}

}
