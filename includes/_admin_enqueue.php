<?php
/**
 * Handle frontend scripts
 */
if ( ! function_exists('wc_filter_func_admin_enqueue_scripts') ) :


    function wc_filter_func_admin_enqueue_scripts() {

		$suffix = (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG) ? '' : '.min';

		/**
		 * Style
		 */
		wp_enqueue_style( 'wc-filter-admin', wc_filter_func_get_asset_url('assets/css/admin.css'), array(), '1.0.0' );

		/**
		 * Script
		 */
		wp_enqueue_script( 'wc-filter-admin', wc_filter_func_get_asset_url('assets/js/admin/admin'.$suffix.'.js'), array('jquery'), null, true );

    }
    add_action( 'admin_init', 'wc_filter_func_admin_enqueue_scripts' );

endif;