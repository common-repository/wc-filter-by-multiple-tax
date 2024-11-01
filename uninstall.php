<?php
/**
 * Fired when the plugin is uninstalled.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

global $wpdb;
// Remove all transient of plugin when uninstall
$sql = "DELETE FROM  $wpdb->options WHERE `option_name` LIKE '%_wc_filter_relate_term_%'";
$wpdb->query($sql);