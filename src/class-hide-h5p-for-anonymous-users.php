<?php
/**
 * Dashboard for Pressbooks and H5P
 *
 * @license  GPL-3.0+
 * @link     https://github.com/uhm-coe/dashboard-for-pressbooks-h5p
 * @package  dashboard-for-pressbooks-h5p
 */

namespace Dashboard_For_Pressbooks_H5P;

use Dashboard_For_Pressbooks_H5P\Settings;

/**
 * Create the plugin settings page.
 */
class Hide_H5P_For_Anonymous_Users extends Singleton {

	/**
	 * If the option is enabled, load the assets for hiding H5P Content.
	 *
	 * @hook enqueue_scripts
	 */
	public function enqueue_scripts() {
		$settings = Settings::get_instance();
		if ( ! is_user_logged_in() && $settings->get( 'hide_h5p_for_anonymous_users' ) ) {
			wp_enqueue_style( 'd4ph/hide-h5p-for-anonymous-users', plugins_url( '/styles/hide-h5p-for-anonymous-users.css', plugin_root() ), array(), plugin_version() );
			wp_enqueue_script( 'd4ph/hide-h5p-for-anonymous-users', plugins_url( 'scripts/hide-h5p-for-anonymous-users.js', plugin_root() ), array( 'jquery' ), plugin_version(), true );
			wp_localize_script(
				'd4ph/hide-h5p-for-anonymous-users',
				'Data',
				array(
					'loginUrl'           => wp_login_url( get_the_permalink() ),
					'msgLogInToComplete' => esc_html__( 'Sign in to complete this activity', 'd4ph' ),
				)
			);
		}
	}

	/**
	 * Add "anonymous" to body classes (for detection in css and js).
	 *
	 * @hook body_class
	 *
	 * @param  array $classes An array of body class names.
	 *
	 * @return array          An array of body class names.
	 */
	public function body_class__add_anonymous( $classes ) {
		if ( ! is_user_logged_in() ) {
			$classes = array_merge( $classes, array( 'anonymous' ) );
		}

		return $classes;
	}

}
