<?php
/**
 * Plugin Name: Dashboard for Pressbooks and H5P
 * Description: This plugin generates summaries of H5P content and results in a Pressbooks book.
 * Author: Paul Ryan <prar@hawaii.edu>
 * Plugin URI: https://github.com/uhm-coe/dashboard-for-pressbooks-h5p
 * Text Domain: dashboard-for-pressbooks-h5p
 * Domain Path: /languages
 * License: GPL3
 * Requires at least: 5.3
 * Requires PHP: 5.6.20
 * Version: 1.1.6
 *
 * @link    https://github.com/uhm-coe/dashboard-for-pressbooks-h5p
 * @package dashboard-for-pressbooks-h5p
 */

namespace Dashboard_For_Pressbooks_H5P;

/**
 * Set the plugin version (used to cache bust and force asset reload of all
 * enqueued scripts and styles).
 */
function plugin_version() {
	return '1.1.6';
}

/**
 * Helper function to always return the path to the plugin's entry point. Used
 * when locating asset paths using plugins_url().
 */
function plugin_root() {
	return __FILE__;
}

/**
 * Include dependencies.
 */
require_once dirname( __FILE__ ) . '/src/abstract-class-singleton.php';
require_once dirname( __FILE__ ) . '/src/class-plugin.php';
require_once dirname( __FILE__ ) . '/src/class-settings.php';
require_once dirname( __FILE__ ) . '/src/class-data.php';
require_once dirname( __FILE__ ) . '/src/class-extend-pressbooks-organize.php';
require_once dirname( __FILE__ ) . '/src/class-extend-pressbooks-toc.php';
require_once dirname( __FILE__ ) . '/src/class-dashboard-widget.php';
require_once dirname( __FILE__ ) . '/src/class-hide-h5p-for-anonymous-users.php';
require_once dirname( __FILE__ ) . '/src/class-save-last-login-user-meta.php';

/**
 * Instantiate plugin (register hooks).
 */
Plugin::get_instance();
