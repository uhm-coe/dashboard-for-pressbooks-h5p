<?php
/**
 * Plugin Name: Dashboard for Pressbook and H5P
 * Description: This plugin generates summaries of H5P content and results in a Pressbooks book.
 * Author: Paul Ryan <prar@hawaii.edu>
 * Plugin URI: https://github.com/uhm-coe/dashboard-for-pressbooks-h5p
 * Text Domain: dashboard-for-pressbooks-h5p
 * Domain Path: /languages
 * License: GPL3
 * Version: 1.0.3
 *
 * @link    https://github.com/uhm-coe/dashboard-for-pressbooks-h5p
 * @package dashboard-for-pressbooks-h5p
 */

namespace Dashboard_For_Pressbooks_H5P;

/**
 * Include dependencies.
 */
require_once dirname( __FILE__ ) . '/src/abstract-class-static-instance.php';
require_once dirname( __FILE__ ) . '/src/class-plugin.php';
require_once dirname( __FILE__ ) . '/src/class-settings.php';
require_once dirname( __FILE__ ) . '/src/class-data.php';
require_once dirname( __FILE__ ) . '/src/class-extend-pressbooks-organize.php';
require_once dirname( __FILE__ ) . '/src/class-extend-pressbooks-toc.php';
require_once dirname( __FILE__ ) . '/src/class-dashboard-widget.php';
require_once dirname( __FILE__ ) . '/src/class-hide-h5p-for-anonymous-users.php';
require_once dirname( __FILE__ ) . '/src/class-save-last-login-user-meta.php';

/**
 * Set the plugin version (used to cache bust and force asset reload of all
 * enqueued scripts and styles).
 */
function plugin_version() {
	return '1.0.3';
}

/**
 * Helper function to always return the path to the plugin's entry point. Used
 * when locating asset paths using plugins_url().
 */
function plugin_root() {
	return __FILE__;
}

/**
 * Instantiate plugin (register hooks).
 */
Plugin::get_instance();
