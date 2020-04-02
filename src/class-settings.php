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

}
