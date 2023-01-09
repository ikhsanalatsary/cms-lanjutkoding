<?php
/*
Plugin Name: Import Markdown
Description: Generates posts based on the imported markdown files.
Version: 1.09
Author: DAEXT
Author URI: https://daext.com
License: GPLv2 or later
*/

//Prevent direct access to this file
if ( ! defined('WPINC')) {
    die();
}

//Save the PHP version in a format that allows a numeric comparison
if ( ! defined('DAIMMA_PHP_VERSION')) {
    $version = explode('.', PHP_VERSION);
    define('DAIMMA_PHP_VERSION', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

//Class shared across public and admin
require_once(plugin_dir_path(__FILE__) . 'shared/class-daimma-shared.php');

//Admin
if (is_admin() && ( ! defined('DOING_AJAX') || ! DOING_AJAX)) {

    //instances of the markdown parsers are created and saved as global variables
    require_once('globalize_md_parsers.php');

    //Admin
    require_once(plugin_dir_path(__FILE__) . 'admin/class-daimma-admin.php');
    add_action('plugins_loaded', array('Daimma_Admin', 'get_instance'));

    //Activate
    register_activation_hook(__FILE__, array(Daimma_Admin::get_instance(), 'ac_activate'));

}

//Customize the action links in the "Plugins" menu
function daimma_customize_action_links( $actions ) {
	$actions[] = '<a href="https://daext.com/ultimate-markdown/">' . esc_html__('Buy the Pro Version', 'import-markdown') . '</a>';
	return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'daimma_customize_action_links' );