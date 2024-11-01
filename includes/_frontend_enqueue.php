<?php
/**
 * Handle frontend scripts
 */
if ( ! function_exists( 'wc_filter_func_frontend_enqueue_scripts' ) ) :

    function wc_filter_func_frontend_enqueue_scripts() {

		$suffix = (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG) ? '' : '.min';

    	if ( !is_admin() ){

    		/**
    		 * Style
    		 */
    		wp_register_style( 'wc-filter', wc_filter_func_get_asset_url('assets/css/wc-filter.css'), array(), null, null );

    		/**
    		 * Script
    		 */
    		wp_register_script( 'main-widget-filter', wc_filter_func_get_asset_url('assets/js/frontend/main-widget-filter'.$suffix.'.js'), array('jquery'), null, true );
            wp_localize_script( 'main-widget-filter', 'Main_Widget_Filter', array(
                'current_page'       => wc_filter_func_get_wc_page_current_url(),
                'label_box_selected' => esc_html__( 'Your selection', 'wc-filter' ),
                'label_remove'       => esc_html__( 'Clear all', 'wc-filter' ),
            ) );

    	}

    }
    add_action( 'wp_enqueue_scripts', 'wc_filter_func_frontend_enqueue_scripts' );

endif;