<?php
/**
 * Dashboard for Pressbooks and H5P
 *
 * @license  GPL-3.0+
 * @link     https://github.com/uhm-coe/dashboard-for-pressbooks-h5p
 * @package  dashboard-for-pressbooks-h5p
 */

namespace Dashboard_For_Pressbooks_H5P;

use Dashboard_For_Pressbooks_H5P\Data;

/**
 * Contains modifications of Pressbooks Organize WordPress Dashboard widget.
 */
class Extend_Pressbooks_Organize extends Singleton {
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

		// Do nothing if plugin dependencies are not installed and activated.
		if ( ! is_plugin_active( 'h5p/h5p.php' ) || ! is_plugin_active( 'pressbooks/pressbooks.php' ) ) {
			return;
		}

		// Fetch embedded H5P shortcodes.
		$data    = Data::get_instance();
		$h5p_ids = $data->get_chapters_with_h5p();

		// Load tippy.js (tooltips).
		// See: https://atomiks.github.io/tippyjs/v6/getting-started/.
		wp_enqueue_script( '@popperjs/core@2.4.2', plugins_url( 'vendor/popperjs/core/2.4.2/dist/umd/popper.min.js', plugin_root() ), array(), '2.4.2', true );
		wp_enqueue_script( 'tippy.js@6.2.3', plugins_url( 'vendor/tippy.js/6.2.3/dist/tippy-bundle.umd.min.js', plugin_root() ), array(), '6.2.3', true );
		wp_enqueue_style( 'tippy.js@6.2.3/themes/light-border', plugins_url( 'vendor/tippy.js/6.2.3/dist/themes/light-border.css', plugin_root() ), array(), '6.2.3', 'all' );

		// Load javascript that creates the H5P Content column.
		wp_enqueue_script( 'd4ph/extend-pressbooks-organize', plugins_url( 'scripts/extend-pressbooks-organize.js', plugin_root() ), array(), plugin_version(), true );
		wp_localize_script(
			'd4ph/extend-pressbooks-organize',
			'Data',
			array(
				'h5p_url'            => admin_url( 'admin.php?page=h5p&task=show' ),
				'h5p_ids'            => $h5p_ids,
				'column_heading_h5p' => esc_html__( 'H5P Content', 'd4ph' ),
			)
		);
	}
}
