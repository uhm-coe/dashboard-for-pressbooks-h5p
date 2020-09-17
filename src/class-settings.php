<?php
/**
 * Dashboard for Pressbooks and H5P
 *
 * @license  GPL-3.0+
 * @link     https://github.com/uhm-coe/dashboard-for-pressbooks-h5p
 * @package  dashboard-for-pressbooks-h5p
 */

namespace Dashboard_For_Pressbooks_H5P;

/**
 * Create the plugin settings page.
 */
class Settings extends Singleton {

	/**
	 * Stores all plugin options from the WordPress database.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Get a plugin option by name.
	 *
	 * @param  string $option Option name.
	 *
	 * @return mixed          Option value, or null if option not found.
	 */
	public function get( $option = '' ) {
		if ( ! isset( $this->options ) ) {
			$this->options = get_option( 'dashboard_for_pressbooks_h5p', $this->sanitized_defaults() );
		}

		return $this->options[ $option ] ?? null;
	}

	/**
	 * Add a link to the settings page from the WordPress Plugins page.
	 *
	 * @hook plugin_action_links_dashboard-for-pressbooks-h5p.php
	 *
	 * @param  array $links Admin sidebar links.
	 * @return array        Admin sidebar links with Authorizer added.
	 */
	public function plugin_settings_link( $links ) {
		$settings_url = admin_url( 'options-general.php?page=dashboard-for-pressbooks-h5p' );
		array_unshift( $links, '<a href="' . $settings_url . '">' . __( 'Settings', 'd4ph' ) . '</a>' );

		return $links;
	}

	/**
	 * Create the options page under Dashboard > Settings.
	 *
	 * @hook admin_menu
	 */
	public function admin_menu__add_options_page() {
		add_options_page(
			__( 'Settings: Dashboard for Pressbooks and H5P', 'd4ph' ),
			__( 'Dashboard for Pressbooks and H5P', 'd4ph' ),
			'manage_options',
			'dashboard-for-pressbooks-h5p',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<div class="banner">
				<img src="<?php echo esc_attr( plugins_url( 'images/banner-1544x500.png', plugin_root() ) ); ?>" alt="Banner image illustrating plugin features" />
			</div>
			<h2><?php esc_html_e( 'Settings: Dashboard for Pressbooks and H5P', 'd4ph' ); ?></h2>
			<p><?php esc_html_e( 'Plugin features', 'd4ph' ); ?>:</p>
			<ol>
				<li>
					<a target="_blank" href="<?php echo esc_attr( admin_url( 'admin.php?page=pb_organize' ) ); ?>"><?php esc_html_e( 'Chapter Badges in Dashboard > Organize', 'd4ph' ); ?></a><br>
					<?php esc_html_e( 'A new column, H5P Content, appears in the Pressbooks Organize dashboard showing which chapters have embedded H5P content.', 'd4ph' ); ?>
				</li>
				<li>
					<a target="_blank" href="<?php echo esc_attr( site_url() ); ?>"><?php esc_html_e( 'Chapter Badges in Table of Contents', 'd4ph' ); ?></a><br>
					<?php esc_html_e( 'A new badge appears next to chapters with embedded H5P content in the Table of Contents. For anonymous users, the badge shows the total number of H5P embeds in the Chapter. For logged in users, the badge shows the number of incomplete H5P embeds, or a checkmark if they are all complete. Hovering over the badge reveals a tooltip with details on each H5P embed.', 'd4ph' ); ?>
				</li>
				<li>
					<a target="_blank" href="<?php echo esc_attr( admin_url( '#d4ph_dashboard_widget' ) ); ?>"><?php esc_html_e( 'Dashboard Widget', 'd4ph' ); ?></a><br>
					<?php esc_html_e( 'A new dashboard widget for instructors showing student progress. Progress can be shown by user and by chapter, and filtered by user role and a range of dates of user registration or last login. Note: last logins are tracked once this plugin is enabled, so there will be no last login times saved from before plugin activation.', 'd4ph' ); ?>
				</li>
				<li>
					<a href="<?php echo esc_attr( '#option_hide_h5p_for_anonymous_users' ); ?>"><?php esc_html_e( 'Hide H5P Content For Anonymous Users', 'd4ph' ); ?></a><br>
					<?php esc_html_e( 'A new option (shown below) to prevent anonymous users from seeing H5P Content. Use this to encourage users to log in so their results can be stored.', 'd4ph' ); ?>
				</li>
			</ol>
			<form method="post" action="options.php" autocomplete="off">
				<?php
				// Render hidden settings fields.
				settings_fields( 'dashboard_for_pressbooks_h5p_group' );
				// Render the sections.
				do_settings_sections( 'dashboard-for-pressbooks-h5p' );
				// Render submit button.
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Create settings sections and fields.
	 *
	 * @hook admin_init
	 */
	public function admin_init__register_settings() {
		register_setting(
			'dashboard_for_pressbooks_h5p_group',
			'dashboard_for_pressbooks_h5p',
			array(
				'default'           => $this->sanitized_defaults(),
				'sanitize_callback' => array( $this, 'sanitized_defaults' ),
			)
		);

		add_settings_section(
			'dashboard-for-pressbooks-h5p-options',
			'', // Don't render a title.
			'__return_false', // Don't render anything at the top of the section.
			'dashboard-for-pressbooks-h5p'
		);

		add_settings_field(
			'hide-h5p-for-anonymous-users',
			__( 'H5P Visibility', 'd4ph' ),
			array( $this, 'render_option_hide_h5p_for_anonymous_users' ),
			'dashboard-for-pressbooks-h5p',
			'dashboard-for-pressbooks-h5p-options'
		);

		add_settings_field(
			'default-pass-percentage',
			__( 'Default Pass Percentage', 'd4ph' ),
			array( $this, 'render_option_default_pass_percentage' ),
			'dashboard-for-pressbooks-h5p',
			'dashboard-for-pressbooks-h5p-options'
		);
	}

	/**
	 * Sanitized the provided options, or return default options if none provided.
	 *
	 * @param  array $options User-submitted plugin options.
	 *
	 * @return array          Sanitized plugin options.
	 */
	public function sanitized_defaults( $options = array() ) {
		// hide_h5p_for_anonymous_users: bool; default to false.
		$options['hide_h5p_for_anonymous_users'] = ! empty( $options['hide_h5p_for_anonymous_users'] );

		// default_pass_percentage: int (0-100); default to 100.
		if ( ! isset( $options['default_pass_percentage'] ) || intval( $options['default_pass_percentage'] ) < 0 ) {
			$options['default_pass_percentage'] = 100;
		} elseif ( intval( $options['default_pass_percentage'] ) > 100 ) {
			$options['default_pass_percentage'] = 100;
		} else {
			$options['default_pass_percentage'] = intval( $options['default_pass_percentage'] );
		}

		return $options;
	}

	/**
	 * Render plugin option: Hide H5P Content for Anonymous users.
	 */
	public function render_option_hide_h5p_for_anonymous_users() {
		$option = 'hide_h5p_for_anonymous_users';
		$value  = $this->get( $option );
		?>
		<label>
			<input
				type="checkbox"
				id="option_<?php echo esc_attr( $option ); ?>"
				name="dashboard_for_pressbooks_h5p[<?php echo esc_attr( $option ); ?>]"
				value="1"<?php checked( $value ); ?>
			/>
			<?php esc_html_e( 'Hide H5P Content for anonymous users', 'd4ph' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'Note: with this setting enabled, embedded H5P Content will be blurred out on the page, with a login button in the center. Enable this to discourage users from interacting with H5P Content anonymously, since no progress or results are stored for users who are not logged in.', 'd4ph' ); ?></p>
		<?php
	}

	/**
	 * Render plugin option: Default pass percentage.
	 */
	public function render_option_default_pass_percentage() {
		$option = 'default_pass_percentage';
		$value  = $this->get( $option );
		?>
		<label>
			<input
				type="number"
				id="option_<?php echo esc_attr( $option ); ?>"
				name="dashboard_for_pressbooks_h5p[<?php echo esc_attr( $option ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				min="0"
				max="100"
			/>
			<?php esc_html_e( 'Default pass percentage (0–100)', 'd4ph' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'Note: some H5P question types, like Multiple Choice, allow you to specify a "pass percentage," the percentage of the total score required for success. For all other question types, specify the value to use here determining success (i.e., whether the Chapter Badges report the H5P Content as complete).', 'd4ph' ); ?></p>
		<?php
	}

	/**
	 * Show notice on the plugin settings page if dependencies are missing.
	 *
	 * @hook admin_notices
	 */
	public function admin_notices__dependency_check() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( isset( $screen->id ) && 'settings_page_dashboard-for-pressbooks-h5p' === $screen->id ) {
			// Show notice if Pressbooks or H5P aren't activated.
			$is_pressbooks_installed = file_exists( WP_PLUGIN_DIR . '/pressbooks/pressbooks.php' );
			$is_pressbooks_active    = is_plugin_active( 'pressbooks/pressbooks.php' );
			$is_h5p_installed        = file_exists( WP_PLUGIN_DIR . '/h5p/h5p.php' );
			$is_h5p_active           = is_plugin_active( 'h5p/h5p.php' );
			if ( ! $is_pressbooks_active || ! $is_h5p_active ) {
				?>
				<div class='notice notice-warning is-dismissible'>
					<h2 class="notice-title"><?php esc_html_e( 'Missing Required Dependencies', 'd4ph' ); ?></h2>
					<p><?php esc_html_e( 'The following plugins need to be activated for this plugin to work:', 'd4ph' ); ?></p>
					<ol>
						<li>
							<a target="_blank" href="<?php esc_attr_e( 'https://docs.pressbooks.org/installation/' ); ?>"><?php esc_html_e( 'Pressbooks', 'd4ph' ); ?></a>
							<?php if ( $is_pressbooks_installed ) : ?>
								<span class="badge badge-success"><span class="dashicons dashicons-yes"></span> Installed</span>
							<?php else : ?>
								<span class="badge badge-warning"><span class="dashicons dashicons-no"></span> Not Installed</span>
							<?php endif; ?>
							<?php if ( $is_pressbooks_active ) : ?>
								<span class="badge badge-success"><span class="dashicons dashicons-yes"></span> Active</span>
							<?php else : ?>
								<span class="badge badge-warning"><span class="dashicons dashicons-no"></span> Not Active</span>
							<?php endif; ?>
						</li>
						<li>
							<a target="_blank" href="<?php esc_attr_e( 'https://h5p.org/documentation/setup/wordpress' ); ?>"><?php esc_html_e( 'H5P', 'd4ph' ); ?></a>
							<?php if ( $is_h5p_installed ) : ?>
								<span class="badge badge-success"><span class="dashicons dashicons-yes"></span> Installed</span>
							<?php else : ?>
								<span class="badge badge-warning"><span class="dashicons dashicons-no"></span> Not Installed</span>
							<?php endif; ?>
							<?php if ( $is_h5p_active ) : ?>
								<span class="badge badge-success"><span class="dashicons dashicons-yes"></span> Active</span>
							<?php else : ?>
								<span class="badge badge-warning"><span class="dashicons dashicons-no"></span> Not Active</span>
							<?php endif; ?>
						</li>
					</ol>
				</div>
				<?php
			}
		}
	}

	/**
	 * Load the styles for the plugin settings page.
	 *
	 * @hook admin_enqueue_scripts
	 *
	 * @param  string $hook_suffix The current admin page.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		// Skip loading if not on plugin settings page.
		if ( 'settings_page_dashboard-for-pressbooks-h5p' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'd4ph/settings', plugins_url( 'styles/settings.css', plugin_root() ), array(), plugin_version(), 'all' );
	}
}
