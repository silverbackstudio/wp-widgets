<?php

namespace Svbk\WP\Widgets\Form;

use Svbk\WP\Helpers;
use Svbk\WP\Widgets\Base;

class Form extends Base {

	public $id_base = 'svbk_form_base';

	public $action = 'svbk_form_base';
	public $formClass = '\Svbk\WP\Helpers\Form\Submission';
	public $formParams = array();

	public $confirmMessage = '';

	public static $visibleOptions = array(
		'visible' => 'Visible',
		'collapse' => 'Collapse',
		'lightbox' => 'Lightbox',
	);

	public $renderOrder = array(
		'formBegin',
		'title',
		'intro',
		'input',
		'requiredNotice',
		'beginPolicySubmit',
		'policy',
		'submitButton',
		'endPolicySubmit',
		'messages',
		'formEnd',
	);

	public function hooks() {
		parent::hooks();

		add_action( 'init', array( $this, 'processSubmission' ) );
	}

	protected function title() {
		return __( 'Silverback Form', 'svbk-widgets' );
	}

	protected function args() {
		return array(
			'description' => __( 'Silverback Base Form', 'svbk-widgets' ),
		);
	}

	protected function submitUrl() {
		$base_url = set_url_scheme( home_url( '/' ) );
		return add_query_arg( 
			array(
				'svbkSubmit' => $this->action,
			), 
			$base_url
		);
	}

	protected function getForm() {

		$formClass = $this->formClass;
		
		$form = new $formClass;
		$form->field_prefix = $this->id_base;
		$form->action = $this->action;
		$form->submitUrl = $this->submitUrl();

		foreach ( $this->formParams as $property => $value ) {
			if ( ! property_exists( $form, $property ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$form->$property = array_merge( $form->$property, $value );
			} else {
				$form->$property = $value;
			}
		}		

		return $form;
	}

	public function processSubmission() {

		if ( filter_input( INPUT_GET, 'svbkSubmit', FILTER_SANITIZE_SPECIAL_CHARS ) !== $this->action ) {
			return;
		}

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'Content-Type: application/json' );
		send_nosniff_header();

		$form = $this->getForm( true );
		$form->processSubmission();
			
		$errors = $form->getErrors();

		echo $this->jsonResponse( $errors, $form );
		exit;
	}

	public function jsonResponse( $errors, $form ) {

		if ( ! empty( $errors ) ) {

			return json_encode( array(
					'prefix' => $this->id_base,
					'status' => 'error',
					'errors' => $errors,
				)
			);

			return false;
		}

		return json_encode(
			array(
				'prefix' => $this->id_base,
				'status' => 'success',
				'message' => $this->confirmMessage(),
			)
		);


		if ( ! $this->hardRedirect && $this->redirectTo ) {
			$response['redirect'] = $this->redirectTo;
		}

	}

	public function confirmMessage() {
		return $this->confirmMessage ?: __( 'Thanks for your request, we will reply as soon as possible.', 'svbk-shortcakes' );
	}

	/**
	 * @inheritdoc
	 */
	public function form( $instance ) {
		$this->textField( 'title', $this->fieldValue( $instance, 'title', __( 'Widget title', 'svbk-widgets' ) ), __( 'Title', 'svbk-widgets' ) . ':' );
		$this->textAreaField( 'description', $this->fieldValue( $instance, 'description', __( 'Description text', 'svbk-widgets' ) ), __( 'Description', 'svbk-widgets' ) . ':' );
		$this->textField( 'form_title', $this->fieldValue( $instance, 'form_title' ), __( 'Form Title', 'svbk-widgets' ) . ':' );		
		$this->selectField( 'hidden', $this->fieldValue( $instance, 'hidden' ), __( 'View mode', 'svbk-widgets' ) . ':', self::$visibleOptions );
		$this->textField( 'submit_button_label', $this->fieldValue( $instance, 'submit_button_label' ), __( 'Submit button', 'svbk-widgets' ) . ':' );
		$this->textField( 'privacy_link', $this->fieldValue( $instance, 'privacy_link' ), __( 'Privacy Link', 'svbk-widgets' ) . ':' );		
		$this->pageSelect( 'redirect_to', $this->fieldValue( $instance, 'redirect_to' ), __( 'Redirect To', 'svbk-widgets' ) . ':' );		
	}

	/**
	 * @inheritdoc
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		$instance['title'] = $this->sanitizeField( $new_instance, 'title' );
		$instance['description'] = $this->sanitizeField( $new_instance, 'description', 'wp_kses_post' );
		$instance['form_title'] = $this->sanitizeField( $new_instance, 'form_title' );
		$instance['privacy_link'] = $this->sanitizeField( $new_instance, 'privacy_link' );
		$instance['hidden'] = $this->sanitizeField( $new_instance, 'hidden' );
		$instance['submit_button_label'] = $this->sanitizeField( $new_instance, 'submit_button_label' );	
		$instance['redirect_to'] = $this->sanitizeField( $new_instance, 'redirect_to', 'intval' );	

		return $instance;
	}

	public function renderOutput( $instance ) {

		$hidden = $instance['hidden']  && ( $instance['hidden'] !== 'visible');

		$form = $this->getForm();
		$form->index = $this->number;

		$output = $form->renderParts( $this->action, $instance );
		
		if (! $hidden ){
			$output['intro'] = $instance['description'];	
		}		
		
		$output['beginPolicySubmit'] = '<div class="form-policy-submit-wrapper">';
		$output['endPolicySubmit'] = '</div>';

		return $output;
	}

	/**
	 * @inheritdoc
	 */
	public function widget( $args, $instance ) {

		$hidden = $instance['hidden']  && ( $instance['hidden'] !== 'visible');

		echo $args['before_widget']; ?>
		<header class="widget-header">
			<h2 class="title"><?php echo apply_filters( 'widget_title', $instance['title'] ); ?></h2>
			<?php if ($hidden) : ?>
			<p class="description"><?php echo $instance['description'] ?></p>
			<?php endif; ?>
		</header>
		<?php

		echo '<div class="svbk-form-container" id="' . $this->id_base . '-container-' . $this->number . '" >';

		if ( $hidden ) {
			echo '<a class="button svbk-show-content svbk-' . $instance['hidden'] . '-open" href="#' . $this->id_base . '-container-' . $this->number . '" >' . urldecode( $instance['submit_button_label'] ) . '</a>';
			echo '<div class="svbk-' . $instance['hidden'] . '-container">';
			echo '	<a class="button svbk-hide-content svbk-' . $instance['hidden'] . '-close" href="#' . $this->id_base . '-container-' . $this->number . '" ><span>' . __( 'Close', 'svbk-widgets' ) . '</span></a>';
			echo '	<div class="svbk-form-content svbk-' . $instance['hidden'] . '-content">';
		}

		if ( $instance['form_title'] ) {
			echo '		<h3 class="form-title">' . $instance['form_title'] . '</h3>';
		}

		echo Helpers\Form\Renderer::mergeParts( $this->renderOutput( $instance ), $this->renderOrder );

		if ( $hidden ) {
			echo '	</div>';
			echo '</div>';
		}

		echo $args['after_widget'];
	}

}
