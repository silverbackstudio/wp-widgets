<?php 

namespace Svbk\WP\Widgets;


/**
 * Adds Foo_Widget widget.
 */
class FeaturedPost extends Base {

    public $widget_id = 'svbk_feaured_post';


    protected function title(){
        return __( 'Featured Post', 'svbk-widgets' );
    }
    
    protected function args(){
        return array(  'description' => __( '', 'svbk-widgets' ), );
    }
    
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

        $the_query = new \WP_Query( array('p' => $instance['featured_post'] ) );
        
        // Il Loop
        while ( $the_query->have_posts() ) : $the_query->the_post();
        	get_template_part( apply_filters('svbk_widget_featured_post_template', 'template-parts/thumb' ), get_post_type() );
        endwhile;
        
        // Ripristina Query & Post Data originali
        wp_reset_query();
        wp_reset_postdata();        

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
            $this->textField('featured_post', $this->fieldValue( $instance, 'featured_post'), __( 'Featured Post:', 'svbk-widgets') );
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
        
        $instance['featured_post'] = $this->sanitizeField($new_instance, 'featured_post', 'intval');
        $instance['title'] = $this->sanitizeField($new_instance, 'title');
        
        return $instance;	    
	    
	}

} // class Foo_Widget
