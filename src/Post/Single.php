<?php 

namespace Svbk\WP\Widgets\Post;

class Single extends Latest {

    public $id_base = 'svbk_post_single';

    protected function title(){
        return __( 'Single Post', 'svbk-widgets' );
    }
    
    protected function args(){
        return array(  'description' => __( '', 'svbk-widgets' ), );
    }
    
    protected function queryArgs( $instance ){
        
        $query_args = parent::queryArgs( $instance );
        
        $query_args[ 'ignore_sticky_posts' ] = 1;
        $query_args[ 'post__in' ] = array( $instance['post_id'] );
        
        return $query_args;
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
	    
	        $query_args = $this->query_args;
	        $query_args['posts_per_page'] = 100;

	        $post_type = get_post_type_object( $query_args['post_type'] );

	        if( ! $post_type || ( $post_type->name === 'page' ) ) {
	            $this->pageSelect('post_id', $this->fieldValue( $instance, 'post_id' ), __( 'Page') . ':', $query_args);
	        } else {
                $this->postSelect('post_id', $this->fieldValue( $instance, 'post_id' ), ($post_type->labels->singular_name ?: $post_type->label) . ':', $query_args );
	        }
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

        $instance['post_id'] = $this->sanitizeField($new_instance, 'post_id', 'intval');
        
        return $instance;	    
	    
	}

} // class Foo_Widget
