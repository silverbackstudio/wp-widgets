<?php 

namespace Svbk\WP\Widgets\Ads;

use Svbk\WP\Widgets\Base;
use Svbk\WP\Helpers;

class AdSense extends Base {

    public $id_base = 'svbk_adsense_adunit';
    public $google_ad_client = '';

    protected function title(){
        return __( 'AdSense AdUnit', 'svbk-widgets' );
    }
    
    protected function args(){
        return array(  'description' => __( 'Shows an AdSense banner', 'svbk-widgets' ), );
    }

	public function widget( $args, $instance ) {

        echo $args['before_widget'];

        $adsense = new Helpers\Ads\AdSense( $this->google_ad_client );
        echo $adsense->adunit_code( $instance['ad_slot'], $instance['ad_size'] );        
        
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
            $this->textField('ad_slot', $this->fieldValue( $instance, 'ad_slot' ), __( 'Ad Unit ID', 'svbk-widgets') . ':'  );
            $this->selectField('ad_size', $this->fieldValue( $instance, 'ad_size', 'auto' ), __( 'Ad Size', 'svbk-widgets') . ':', Helpers\Ads\AdSense::adunit_sizes() );
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
        
        $instance['ad_slot'] = $this->sanitizeField($new_instance, 'ad_slot');
        $instance['ad_size'] = $this->sanitizeField($new_instance, 'ad_size');
        
        return $instance;	    
	    
	}

} // class Foo_Widget
