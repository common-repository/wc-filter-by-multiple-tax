<?php
/**
 * Show html setting property
 *
 * @author  NooTeam <suppport@nootheme.com>
 * @verion  0.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! function_exists( 'wc_filter_show_html_setting_property' ) ) :

	function wc_filter_show_html_setting_property( $list_tab ) {

		$list_tab[] = array(
			'name'     => esc_html__( 'Dashboard', 'wc-filter' ),
			'id'       => 'tab-setting',
			'class'    => 'active',
			'position' => 5,
		);

		$list_tab[] = array(
			'name'     => esc_html__( 'Document', 'wc-filter' ),
			'id'       => 'tab-document',
			'class'    => '',
			'position' => 20,
		);
		return $list_tab;
	}

	add_filter( 'wc_filter_tab_setting/Config', 'wc_filter_show_html_setting_property', 5 );

endif;