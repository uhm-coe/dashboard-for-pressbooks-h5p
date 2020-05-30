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
class Settings extends Static_Instance {

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
			$this->options = get_option( 'pressbooks_h5p_dashboard', $this->sanitized_defaults() );
		}

		return $this->options[ $option ] ?? null;
	}

	/**
	 * Create the options page under Dashboard > Settings.
	 *
	 * @hook admin_menu
	 */
	public function admin_menu__add_options_page() {
		add_options_page(
			__( 'Pressbooks H5P Dashboard Settings', 'p22d' ),
			__( 'Pressbooks H5P Dashboard', 'p22d' ),
			'manage_options',
			'pressbooks-h5p-dashboard',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Pressbooks H5P Dashboard Settings', 'p22d' ); ?></h2>
			<p>Plugin features:</p>
			<ol>
				<li>
					<a target="_blank" href="<?php esc_attr_e( admin_url( 'admin.php?page=pb_organize' ) ); ?>"><?php esc_html_e( 'Chapter Badges in Dashboard > Organize', 'p22d' ); ?></a><br>
					<?php esc_html_e( 'A new column, H5P Content, appears in the Pressbooks Organize dashboard showing which chapters have embedded H5P content.', 'p22d' ); ?>
				</li>
				<li>
					<a target="_blank" href="<?php esc_attr_e( site_url() ); ?>"><?php esc_html_e( 'Chapter Badges in Table of Contents', 'p22d' ); ?></a><br>
					<?php esc_html_e( 'A new badge appears next to chapters with embedded H5P content in the Table of Contents. For anonymous users, the badge shows the total number of H5P embeds in the Chapter. For logged in users, the badge shows the number of incomplete H5P embeds, or a checkmark if they are all complete. Hovering over the badge reveals a tooltip with details on each H5P embed.', 'p22d' ); ?>
				</li>
				<li>
					<a target="_blank" href="<?php esc_attr_e( admin_url( '#p22d_dashboard_widget' ) ); ?>"><?php esc_html_e( 'Dashboard Widget', 'p22d' ); ?></a><br>
					<?php esc_html_e( 'A new dashboard widget for instructors showing student progress. Progress can be shown by user and by chapter, and filtered by user role and a range of dates of user registration or last login. Note: last logins are tracked once this plugin is enabled, so there will be no last login times saved from before plugin activation.', 'p22d' ); ?>
				</li>
				<li>
					<a href="<?php esc_attr_e( '#option_hide_h5p_for_anonymous_users' ); ?>"><?php esc_html_e( 'Hide H5P Content For Anonymous Users', 'p22d' ); ?></a><br>
					<?php esc_html_e( 'A new option (shown below) to prevent anonymous users from seeing H5P Content. Use this to encourage users to log in so their results can be stored.', 'p22d' ); ?>
				</li>
			</ol>
			<form method="post" action="options.php" autocomplete="off">
				<?php
				// Render hidden settings fields.
				settings_fields( 'pressbooks_h5p_dashboard_group' );
				// Render the sections.
				do_settings_sections( 'pressbooks-h5p-dashboard' );
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
			'pressbooks_h5p_dashboard_group',
			'pressbooks_h5p_dashboard',
			array(
				'default'           => $this->sanitized_defaults(),
				'sanitize_callback' => array( $this, 'sanitized_defaults' ),
			)
		);

		add_settings_section(
			'pressbooks-h5p-dashboard-options',
			'', // Don't render a title.
			'__return_false', // Don't render anything at the top of the section.
			'pressbooks-h5p-dashboard'
		);

		add_settings_field(
			'hide-h5p-for-anonymous-users',
			__( 'H5P Visibility', 'p22d' ),
			array( $this, 'render_option_hide_h5p_for_anonymous_users' ),
			'pressbooks-h5p-dashboard',
			'pressbooks-h5p-dashboard-options'
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
				name="pressbooks_h5p_dashboard[<?php echo esc_attr( $option ); ?>]"
				value="1"<?php checked( $value ); ?>
			/>
			<?php esc_html_e( 'Hide H5P Content for anonymous users', 'p22d' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'Note: with this setting enabled, embedded H5P Content will be blurred out on the page, with a login button in the center. Enable this to discourage users from interacting with H5P Content anonymously, since no progress or results are stored for users who are not logged in.', 'p22d' ); ?></p>
		<?php
	}

	/**
	 * Show notice on the plugin settings page if dependencies are missing.
	 *
	 * @hook admin_notices
	 */
	public function admin_notices__dependency_check() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( isset( $screen->id ) && 'settings_page_pressbooks-h5p-dashboard' === $screen->id ) {
			// Show notice if Pressbooks or H5P aren't activated.
			$is_pressbooks_installed = file_exists( WP_PLUGIN_DIR . '/pressbooks/pressbooks.php' );
			$is_pressbooks_active    = is_plugin_active( 'pressbooks/pressbooks.php' );
			$is_h5p_installed        = file_exists( WP_PLUGIN_DIR . '/h5p/h5p.php' );
			$is_h5p_active           = is_plugin_active( 'h5p/h5p.php' );
			if ( ! $is_pressbooks_active || ! $is_h5p_active ) {
				?>
				<div class='notice notice-warning is-dismissible'>
					<h2 class="notice-title"><?php esc_html_e( 'Missing Required Dependencies', 'p22d' ); ?></h2>
					<p><?php esc_html_e( 'The following plugins need to be activated for this plugin to work:', 'p22d' ); ?></p>
					<ol>
						<li>
							<a target="_blank" href="<?php esc_attr_e( 'https://docs.pressbooks.org/installation/' ); ?>"><?php esc_html_e( 'Pressbooks', 'p22d' ); ?></a>
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
							<a target="_blank" href="<?php esc_attr_e( 'https://h5p.org/documentation/setup/wordpress' ); ?>"><?php esc_html_e( 'H5P', 'p22d' ); ?></a>
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
		if ( 'settings_page_pressbooks-h5p-dashboard' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'p22d/settings', plugins_url( 'styles/settings.css', plugin_root() ), array(), plugin_version(), 'all' );
	}
}
