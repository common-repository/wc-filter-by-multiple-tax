<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load main file admin
include ( dirname(__FILE__) . '/wc-filter-admin.php' );

// Load Tab
$tab_path = plugin_dir_path( __FILE__ ) . 'tab/';
foreach ( glob($tab_path.'*.php') as $file_tab) {
	require $file_tab;
}

// Load Form
$form_path = plugin_dir_path( __FILE__ ) . 'form/';
foreach ( glob($form_path.'*.php') as $file_form) {
	require $file_form;
}