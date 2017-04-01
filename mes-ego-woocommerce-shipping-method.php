<?php
/**
* Plugin Name: E-Go Courier Shipping For WooCommerce
* Description: Integrates with E-Go Courier  allowing you to provide accurate shipping quotes for your customers.
* Version: 1.0.0
* Author: mis1
* Author URI: https://github.com/mis1
*/

/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) { 
    exit; 
}

/**
 * Check if WooCommerce is active 
 */
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	exit;
}

function mes_ego_register_activation_hook(){
	global $wp_version;

	$wp_ver = "4.0";
	$wc_ver = "2.6";
    if(version_compare( $wp_version, $wp_ver, '<' ) || version_compare( WC()->version, $wc_ver, '<' ) ){
        $msg = "Wordpress version $wp_ver+ and WooCommerce version $wc_ver+.";
        deactivate_plugins(basename(__FILE__));
        wp_die('<p>The <strong>E-Go Courier Shipping For WooCommerce</strong> plugin requires ' . $msg . '</p>', 'Plugin Activation Error', array('response' => 200, 'back_link' => TRUE));
    }

}

function mes_ego_calculate_shipping($methods) {
	$methods['mes-ego-shipping-method'] = 'EGo_Shipping_Method';
	return $methods;
}

function mes_ego_shipping_method_init() {
	include_once 'includes/ego-shipping-method.php';
}

function mes_ego_add_admin_options($links) {
	$custom_links = [
		'<a href="admin.php?page=wc-settings&tab=shipping&section=">Settings</a>'
	];
	return array_merge($custom_links, $links);
}

add_filter('woocommerce_shipping_methods', 'mes_ego_calculate_shipping');
add_action('woocommerce_shipping_init', 'mes_ego_shipping_method_init');
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'mes_ego_add_admin_options');
register_activation_hook(__FILE__, 'mes_ego_register_activation_hook' );
