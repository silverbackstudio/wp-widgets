<?php 

namespace Svbk\WP\Widgets;

use Svbk\WP\Helpers;

/**
 * Adds Foo_Widget widget.
 */
class Jetpack_RelatedPosts extends Base {

    public $id_base = 'svbk_jp_related_posts';

    protected function title(){
        return __( 'Jetpack Related Posts', 'svbk-widgets' );
    }
    
    protected function args(){
        return array(  'description' => __( '', 'svbk-widgets' ), );
    }
    
	public function widget( $args, $instance ) {
		
		if (is_single() && class_exists( 'Jetpack_RelatedPosts' ) && method_exists( 'Jetpack_RelatedPosts', 'init_raw' ) ) {
    		
    		echo $args['before_widget'];
    		
    		if ( ! empty( $instance['title'] ) ) {
    			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    		}
    
            $related = Helpers\Jetpack::relatedPosts();
    		
            if ( $related ) {
            	echo '<ul class="post-list">';
                foreach ( $related as $result ) {
                    echo '<li><a href="' . get_permalink( $result['id'] ) . '">' . get_the_title( $result['id'] ) . '</a></li>';
                }
                echo '</ul>';
            }
        
    		echo $args['after_widget'];
    		
		}
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
        
        return $instance;	    
	    
	}

} // class Foo_Widget
