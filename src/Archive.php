<?php
namespace Svbk\WP\Widgets;

/**
 * Adds Foo_Widget widget.
 */
class Archive extends Base { 

    public $id_base = 'svbk_archive';
    public $post_type = 'post';
    public $limit = 100;
    public $order = 'ASC';
    
    protected function title(){
        return __( 'Posts Archive', 'svbk-widgets' );
    }
    
    protected function args(){
        return array(  'description' => __( 'Shows archive in a collapse', 'svbk-widgets' ), );
    }

	public function widget( $args, $instance ) {
	    global $wpdb, $wp_locale;
	    
		echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

        // this is what will separate dates on weekly archive links
        $archive_week_separator = '&#8211;';
     
        $sql_where = $wpdb->prepare( "WHERE post_type = %s AND post_status = 'publish'", $this->post_type );
     
        /**
         * Filters the SQL WHERE clause for retrieving archives.
         *
         * @since 2.2.0
         *
         * @param string $sql_where Portion of SQL query containing the WHERE clause.
         * @param array  $r         An array of default arguments.
         */
        $where = apply_filters( 'getarchives_where', $sql_where, $r );
     
        /**
         * Filters the SQL JOIN clause for retrieving archives.
         *
         * @since 2.2.0
         *
         * @param string $sql_join Portion of SQL query containing JOIN clause.
         * @param array  $r        An array of default arguments.
         */
        $join = apply_filters( 'getarchives_join', '', $r );
     
        $output = '';
     
        $last_changed = wp_cache_get_last_changed( 'posts' );
     
        $limit = absint( $this->limit );
        $limit = ' LIMIT ' . $limit;
     
        $query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $this->order $limit";
        $key = md5( $query );
        $key = "wp_get_archives:$key:$last_changed";
        
        if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
            $results = $wpdb->get_results( $query );
            wp_cache_set( $key, $results, 'posts' );
        }
        
        if ( $results ) {
            
            $archiveArray = array();

            foreach ( (array) $results as $result ) {
                $url = get_month_link( $result->year, $result->month );
                if ( 'post' !== $this->post_type ) {
                    $url = add_query_arg( 'post_type', $this->post_type, $url );
                }

                $archiveArray[$result->year][$result->month] = $url;
            }
        }
        
        echo '<ul class="archive-years">';
        
        foreach($archiveArray as $year => $monthArchive){
            
            echo '<li class="archive-year">';
            echo '<span class="month-name">' . $year . '</span>'; 
            echo '<ul class="archive-months">';
            
            foreach($monthArchive as $month => $url){
               echo '<li>';
               echo '<a href="' . esc_url( $url ) . '">' . $wp_locale->get_month( $month ) . '</a>';
               echo '</li>';
            }
            
            echo '</ul>';
            echo '</li>';
        }

        echo '</ul>';   

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

}