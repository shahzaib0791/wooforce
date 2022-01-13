<?php
/**
 * @package WooForce
 */
/**
 * Plugin Name: WooForce
 * Plugin URI: http://www.example.com
 * Description: A WooCommerce plugin to connect and sync data between WooCommerce and SalesForce.
 * Version: 1.0.0
 * Author: Salik Jamal
 * Author URI: http://www.example.com
 * License: GPLv3 or later
 * Text Domain: wooforce
 */

defined('ABSPATH') || exit;

// check if woocommerce is installed and activated, if not - then do not proceed with this plugin
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

// constants
define('WOOFORCE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BASE_URL', get_option('wf_salesforce_instance_url'));

// require the main file for the plugin logics
require_once WOOFORCE_PLUGIN_PATH . 'inc/class-wooforce.php';

// activation
register_activation_hook(__FILE__, array('WooForce', 'activate'));

// deactivation
register_deactivation_hook(__FILE__, array('WooForce', 'deactivate'));

// function my_action_wpcf7_before_send_mail($contact_form) { 
//     $user_id = wp_insert_user([
//         'user_login' => 'test',
//         'user_nicename' => 'test',
//         'user_email' => 'test@gmail.com',
//         'user_pass' => wp_hash_password('Alphapromed@123'),
//         'display_name' => 'test',
//         'first_name' => 'test',
//         'last_name' => 'test',
//         'role' => 'healthcare_customer'
//     ]);
//     echo $user_id;
 
// }
         
// // add the action 
// add_action('wpcf7_before_send_mail', 'my_action_wpcf7_before_send_mail', 10, 1); 


