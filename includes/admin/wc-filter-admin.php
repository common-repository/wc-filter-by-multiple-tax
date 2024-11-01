<?php
/**
 * WC Filter By Multiple Tax Admin
 *
 * @class    WC_Filter_Admin
 * @author   TVLA92
 * @category Admin
 * @package  WC Filter By Multiple Tax/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Filter_Admin Class
 */
class WC_Filter_Admin {

	public function __construct() {
		// Add menus
		add_action( 'admin_menu', array( $this, 'register_menu' ), 90 );
	}

	public function register_menu() {

	    add_menu_page(
	        __( 'WC Filter', 'wc-filter' ), // $page_title
	        __( 'WC Filter', 'wc-filter' ), // $menu_title
	        null, 							// $capability
	        'wc-filter-setting', 			// $menu_slug
	        null, 							// $function
	        'dashicons-list-view',			// $icon
	        30								// $position
	    );

		add_submenu_page(
			'wc-filter-setting',						// $parent_slug
			esc_html__( 'Settings', 'wc-filter' ),		// $page_title
			esc_html__( 'Settings', 'wc-filter' ),		// $menu_title
			'manage_options',							// $capability
			'wc-filter-config',							// $menu_slug
			array( $this, 'display_setting_dashboard' )	// $funcion
		);

	}

	public function display_setting_dashboard() {
		include 'wc-filter-setting-dashboard.php';
	}

}

return new WC_Filter_Admin();