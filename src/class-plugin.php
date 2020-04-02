<?php
/**
 * Pressbook H5P Dashboard
 *
 * @license  GPL-3.0+
 * @link     https://github.com/uhm-coe/pressbooks-h5p-dashboard
 * @package  pressbooks-h5p-dashboard
 */

namespace Pressbooks_H5P_Dashboard;

use Pressbooks_H5P_Dashboard\Hide_H5P_For_Anonymous_Users;

/**
 * Main plugin class. Activates/deactivates the plugin, and registers all hooks.
 */
class Plugin extends Static_Instance {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Installation and uninstallation hooks.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		/**
		 * Register hooks.
		 */

		// Add H5P data column to the Pressbooks Organize page.
		add_action( 'admin_enqueue_scripts', array( Extend_Pressbooks_Organize::get_instance(), 'admin_enqueue_scripts__add_h5p_data' ), 10, 1 );

		// Create plugin options and settings page.
		add_action( 'admin_menu', array( Settings::get_instance(), 'admin_menu__add_options_page' ), 10, 1 );
		add_action( 'admin_init', array( Settings::get_instance(), 'admin_init__register_settings' ), 10, 1 );

		// Load custom javascript for hiding H5P Content from anonymous users.
		add_action( 'wp_enqueue_scripts', array( Hide_H5P_For_Anonymous_Users::get_instance(), 'enqueue_scripts' ), 10, 1 );
		add_filter( 'body_class', array( Hide_H5P_For_Anonymous_Users::get_instance(), 'body_class__add_anonymous' ), 10, 1 );

	}

	/**
	 * Plugin activation hook.
	 * Will also activate the plugin for all sites/blogs if this is a "Network enable."
	 *
	 * @param bool $network_wide Whether the plugin is being activated for the whole network.
	 * @return void
	 */
	public function activate( $network_wide ) {
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {
		// Do nothing. Use uninstall.php instead.
	}

	/**
	 * Load translated strings from *.mo files in /languages.
	 *
	 * @hook plugins_loaded
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'pressbooks-h5p-dashboard',
			false,
			basename( dirname( plugin_root() ) ) . '/languages'
		);
	}
}
