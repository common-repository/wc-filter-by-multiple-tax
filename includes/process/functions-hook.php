<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add class to List Taxonomies Multiple Level
if( ! function_exists( 'wc_filter_add_class_by_theme_active' ) ){
	function wc_filter_add_class_by_theme_active($class){
		$class_theme = wc_filter_get_theme_active_render_class();
		$class .= ''.$class_theme;
		return $class;
	}
	add_filter( 'wc_filter_add_class_to_ul', 'wc_filter_add_class_by_theme_active' );
}