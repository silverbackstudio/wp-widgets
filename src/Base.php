<?php 

namespace Svbk\WP\Widgets;

load_textdomain( 'svbk-widgets', dirname(__DIR__).'/languages/svbk-shortcakes' . '-' . get_locale() . '.mo'   ); 

/**
 * Adds Foo_Widget widget.
 */
abstract class Base extends \WP_Widget {

    public $widget_id = 'svbk_widget_base';

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			$this->widget_id, // Base ID
			$this->title(), // Name
			$this->args()
		);
	}

    protected function title(){
        return __( 'Silverback Base Title', 'svbk-widgets' );
    }
    
    protected function args(){
        return array(  'description' => __( 'Insert Description', 'svbk-widgets' ), );
    }


    protected function translateField($name, $value){
        
        return apply_filters( 'widget_translate_field', $value, $name, $this->id);
        
    }

    protected function textField($name, $value, $title, $attr=array()){ ?>
        <p>
            <label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $title ?></label> 
            <input id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" <?php $this->printAttrs($attr); ?>/>
        </p>
    <?php 
    }

    protected function textAreaField($name, $value, $title, $attr=array()){ ?>
        <p>
            <label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $title; ?></label> 
            <textarea id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>" <?php $this->printAttrs($attr, array('rows'=>5)); ?>><?php echo esc_attr($value); ?></textarea>
        </p>
    <?php 
    }
    
    protected function checkBoxField($name, $value, $title){ ?>
        <p>
           <input id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>" type="checkbox" value="1" <?php echo $value?'checked="checked"':''; ?> />
           <label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $title; ?></label>                     
        </p>
    <?php
    }
    
    protected function selectField($name, $value, $title, $options, $attr=array()){ ?>
        <p>
           <label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $title; ?></label> 
           <select id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>" <?php $this->printAttrs($attr); ?> >
               <?php foreach($options as $opt_value=>$opt_label): ?>
                   <option value="<?php echo esc_attr($opt_value); ?>" <?php echo ($value == $opt_value)?'selected="selected"':''; ?>><?php echo esc_html($opt_label);  ?></option>
               <?php endforeach; ?>
           </select>
        </p>
    <?php
    }    
    
    protected function postSelect($name, $value, $title){ ?>
        <p>
           <input id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" <?php $this->printAttrs($attr); ?>/>
           <label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $title; ?></label>                     
        </p>
    <?php
    }  
    
    protected function pageSelect($name, $value, $title, $args=array()){ ?>
        <p>
           <label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $title; ?></label>                     
           <?php 
           
           $args = wp_parse_args($args, array(
                'name' => $this->get_field_name( $name ),
                'id'=> $this->get_field_id( $name ),
                'selected' => $value,
                'class'=>'widefat'
           ) );
           
           wp_dropdown_pages( $args ); 
           
           ?>
        </p>
    <?php
    }      

    protected function printAttrs($attr, $defaults=array()){

        $attr = array_merge(array_merge(array('class'=>'widefat'), $defaults), $attr);

        $pairs = array();

        foreach ($attr as $name => $value) {
            $pairs[] = sprintf('%s="%s"', $name, esc_attr($value));
        }

        echo join(' ', $pairs);
    }

    protected function fieldValue($instance, $name, $default=''){
        if ( isset( $instance[ $name ] ) ) {
                 return $instance[ $name ];
        }
        else {
                return $default; 
        }
    }

    protected function sanitizeField($instance, $name, $sanitize_function='sanitize_text_field', $default=""){
        
        if( empty( $instance[$name])){
            return $default;
        }                
        
        $value  = is_callable($sanitize_function)?call_user_func($sanitize_function, $instance[$name] ):$value;

        return apply_filters('widget_sanitize_field', $value, $name, $this->id);
    }

} // class Foo_Widget
