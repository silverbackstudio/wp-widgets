<?php 

namespace Svbk\WP\Widgets;


/**
 * Adds Foo_Widget widget.
 */
class FeaturedPost extends Base {

    public $id_base = 'svbk_latest_post';
    public $template = 'template-parts/thumb';
    public $excerpt_lenght = 15;

    protected function title(){
        return __( 'Latest Post', 'svbk-widgets' );
    }
    
    protected function args(){
        return array(  'description' => __( '', 'svbk-widgets' ), );
    }
    
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

        $query_args = array(
            'posts_per_page' => 1, 
            'orderby'=>'date'
        );
        
        if(is_single()){
            $query_args['post__not_in'] = array( get_the_ID() );
        }

        $the_query = new \WP_Query( $query_args );
        
        add_filter( 'excerpt_length', array($this, 'excerpt_length'), 99 );
        // Il Loop
        while ( $the_query->have_posts() ) : $the_query->the_post();
        	get_template_part( $this->template, get_post_type() );
        endwhile;
        remove_filter( 'excerpt_length', array($this, 'excerpt_length'), 99 );
        
        
        // Ripristina Query & Post Data originali
        wp_reset_query();
        wp_reset_postdata();        

		echo $args['after_widget'];
	}
	
    public function excerpt_length( $length ) {
	    return $this->excerpt_lenght;
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