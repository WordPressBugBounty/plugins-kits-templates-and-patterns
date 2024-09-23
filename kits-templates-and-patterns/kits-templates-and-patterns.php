<?php
/**
 * Plugin Name: Kits, Templates and Patterns
 * Plugin URI: https://getbowtied.com
 * Description: Import Kits, Templates and Patterns with just one click.
 * Version: 1.7
 * Author: Get Bowtied
 * Author URI: https://getbowtied.com
 * License: GPLv3 or later
 * Text Domain: kits-templates-and-patterns
 * Domain Path: /languages/
 *
 * @package GetBowtied_Import_Demo_Content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define GETBOWTIED_IDC_PLUGIN_FILE.
if ( ! defined( 'GETBOWTIED_IDC_PLUGIN_FILE' ) ) {
	define( 'GETBOWTIED_IDC_PLUGIN_FILE', __FILE__ );
}

// Include the main GetBowtied Import Demo Content class.
if ( ! class_exists( 'GetBowtied_Import_Demo_Content' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-getbowtied-kits-templates-and-patterns.php';
	//include_once dirname( __FILE__ ) . '/includes/admin/pointers/pointers.php';
}

/**
 * Main instance of GetBowtied Demo importer.
 *
 * Returns the main instance of GETBOWTIED_IDC to prevent the need to use globals.
 *
 * @since  1.3.4
 * @return GetBowtied_Import_Demo_Content
 */
function getbowtied_import_demo_content() {
	return GetBowtied_Import_Demo_Content::instance();
}

// Global for backwards compatibility.
$GLOBALS['getbowtied-kits-templates-and-patterns'] = getbowtied_import_demo_content();












