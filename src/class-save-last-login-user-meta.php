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
 * Create the plugin settings page.
 */
class Save_Last_Login_User_Meta extends Static_Instance {

	/**
	 * Update the "last login" user meta on login.
	 *
	 * @hook wp_login
	 *
	 * @param  string  $user_login Username.
	 * @param  WP_User $user       WP_User object of the logged-in user.
	 */
	public function update( $user_login, $user ) {
		update_user_option( $user->ID, 'dashboard_for_pressbooks_h5p_last_login', time() );
	}

}
