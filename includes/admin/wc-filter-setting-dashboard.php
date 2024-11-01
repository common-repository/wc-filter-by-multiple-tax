<?php
/**
 * Show HTML setting dashboard
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="wrap">
	<?php
		/**
		 * Create tab
		 */
		$list_tab = apply_filters( 'wc_filter_tab_setting/Config', array() );

		/**
		 * @hook wc_filter_tab_setting_content_before
		 */
		do_action( 'wc_filter_tab_setting_content_before', $list_tab );

		wc_filter_tab_setting($list_tab);

		/**
		 * @hook wc_filter_tab_setting_content_after
		 * see 
		 */
		do_action( 'wc_filter_tab_setting_content_after', $list_tab );
	?>
</div>