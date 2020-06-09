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
 * Contains modifications of the Pressbooks table of contents.
 */
class Extend_Pressbooks_TOC extends Static_Instance {
	/**
	 * Load the javascript that annotates the Pressbooks table of contents with
	 * embedded H5P details.
	 *
	 * @hook enqueue_scripts
	 */
	public function enqueue_scripts() {
		// Only load on a single page or if Pressbooks first post id is set.
		// Note: this is the logic Pressbooks uses to render the TOC.
		if ( ! is_single() && ! ( function_exists( 'pb_get_first_post_id' ) && pb_get_first_post_id() ) ) {
			return;
		}

		// Do nothing if plugin dependencies are not installed and activated.
		if ( ! is_plugin_active( 'h5p/h5p.php' ) || ! is_plugin_active( 'pressbooks/pressbooks.php' ) ) {
			return;
		}

		$data = Data::get_instance();

		// Fetch embedded H5P shortcodes.
		$h5p_ids = $data->get_chapters_with_h5p();

		// Fetch current user's results. If there are multiple attempts on any, keep
		// the higher score.
		$h5p_results = $data->get_my_h5p_results();

		// Load tippy.js (tooltips) from CDN.
		// See: https://atomiks.github.io/tippyjs/v6/getting-started/.
		wp_enqueue_script( '@popperjs/core@2', 'https://unpkg.com/@popperjs/core@2', array(), '2', true );
		wp_enqueue_script( 'tippy.js@6', 'https://unpkg.com/tippy.js@6', array(), '6', true );
		wp_enqueue_style( 'tippy.js@6/themes/light-border', 'https://unpkg.com/tippy.js@6/themes/light-border.css', array(), '6', 'all' );

		// Load styles and script that appends H5P details to TOC.
		wp_enqueue_style( 'p22d/extend-pressbooks-toc', plugins_url( '/styles/extend-pressbooks-toc.css', plugin_root() ), array(), plugin_version() );
		wp_enqueue_script( 'p22d/extend-pressbooks-toc', plugins_url( 'scripts/extend-pressbooks-toc.js', plugin_root() ), array(), plugin_version(), true );
		wp_localize_script(
			'p22d/extend-pressbooks-toc',
			'Data',
			array(
				'h5p_ids'           => $h5p_ids,
				'h5p_results'       => $h5p_results,
				'isLoggedIn'        => is_user_logged_in(),
				'loginUrl'          => wp_login_url( get_the_permalink() ),
				'msgYourH5PResults' => esc_html__( 'Your H5P Results', 'p22d' ),
				'msgLogInToSee'     => esc_html__( 'Sign in to see your results', 'p22d' ),
			)
		);
	}
}
