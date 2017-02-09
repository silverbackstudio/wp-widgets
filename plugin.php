<?php 

/**
 * @package Silverback Widgets
 * @version 1.1
 */
/*
Plugin Name: Silverback widgets
Plugin URI: https://gitlab.com/silverbackstudio/wp-widgets
Description: Silverback's Widget Package
Author: Silverback Studio
Version: 1.1
Author URI: http://www.silverbackstudio.it/
Text Domain: svbk-widgets
*/

function svbk_widgets_init() {
  load_plugin_textdomain( 'svbk-widgets', false, dirname( plugin_basename( __FILE__ ) ). '/languages' ); 
}

add_action('plugins_loaded', 'svbk_widgets_init'); 