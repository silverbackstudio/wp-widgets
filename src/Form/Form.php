<?php

namespace Svbk\WP\Widgets\Form;

use Svbk\WP\Helpers;
use Svbk\WP\Forms;
use Svbk\WP\Widgets\Base;
use Exception;

class Form extends Base {

	public $id_base = 'svbk_form_base';

	public $action = 'svbk_form_base';
	public $formClass = '\Svbk\WP\Forms\Submission';
	public $form;	
	
	public $formParams = array();

	public $classes = array('svbk-form-container');

	public $redirectData = array();	
	
	public static $form_errors = array();

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

	static function register( $properties = array(), $form_properties = array() ) {

		$instance = parent::register( $properties );

		//Retrocompatibility with 4.1
		if ( ! empty( $form_properties ) ) {
		    _deprecated_argument( __FUNCTION__, '4.2.0', 'Please use the setForm method' );
    		$instance->setForm( $form_properties );
		}

		return $instance;
	}

	public function setForm( $form = array() ){
		
		if( is_array( $form ) ) {
			$formClass = $this->formClass;
			$this->form = new $formClass( $form );
		} elseif ( is_string( $form ) && Forms\Manager::has( $form ) ) {
			$this->form = Forms\Manager::get( $form );
		} elseif ( is_a( $form,  Form::class ) ) {
			$this->form = $form;
		} else {
			throw new Exception( 'Unable to setup form' );
		}
		return $this->form;
	}

	protected function title() {
		return __( 'Silverback Form', 'svbk-widgets' );
	}

	protected function args() {
		return array(
			'description' => __( 'Silverback Base Form', 'svbk-widgets' ),
		);
	}

	protected function getForm() {

		if( !empty( self::$form_errors  ) ) {
			$form->errors = self::$form_errors;
		}

		if( empty( $this->form ) ) {
			$this->setForm();
		}

		return $this->form;
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

		$output = $form->renderParts( $instance );
		
		if (! $hidden ){
			$output['intro'] = $instance['description'];	
		}		
		
		if( $instance['redirect_to'] ) {
			$output['input']['redirect_to'] =  $form->renderField( 'redirect_to', 
				array(
					'label' => __( 'Redirect To', 'svbk-helpers' ),
					'type' => 'hidden',
					'default' => $instance['redirect_to'],
					'filter' => FILTER_VALIDATE_INT,
				) 
			);
		}
		
		$output['beginPolicySubmit'] = '<div class="form-policy-submit-wrapper">';
		$output['endPolicySubmit'] = '</div>';

		return $output;
	}

	protected function getClasses( $instance ) {

		$instance_classes = array();

		if ( ! empty( $instance['class'] ) ) {
			$instance_classes = array_merge( $instance_classes, preg_split( '/[\s,]+/', $instance['class'], -1, PREG_SPLIT_NO_EMPTY ) );
		}

		if ( ! empty( $instance['classes'] ) ) {
			$instance_classes = array_merge( preg_split( '/[\s,]+/', $instance['classes'], -1, PREG_SPLIT_NO_EMPTY ) );
		}

		$classes = array_merge( (array) $this->classes, $instance_classes );

		if ( ! empty( $classes ) ) {
			return array_map( 'trim', $classes );
		}

	}

	protected static function renderClasses( $classes ) {

		if ( empty( $classes ) ) {
			return '';
		}

		return  'class="' . esc_attr( join( ' ', $classes ) ) . '"';
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

		echo '<div ' . $this->renderClasses( $this->getClasses( $instance ) ) . ' id="' . $this->id_base . '-container-' . $this->number . '" >';

		if ( $hidden ) {
			echo '<a class="button svbk-show-content svbk-' . $instance['hidden'] . '-open" href="#' . $this->id_base . '-container-' . $this->number . '" >' . urldecode( $instance['submit_button_label'] ) . '</a>';
			echo '<div class="svbk-' . $instance['hidden'] . '-container">';
			echo '	<a class="button svbk-hide-content svbk-' . $instance['hidden'] . '-close" href="#' . $this->id_base . '-container-' . $this->number . '" ><span>' . __( 'Close', 'svbk-widgets' ) . '</span></a>';
			echo '	<div class="svbk-form-content svbk-' . $instance['hidden'] . '-content">';
		}

		if ( $instance['form_title'] ) {
			echo '		<h3 class="form-title">' . $instance['form_title'] . '</h3>';
		}

		echo Forms\Renderer::mergeParts( $this->renderOutput( $instance ), $this->renderOrder );

		if ( $hidden ) {
			echo '	</div>';
			echo '</div>';
		}

		echo $args['after_widget'];
	}

}
