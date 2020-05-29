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
 * Create the plugin settings page.
 */
class Save_Last_Login_User_Meta extends Static_Instance {

	/**
	 * Update the pressbook_h5p_dashboard_last_login user meta on login.
	 *
	 * @hook wp_login
	 *
	 * @param  string  $user_login Username.
	 * @param  WP_User $user       WP_User object of the logged-in user.
	 */
	public function update( $user_login, $user ) {
		update_user_meta( $user->ID, 'pressbooks_h5p_dashboard_last_login', time() );
	}

}
