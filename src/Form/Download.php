<?php

namespace Svbk\WP\Widgets\Form;

use Svbk\WP\Helpers;
use Svbk\WP\Widgets\Base;

class Download extends Form {

	public $id_base = 'svbk_download_form';

	public $action = 'wDownload';
	public $formClass = '\Svbk\WP\Forms\Download';

	/**
	 * @inheritdoc
	 */
	protected function title() {
		return __( 'Download', 'svbk-widgets' );
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
