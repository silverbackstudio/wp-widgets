<?php 

namespace Svbk\WP\Widgets;

use Svbk\WP\Helpers;

class WhitepaperDl extends Base {

    public $id_base = 'svbk_whitepaper_dl';

    public $mc_apikey = '';
    public $mc_list_id = '';
    
    public $md_apikey = '';
    public $md_template = '';

    public $action = 'sendwhitepaper'; 
    public $formClass = '\Svbk\WP\Helpers\Form\Download';    
    
    public $renderOrder = array(
    	'formBegin',
    	'title',
    	'input',
    	'beginPolicySubmit',
        'policy',
        'submitButton',
        'messages',
        'endPolicySubmit',
        'formEnd'
    ); 
    
    
    public function hooks(){
        
        parent::hooks();
        
        add_action( "admin_post_nopriv_{$this->action}", array($this, 'processSubmission') );
        add_action( "admin_post_{$this->action}", array($this, 'processSubmission') );
    }

    protected function title(){
        return __( 'Whitepapers Download', 'svbk-widgets' );
    }
    
    protected function args(){
        return array( 'description' => __( 'Subscribe & download whitepaper widget', 'svbk-widgets' ) );
    }    
    
    protected function getForm($setSendParams=false){
        
        $formClass = $this->formClass;
        
        $form = new $formClass;
        
        $form->field_prefix = 'form_'.$this->id_base;
        $form->action = $this->action;
        
        if($setSendParams) {
            $form->mc_apikey = $this->mc_apikey;
            $form->mc_list_id = $this->mc_list_id;
            $form->md_apikey = $this->md_apikey;
            
            $form->templateName = $this->md_template;        
        }
        
        return $form;
    }
    
    public function processSubmission(){
        
        $form = $this->getForm(true);
        
        $form->processSubmission();
        
        $errors = $form->getErrors();
        
        header('Content-Type: application/json');

        echo $this->jsonResponse($errors, $form);
    }
    
    public function jsonResponse($errors, $form) {
        
        if(!empty($errors)){
            
            return json_encode( array(
                'prefix' => $this->id_base,
                'status' => 'error', 
                'errors' => $errors
                )
            );
            
            return false;
        }
        
        return json_encode( 
            array(
                'prefix' => $this->id_base,
                'status'=>'success', 
                'message'=> $this->confirmMessage()
            ) 
        );        
        
    }	
    
    public function confirmMessage(){
        return $this->confirmMessage ?: __('Thanks for your request, the file you requested will be sent to your inbox.', 'svbk-shortcakes');
    }      

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
            $this->textField('title', $this->fieldValue( $instance, 'title', __( 'Widget title', 'svbk-widgets' ) ), __( 'Title', 'svbk-widgets').':' );
            $this->textAreaField('description', $this->fieldValue( $instance, 'description', __( 'Description text', 'svbk-widgets' ) ), __( 'Description', 'svbk-widgets').':' );
            $this->textField('form_title', $this->fieldValue( $instance, 'form_title' ), __( 'Form Title', 'svbk-widgets').':' );
            $this->fileField('file', $this->fieldValue( $instance, 'file' ), __( 'Downoload File ID', 'svbk-widgets').':' );
            $this->textField('submit_button_label', $this->fieldValue( $instance, 'submit_button_label' ), __( 'Submit button', 'svbk-widgets').':' );
            $this->textField('privacy_link', $this->fieldValue( $instance, 'privacy_link' ), __( 'Privacy Link', 'svbk-widgets').':' );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
	    
        $instance = array();
        
        $instance['title'] = $this->sanitizeField($new_instance, 'title');
        $instance['privacy_link'] = $this->sanitizeField($new_instance, 'privacy_link');
        $instance['description'] = $this->sanitizeField($new_instance, 'description', 'wp_kses_post');
        $instance['form_title'] = $this->sanitizeField($new_instance, 'form_title');
        $instance['submit_button_label'] = $this->sanitizeField($new_instance, 'submit_button_label');
        $instance['file'] = $this->sanitizeField($new_instance, 'file', 'intval');

        return $instance;
	}
	
	public function renderOutput($instance){
	    
	    $form = $this->getForm();
        $form->index = $this->number;

        $output = $form->renderParts( $this->action, $instance );  
            
        $output['beginPolicySubmit'] = '<div class="form-policy-submit-wrapper">';
        $output['endPolicySubmit'] = '</div>';   
	    
	    return $output;
	    
	}
	
	public function widget( $args, $instance ) {
	    
		echo $args['before_widget'];
		
		?>
		<header class="widget-header">
		    <h2 class="title"><?php echo apply_filters( 'widget_title', $instance['title'] ); ?></h2>
			<p class="description"><?php echo $instance['description'] ?></p>
		</header>
        <?php
        
        echo '<div class="whitepaper-dl svbk-form-container" id="' . $this->id_base . '-container-' . $this->number .'" >';
        
        echo '<a class="button svbk-show-content" href="#' . $this->id_base . '-container-' . $this->number .'" >' . urldecode( $instance['submit_button_label'] ) . '</a>';
        echo '<div class="svbk-form-content">';
        echo '<a class="button svbk-hide-content" href="#' . $this->id_base . '-container-' . $this->number .'" ><span>' . __('Close', 'svbk-shortcakes') . '</span></a>';
        
        if($instance['form_title']){
            echo '<h3 class="form-title">'.$instance['form_title'].'</h3>';
        }

        echo Helpers\Renderer::mergeParts($this->renderOutput($instance), $this->renderOrder);
        
        echo '</div>';
        echo '</div>';
        
		echo $args['after_widget'];
	}	

} 