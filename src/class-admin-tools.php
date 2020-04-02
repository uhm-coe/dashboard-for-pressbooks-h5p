<?php
/**
 * Pressbook H5P Dashboard
 *
 * @license  GPL-3.0+
 * @link     https://github.com/uhm-coe/pressbooks-h5p-dashboard
 * @package  pressbooks-h5p-dashboard
 */

namespace Pressbooks_H5P_Dashboard;

use Pressbooks_H5P_Dashboard\Data;

/**
 * Contains features added in the WordPress Dashboard.
 */
class Admin_Tools extends Static_Instance {
	/**
	 * Load the javascript that annotates the Pressbooks Organize page with
	 * embedded H5P details.
	 *
	 * @hook admin_enqueue_scripts
	 *
	 * @param  string $hook_suffix The current admin page.
	 */
	public function admin_enqueue_scripts__add_h5p_data( $hook_suffix ) {
		// Skip loading if not on Pressbooks Organize page.
		if ( 'toplevel_page_pb_organize' !== $hook_suffix ) {
			return;
		}

		// Fetch embedded H5P shortcodes.
		$data    = Data::get_instance();
		$h5p_ids = $data->get_chapters_with_h5p();

		// Load tippy.js (tooltips) from CDN.
		// See: https://atomiks.github.io/tippyjs/v6/getting-started/.
		wp_enqueue_script( '@popperjs/core@2', 'https://unpkg.com/@popperjs/core@2', array(), '2', true );
		wp_enqueue_script( 'tippy.js@6', 'https://unpkg.com/tippy.js@6', array(), '6', true );
		wp_enqueue_style( 'tippy.js@6/themes/light-border', 'https://unpkg.com/tippy.js@6/themes/light-border.css', array(), '6', 'all' );

		// Load javascript that creates the H5P Content column.
		wp_enqueue_script( 'pressbooks-h5p-dashboard/admin-organize', plugins_url( 'scripts/admin-organize.js', plugin_root() ), array(), '0.1.0', true );
		wp_localize_script(
			'pressbooks-h5p-dashboard/admin-organize',
			'Data',
			array(
				'baseurl'            => get_bloginfo( 'url' ),
				'h5p_url'            => admin_url( 'admin.php?page=h5p&task=show' ),
				'h5p_ids'            => $h5p_ids,
				'column_heading_h5p' => esc_html__( 'H5P Content', 'pressbooks-h5p-dashboard' ),
			)
		);
	}
}
