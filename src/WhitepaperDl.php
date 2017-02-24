<?php 

namespace Svbk\WP\Themes\UfficioBrevetti\Widgets;


/**
 * Adds Foo_Widget widget.
 */
class WhitepaperDl extends \Svbk\WP\Widgets\Base {

    public $id_base = 'svbk_whitepaper_dl';

    public static $mc_apikey = '';
    public static $mc_endpoint = '';    
    public static $mc_list_id = '';
    
    public static $sg_key = '';
    public static $sg_template_id = '';

    public static $knownErrors; 

    const API_VERSION = '3.0';


    public static function hooks(){
        add_action( 'admin_post_nopriv_sendguide', array(static::class, 'sendDownload') );
        add_action( 'admin_post_sendguide', array(static::class, 'sendDownload') );
    }

    public static function knownErrors(){
        
        if(empty(self::$knownErrors)){
            self::$knownErrors = array(
            md5('You’ve already sent this email to the subscriber.') => __('You have already received this whitepaper, check your inbox, use another email or request it via email.', 'svbk-widgets'),
            md5('The subscriber has already been triggered for this email.') => __('Your content is beeing delivered, please wait', 'svbk-widgets')
            );
        }
        
        return self::$knownErrors;
        
    }

    public static function translateError($error){
        
        $errors = self::knownErrors();

        $error_hash = md5($error);
        
        if(array_key_exists($error_hash, $errors)){
            return $errors[$error_hash];
        } else {
            return $error;
        }
        
    }

    public static function requestMC($url, $request, &$errors){
        
        $request['headers']['Authorization'] = 'Basic ' . base64_encode('apikey:' . self::$mc_apikey );
        
        if(isset($request['body']) && is_array($request['body'])){
            $request['headers']['Content-Type'] = 'application/json';
            $request['body'] = json_encode($request['body']);
        }
        
        $response = wp_remote_request( $url, $request ); // insert user
        
        if(is_wp_error($response)){
            $errors[] = __('Sorry a temporary system error is preventig us to send the email to you, please try again later or send an email to us','svbk-widgets');
            return false;
        }
        
        $body = json_decode( wp_remote_retrieve_body($response), true );
        
        if( 200 !== wp_remote_retrieve_response_code($response)) {
            
            if(isset($body['detail'])){
                $errors[] = $body['detail'];
            }
            return false;
        }
        
        return $body;
    }
    
    public static function validateMCApi($url){
        return self::isMCApi(base64_decode($url));
    }
    
    public static function isMCApi($url){
        if(preg_match('@^https://\w+\.api\.mailchimp\.com/@i', $url)){
            return $url;
        } else {
            return false;
        }
    }
    
    public static function sendDownload(){

        $errors = array();

        $input = filter_input_array(INPUT_POST, array(
            'accept_policy'=> FILTER_VALIDATE_BOOLEAN,
            'dl_m'=>FILTER_VALIDATE_EMAIL,
            'atid'=>array( 
                'filter' => FILTER_CALLBACK , 
                'options'=> array(__CLASS__, 'validateMCApi'),
                )
            )
        );

        if(!self::$mc_endpoint || !self::$mc_apikey || !$input['atid']){
            $errors[] = __('System error, please send an email to us', 'svbk-widgets');
        }
        
        // if( check_ajax_referer( 'whitepaper_dl', false, false ) ){
        //     $errors[] = __('Session expired, please refresh your page', 'svbk-widgets');
        // } 

        if(!$input['dl_m']){
            $errors['dl_m'] = __('Invalid email address', 'svbk-widgets');
        }
        
        if(!$input['accept_policy']){
            $errors['accept_policy'] = __('You must accept policy to receive the content', 'svbk-widgets');
        }            

        if( empty($errors) ){
            
            //prepare endpoint    
            $mc_members_ep = sprintf( untrailingslashit(self::$mc_endpoint).'/%s/lists/%s/members/', self::API_VERSION, self::$mc_list_id);
    
            //request if the user is already present in list
            $c_resp = self::requestMC( $mc_members_ep . md5($input['dl_m']), array( 'method' => 'GET'), $errors);
            
            if( $c_resp === false ) { // user not yet subscribed
                    
                $ins_resp = self::requestMC( $mc_members_ep, array( 
                        'method' => 'POST',
                        'body'=>array(
                            'email_address' => $input['dl_m'],
                            'status' => 'subscribed',
                            'ip_signup' => $_SERVER['REMOTE_ADDR'],
                            'language' => substr(get_locale(), 0, 2),
                            ),
                        ),
                        $errors
                    ); // insert user
                
            } elseif ( isset($c_resp['status']) && ( $c_resp['status'] === 'unsubscribed' ) ) { // user unsubscribed, exclude 'cleaned' (bounced) emails
                
                $sub_resp = self::requestMC( $mc_members_ep . md5($input['dl_m']), array( 
                        'method' => 'PATCH',
                        'body'=>array(
                            'status' => 'subscribed',
                            ),
                        ),
                        $errors
                    ); // re-subscribe user      
            } 
            
    
            $trigger_resp = self::requestMC( $input['atid'], array( 
                'method' => 'POST',
                'body'=>array( 'email_address'=>$input['dl_m'] ),
                ) 
                , $errors
            ); // trigger automation             
        
        }
        
        header('Content-Type: application/json');
        
        if(!empty($errors)){
            
            $errors = array_map(array(__CLASS__, 'translateError'), $errors);
            
            echo json_encode(array('status'=>'error', 'errors'=>$errors));
            return false;
        }
        
        echo json_encode( array('status'=>'success', 'message'=>__('Request confirmed, the requested content will get to your inbox in the next minutes.', 'svbk-widgets')) );
    }

    protected function title(){
        return __( 'Whitepapers Download', 'svbk-widgets' );
    }
    
    protected function args(){
        return array( 'description' => __( 'Subscribe & download whitepaper widget', 'svbk-widgets' ) );
    }
    
	public function widget( $args, $instance ) {
	    
	    //wp_enqueue_script('mailchimp-forms', '//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js', array(), null, true);
	    
		echo $args['before_widget'];
		
		?>
		<header class="widget-header">
		    <h3 class="title"><?php echo apply_filters( 'widget_title', $instance['title'] ); ?></h3>
			<p class="description"><?php echo $instance['description'] ?></p>
		</header>
        <form class="whitepaper-dl" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" id="wdl-<?php echo esc_attr($args['widget_id']) ?>" method="POST">
            <div class="dl_m-group field-group">
                <label for="dl-m-<?php echo esc_attr($args['widget_id']) ?>"><?php _e('Email Address', 'svbk-widgets'); ?></label>
                <input type="text" name="dl_m" id="dl-m-<?php echo esc_attr($args['widget_id']) ?>" />
                <span class="field-errors"></span>
            </div>            
            <div class="policy-agreement accept_policy-group field-group">
                <input type="checkbox" id="accept-policy-<?php echo esc_attr($args['widget_id']) ?>" value="1" name="accept_policy">
                <label for="accept-policy-<?php echo esc_attr($args['widget_id']) ?>">
                    <?php 
                        printf(
                            __('I declare I have read and accept the %s notification and I consent to process my personal data.', 'svbk-widgets'), 
                            shortcode_exists('privacy-link') ? 
                                do_shortcode('[privacy-link]privacy policy[/privacy-link]') : sprintf( __('<a href="%s" target="_blank">privacy policy</a>', 'svbk-widgets'), $instance['privacy_link']) 
                        ); 
                    ?>
                </label>
                <span class="field-errors"></span>
            </div>
            <input type="hidden" name="action" value="sendguide" >
            <input type="hidden" name="atid" value="<?php echo esc_attr(base64_encode($instance['automation_trigger'])); ?>" >
            <?php  wp_nonce_field( 'whitepaper_dl' ); ?>
            <button type="submit" name="subscribe" class="button"><?php _e('Download', 'svbk-widgets');  ?></button>
            <ul class="messages"></ul>
        </form>
        
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
		
            $this->textField('title', $this->fieldValue( $instance, 'title', __( 'Widget title', 'svbk-widgets' ) ), __( 'Title', 'svbk-widgets').':' );
            $this->textAreaField('description', $this->fieldValue( $instance, 'description', __( 'Description text', 'svbk-widgets' ) ), __( 'Description', 'svbk-widgets').':' );
            $this->textField('automation_trigger', self::isMCApi($this->fieldValue( $instance, 'automation_trigger', '' )), __( 'Automation Trigger URL', 'svbk-widgets').':' );
            
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
        $instance['description'] = $this->sanitizeField($new_instance, 'description');
        $instance['automation_trigger'] = $this->sanitizeField($new_instance, 'automation_trigger');

        return $instance;
	}

} 