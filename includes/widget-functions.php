<?php
/**
 * Widget Functions
 *
 * Widget related functions and widget registration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include widget classes.
include_once( dirname( __FILE__ ) . '/abstracts/abstract-wc-widget.php' );
include_once( dirname( __FILE__ ) . '/widgets/wc-filter-taxonomy-multi-level.php' );

/**
 * Register Widgets.
 */
function wc_filter_register_widgets() {

	register_widget( 'WC_Filter_Taxonomy_Multi_Level' );
	
}
add_action( 'widgets_init', 'wc_filter_register_widgets' );