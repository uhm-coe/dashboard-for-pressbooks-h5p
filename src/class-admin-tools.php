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
		$h5p_ids = $this->get_chapters_with_h5p();

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
				'h5p_ids'            => wp_json_encode( $h5p_ids ),
				'column_heading_h5p' => esc_html__( 'H5P Content', 'pressbooks-h5p-dashboard' ),
			)
		);
	}

	/**
	 * Returns H5P ids grouped by the post_id they are embedded on.
	 *
	 * @return array Post ID keys with an array of H5P ids under each.
	 */
	public function get_chapters_with_h5p() {
		global $wpdb;
		$H5P_Plugin = \H5P_Plugin::get_instance();

		$h5p_by_chapter = array();

		$chapters_with_h5p = get_posts(
			array(
				'post_type'      => 'chapter',
				'posts_per_page' => -1,
				's'              => '[h5p',
			)
		);

		foreach ( $chapters_with_h5p as $chapter ) {
			// Get H5P IDs in each chapter (e.g., [h5p id="123"]).
			preg_match_all( '/\[h5p id="([0-9]*)"/', $chapter->post_content, $matches );
			foreach ( $matches[1] as $h5p_id ) {
				if ( ! isset( $h5p_by_chapter[ $chapter->ID ] ) ) {
					$h5p_by_chapter[ $chapter->ID ] = array();
				}

				$h5p = $H5P_Plugin->get_content( $h5p_id );
				if ( ! empty( $h5p['title'] ) && ! empty( $h5p['library']['name'] ) ) {
					if ( ! isset( $h5p_by_chapter[ $chapter->ID ] ) ) {
						$h5p_by_chapter[ $chapter->ID ] = array();
					}
					$h5p_by_chapter[ $chapter->ID ][ $h5p_id ] = $h5p['title'] . ' (' . str_replace( 'H5P.', '', $h5p['library']['name'] ) . ')';
				}
			}

			// Get H5P slugs in each chapter (e.g., [h5p id="sample-page"]).
			// Note: this is if the H5P "Add Content Method" setting is set to
			// "Reference content by slug" and requires a separate lookup of the slug
			// to ID mapping.
			preg_match_all( '/\[h5p slug="([^"]*)"/', $chapter->post_content, $matches );
			foreach ( $matches[1] as $maybe_h5p_slug ) {
				// Look up H5P id from slug.
				$h5p_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->prepare(
						"SELECT id FROM {$wpdb->prefix}h5p_contents WHERE slug = %s",
						$maybe_h5p_slug
					)
				);
				if ( empty( $h5p_id ) ) {
					continue;
				}

				// Get H5P content details.
				$h5p = $H5P_Plugin->get_content( $h5p_id );
				if ( ! empty( $h5p['title'] ) && ! empty( $h5p['library']['name'] ) ) {
					if ( ! isset( $h5p_by_chapter[ $chapter->ID ] ) ) {
						$h5p_by_chapter[ $chapter->ID ] = array();
					}
					$h5p_by_chapter[ $chapter->ID ][ $h5p_id ] = $h5p['title'] . ' (' . str_replace( 'H5P.', '', $h5p['library']['name'] ) . ')';
				}
			}
		}

		return $h5p_by_chapter;
	}
}
