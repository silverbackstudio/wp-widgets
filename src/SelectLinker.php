<?php 

namespace Svbk\WP\Widgets;


/**
 * Adds Foo_Widget widget.
 */
class SelectLinker extends Base {

    public $widget_id = 'svbk_select_linker';


    protected function title(){
        return __( 'Select Linker', 'svbk-widgets' );
    }
    
    protected function args(){
        return array(  'description' => __( 'Displays a menu in a select', 'svbk-widgets' ), );
    }
    
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		echo '<header class="widget-header">';
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		} 
		if ( ! empty( $instance['subtitle'] ) ) {
			echo '<p class="subtitle">' . $instance['subtitle'] . '</p>';
		} 
		echo '</header>';
		?>
		
        <?php wp_nav_menu( array( 'menu'=>$instance['select_menu'], '') ); ?>
        
        <?php
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
            $this->textField('title', $this->fieldValue( $instance, 'title', __( 'New title', 'svbk-widgets' ) ), __( 'Title:', 'svbk-widgets') );
            $this->textField('subtitle', $this->fieldValue( $instance, 'subtitle', __( 'Subtitle text', 'ufficiobrevetti' ) ), __( 'Subitle:', 'ufficiobrevetti') );
            $this->selectField('select_menu', $this->fieldValue( $instance, 'select_menu'), __( 'Select Menu:', 'svbk-widgets'), wp_list_pluck( wp_get_nav_menus(), 'name', 'term_id' ) );
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
        $instance['subtitle'] = $this->sanitizeField($new_instance, 'subtitle');
        $instance['select_menu'] = $this->sanitizeField($new_instance, 'select_menu', 'intval');
        
        return $instance;	    
	    
	}

} // class Foo_Widget
