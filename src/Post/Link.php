<?php 

namespace Svbk\WP\Widgets\Post;

class Link extends Single {

    public $id_base = 'svbk_post_link';

    protected function title(){
        return __( 'Post Link', 'svbk-widgets' );
    }
    
    protected function args(){
        return array(  'description' => __( '', 'svbk-widgets' ), );
    }
    
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
	    
	       parent::form( $instance );
	    
	       $this->textField('subtitle', $this->fieldValue( $instance, 'subtitle', __( 'New subtitle', 'svbk-widgets' ) ), __( 'Subtitle:', 'svbk-widgets') );
	       $this->textAreaField('description', $this->fieldValue( $instance, 'description', __( 'New description', 'svbk-widgets' ) ), __( 'Description:', 'svbk-widgets') );
	       $this->textField('button_label', $this->fieldValue( $instance, 'button_label', __( 'Click here', 'svbk-widgets' ) ), __( 'Button Label:', 'svbk-widgets') );
	    
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
	    
        $instance = parent::update( $new_instance, $old_instance );

        $instance['subtitle'] = $this->sanitizeField($new_instance, 'subtitle');
        $instance['description'] = $this->sanitizeField($new_instance, 'description', 'wp_kses_post');
        $instance['button_label'] = $this->sanitizeField($new_instance, 'button_label');
        
        return $instance;	    
	    
	}    
	
	public function widget( $args, $instance ) {

		echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		} ?>
		
		<?php if ( ! empty( $instance['subtitle'] ) ) : ?>
		<div class="subtitle"><?php echo $instance[ 'subtitle' ]; ?></div>
		<?php endif; ?>
        
        <?php if ( ! empty( $instance['description'] ) ) : ?>
        <div class="widget-description"><?php echo $instance[ 'description' ] ?></div>
        <?php endif; ?>
        
        <a class="button" href="<?php echo esc_attr( get_permalink( $instance['post_id'] ) ); ?>"><?php echo $instance[ 'button_label' ] ?></a>
        
        <?php 
		echo $args['after_widget'];
	}	

} // class Foo_Widget
