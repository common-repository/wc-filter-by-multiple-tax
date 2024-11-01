<?php
/**
 * admin_functions.php
 *
 * @package:
 * @since  : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'wc_filter_tab_setting' ) ) :

	/**
	 * Create tab setting
	 *
	 * @param array $fields
	 */
	function wc_filter_tab_setting( $fields = array() ) {

		$fields = apply_filters( 'wc_filter_tab_setting', $fields );

		/**
		 * Reorder list field by position
		 */
		$position = array();
		foreach ( $fields as $key => $row ) {
			$position[ $key ] = $row[ 'position' ];
		}
		array_multisort( $position, SORT_ASC, $fields );

		/**
		 * Show field
		 */
		echo '<div class="wc-filter-tab-setting">';
		foreach ( $fields as $field ) {

			if ( ! empty( $field[ 'name' ] ) ) {

				$id = ( ! empty( $field[ 'id' ] ) ? ' data-id=' . esc_attr( $field[ 'id' ] ) : '' );

				echo '<span class="wc-filter-tab-item ' . ( ! empty( $field[ 'class' ] ) ? esc_attr( $field[ 'class' ] ) : '' ) . '"' . esc_html( $id ) . '>';
				echo esc_html( $field[ 'name' ] );
				echo '</span>';
			}
		}
		echo '</div><!-- /.wc-filter-tab-setting -->';
	}

endif;