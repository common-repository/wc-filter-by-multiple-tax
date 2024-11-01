<?php
/**
 * WC Filter By Multiple Tax Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author 		TVLA92
 * @category 	Core
 * @package 	WC Filter By Multiple Tax/_init
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! in_array(  'woocommerce/woocommerce.php',  apply_filters( 'active_plugins', get_option( 'active_plugins' ) )  )  ) {

	function wc_filter_notice__error() {
	    ?>
	    <div class="notice notice-error is-dismissible">
	    	<p><strong><?php echo esc_html__( 'WC Filter By Multiple Tax', 'wc-filter' ); ?></strong></p>
	        <p><?php echo esc_attr__( 'WC Filter By Multiple Tax is enabled but not effective. It requires WooCommerce in order to work.', 'wc-filter' ); ?></p>
	    </div>
	    <?php
	}
	add_action( 'admin_notices', 'wc_filter_notice__error' );

} else {

	// Include core functions (available in both admin and frontend).
	include( 'process/list_category_img.php' );
	include( 'process/functions-global.php' );
	include( 'process/admin_functions.php' );
	include( 'process/functions-hook.php' );
	include( 'process/functions.php' );
	include( '_frontend_enqueue.php' );
	include( 'widget-functions.php' );
	include( 'process/fields.php' );
	include( '_admin_enqueue.php' );
	include( 'woocommerce.php' );
	
}

