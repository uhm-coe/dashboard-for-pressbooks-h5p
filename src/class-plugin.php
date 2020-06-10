<?php
/**
 * Dashboard for Pressbook and H5P
 *
 * @license  GPL-3.0+
 * @link     https://github.com/uhm-coe/dashboard-for-pressbooks-h5p
 * @package  dashboard-for-pressbooks-h5p
 */

namespace Dashboard_For_Pressbooks_H5P;

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

		// Enable localization. Translation files stored in /languages.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Add H5P data column to the Pressbooks Organize page.
		add_action( 'admin_enqueue_scripts', array( Extend_Pressbooks_Organize::get_instance(), 'admin_enqueue_scripts__add_h5p_data' ), 10, 1 );

		// Add H5P counts to parts and chapters in the Pressbooks table of contents.
		add_action( 'wp_enqueue_scripts', array( Extend_Pressbooks_TOC::get_instance(), 'enqueue_scripts' ), 10, 1 );

		// Create plugin options and settings page.
		add_action( 'admin_menu', array( Settings::get_instance(), 'admin_menu__add_options_page' ), 10, 1 );
		add_action( 'admin_init', array( Settings::get_instance(), 'admin_init__register_settings' ), 10, 1 );

		// Add a link to the settings page from the Plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( plugin_root() ), array( Settings::get_instance(), 'plugin_settings_link' ) );

		// Show admin notices on plugin settings page if dependencies are missing.
		add_action( 'admin_notices', array( Settings::get_instance(), 'admin_notices__dependency_check' ), 10, 1 );

		// Add admin styles for the plugin settings page.
		add_action( 'admin_enqueue_scripts', array( Settings::get_instance(), 'admin_enqueue_scripts' ), 10, 1 );

		// Add dashboard widget and ajax handler.
		add_action( 'wp_dashboard_setup', array( Dashboard_Widget::get_instance(), 'add_dashboard_widget' ), 10, 1 );
		add_action( 'wp_ajax_d4ph_dashboard_widget_update', array( Dashboard_Widget::get_instance(), 'update' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( Dashboard_Widget::get_instance(), 'admin_enqueue_scripts' ), 10, 1 );

		// Update dashboard_for_pressbooks_h5p_last_login user meta on login.
		add_action( 'wp_login', array( Save_Last_Login_User_Meta::get_instance(), 'update' ), 10, 2 );

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
			'd4ph',
			false,
			basename( dirname( plugin_root() ) ) . '/languages'
		);
	}
}
