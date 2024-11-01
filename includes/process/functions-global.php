<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists('wc_filter_get_theme_active_render_class') ) {
	function wc_filter_get_theme_active_render_class(){
		$obj_theme = wp_get_theme();
		$theme_name = $obj_theme->Name;
		switch ($theme_name) {
			case 'Flatsome':
				$class = ' flatsome';
				break;
			case 'Flatsome Child':
				$class = ' flatsome';
				break;
			
			default:
				$class = '';
				break;
		}
		return $class;
	}

}