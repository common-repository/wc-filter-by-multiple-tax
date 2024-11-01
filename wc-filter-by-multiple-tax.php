<?php
/**
 * Plugin Name: WC Filter By Multiple Tax
 * Plugin URI: https://wordpress.org/plugins/wc-filter-by-multiple-tax/
 * Description: An e-commerce toolkit that helps you filter products by multiple taxonomy (Product category, Feature, Brand).
 * Version: 1.1.0
 * Author: PeePress
 * Author URI: https://peepress.com/
 *
 * Text Domain: wc-filter
 * Domain Path: /languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly


if ( !class_exists( 'WC_Filter_By_Multiple_Tax' ) ) :

	/**
	 * Main WC_Filter_By_Multiple_Tax Class
	 * @class WC_Filter_By_Multiple_Tax
	 * @version 1.1.0
	 */
	final class WC_Filter_By_Multiple_Tax {

		/**
		 * WC_Filter_By_Multiple_Tax version
		 * @var string
		 */
		public $version = '1.1.0';

		/**
		 * The single instance of the class.
		 *
		 * @var WC_Filter_By_Multiple_Tax
		 */
		protected static $_instance = null;

		/**
		 * Main WC_Filter_By_Multiple_Tax Instance.
		 *
		 * @return WC_Filter_By_Multiple_Tax - Main instance
		 */
		public static function instance() {

			if( is_null( self::$_instance ) ){
				self::$_instance = new self();
			}
			return self::$_instance;

		}

		/**
		 * WC_Filter_By_Multiple_Tax Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'WC_Filter_By_Multiple_Tax_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 */
		private function init_hooks() {

			add_action( 'init', array( $this, 'init' ), 0 );

		}

		/**
		 * Define WC_FILTER Constants.
		 */
		private function define_constants() {

			$this->define( 'WC_FILTER_PLUGIN_FILE', __FILE__ );
			$this->define( 'WC_FILTER_ABSPATH', dirname( __FILE__ ) . '/' );
			$this->define( 'WC_FILTER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'WC_FILTER_VERSION', $this->version );

		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {

			if ( ! defined( $name ) ) {
				define( $name, $value );
			}

		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private function is_request( $type ) {

			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}

		}

		/**
		 * Include required core file used in site (for Admin/Frontend)
		 */
		public function includes(){

			include_once( WC_FILTER_ABSPATH . 'includes/_init.php' );

			if ( $this->is_request( 'admin' ) ) {
				// include_once( WC_FILTER_ABSPATH . 'includes/admin/loader.php' );
			}

			if( $this->is_request( 'frontend' ) ){
				$this->frontend_includes();
			}

		}

		/**
		 * Include required frontend files.
		 */
		public function frontend_includes() {

		}

		/**
		 * Init WC_Filter_By_Multiple_Tax when WordPress Initialises.
		 */
		public function init() {

			// Before init action.
			do_action( 'before_wc_filter_init' );

			// Load languages
			$this->load_plugin_textdomain();

			// Init action
			do_action( 'after_wc_filter_init' );

		}

		/**
		 * Load Localisation files.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/wc-filter-multiple-by-tax/wc-filter-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/wc-filter-LOCALE.mo
		 */
		public function load_plugin_textdomain() {

			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'wc-filter' );

			load_textdomain( 'wc-filter-multiple-by-tax', WP_LANG_DIR . '/wc-filter-multiple-by-tax/wc-filter-' . $locale . '.mo' );
			load_plugin_textdomain( 'wc-filter', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
		
		/**
		 * Get value opiton setting wc filter
		 */
		public static function get_setting( $name = '', $value = '', $default = '' ) {

			if ( empty($name) ) {
				return false;
			}

			$value_option = (array) get_option( esc_attr($name), array() );

			if ( empty( $value ) && ! empty( $value_option ) ) {
				return $value_option;
			}

			if ( array_key_exists(  $value, $value_option ) ) {

				if ( ! empty($value) && ! empty( $value_option[$value] ) ) {
					return $value_option[$value];
				}

			}

			return $default;

		}


	}

endif;

/**
 * Main instance of WC_Filter_By_Multiple_Tax.
 *
 * Returns the main instance of WC_FILTER to prevent the need to use globals.
 *
 * @since  2.1
 * @return WC_Filter_By_Multiple_Tax
 */
function WC_FILTER() {
	return WC_Filter_By_Multiple_Tax::instance();
}

// Global for backwards compatibility.
$GLOBALS['wc_filter'] = WC_FILTER();
