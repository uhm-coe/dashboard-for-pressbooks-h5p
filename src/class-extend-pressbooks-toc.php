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
 * Contains modifications of the Pressbooks table of contents.
 */
class Extend_Pressbooks_TOC extends Singleton {
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

		// Load tippy.js (tooltips). Note: also run a preflight script checking for
		// any old Popper v1 (as of this writing, used by Pressbooks v5.19.1 on the
		// frontend to render Glossary Term shortcode tooltips). If found, stash it,
		// and then restore it in extend-pressbooks-toc.js with the v2 createPopper
		// function added as a property of the v1 function window.Popper.
		// See: https://atomiks.github.io/tippyjs/v6/getting-started/.
		wp_enqueue_script( 'd4ph/stash-pressbooks-popper', plugins_url( 'scripts/stash-pressbooks-popper.js', plugin_root() ), array(), plugin_version(), true );
		wp_enqueue_script( '@popperjs/core@2.4.2', plugins_url( 'vendor/popperjs/core/2.4.2/dist/umd/popper.min.js', plugin_root() ), array(), '2.4.2', true );
		wp_enqueue_script( 'tippy.js@6.2.3', plugins_url( 'vendor/tippy.js/6.2.3/dist/tippy-bundle.umd.min.js', plugin_root() ), array(), '6.2.3', true );
		wp_enqueue_style( 'tippy.js@6.2.3/themes/light-border', plugins_url( 'vendor/tippy.js/6.2.3/dist/themes/light-border.css', plugin_root() ), array(), '6.2.3', 'all' );

		// Load styles and script that appends H5P details to TOC.
		wp_enqueue_style( 'd4ph/extend-pressbooks-toc', plugins_url( '/styles/extend-pressbooks-toc.css', plugin_root() ), array(), plugin_version() );
		wp_enqueue_script( 'd4ph/extend-pressbooks-toc', plugins_url( 'scripts/extend-pressbooks-toc.js', plugin_root() ), array(), plugin_version(), true );
		wp_localize_script(
			'd4ph/extend-pressbooks-toc',
			'Data',
			array(
				'h5p_ids'           => $h5p_ids,
				'h5p_results'       => $h5p_results,
				'isLoggedIn'        => is_user_logged_in(),
				'loginUrl'          => wp_login_url( get_the_permalink() ),
				'msgYourH5PResults' => esc_html__( 'Your H5P Results', 'd4ph' ),
				'msgLogInToSee'     => esc_html__( 'Sign in to see your results', 'd4ph' ),
			)
		);
	}
}
