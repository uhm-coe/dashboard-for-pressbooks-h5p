<?php
/**
 * Pressbook H5P Dashboard
 *
 * @license  GPL-3.0+
 * @link     https://github.com/uhm-coe/pressbooks-h5p-dashboard
 * @package  pressbooks-h5p-dashboard
 */

namespace Pressbooks_H5P_Dashboard;

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
		add_action( 'admin_enqueue_scripts', array( Admin_Tools::get_instance(), 'admin_enqueue_scripts__add_h5p_data' ), 10, 1 );
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
	 * Action: plugins_loaded
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'pressbooks-h5p-dashboard',
			false,
			basename( dirname( plugin_root() ) ) . '/languages'
		);
	}
}
