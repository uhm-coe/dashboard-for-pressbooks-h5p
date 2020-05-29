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
 * Create the plugin dashboard widget.
 */
class Dashboard_Widget extends Static_Instance {

	/**
	 * Dashboard widget user options (e.g., filters, users per page).
	 */
	private $options;


	/**
	 * Get all user options.
	 *
	 * @return array All user options (defaults if none set yet).
	 */
	public function get_options() {
		if ( ! isset( $this->options ) ) {
			$this->options = $this->sanitized_defaults( get_user_option( 'pressbooks_h5p_dashboard', get_current_user_id() ) );
		}

		return $this->options;
	}


	/**
	 * Get a user option by name.
	 *
	 * @param  string $option Option name.
	 *
	 * @return mixed          Option value, or null if option not found.
	 */
	public function get_option( $option = '' ) {
		$options = $this->get_options();

		return $options[ $option ] ?? null;
	}


	/**
	 * Save user options to usermeta.
	 *
	 * @param  array  $options User options.
	 */
	public function update_options( $options = array() ) {
		$this->options = $this->sanitized_defaults( $options );
		update_user_option( get_current_user_id(), 'pressbooks_h5p_dashboard', $this->options );
	}


	/**
	 * Sanitized the provided options, or return default options if none provided.
	 *
	 * @param  array $options User-submitted plugin options.
	 *
	 * @return array          Sanitized plugin options.
	 */
	public function sanitized_defaults( $options = array() ) {
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		// Role to filter to. Default: all roles.
		if ( empty( $options['role'] ) || ! in_array( $options['role'], array_keys( get_editable_roles() ) )) {
			$options['role'] = '';
		}

		// Usermeta to filter to (e.g., last logged in time). Default: all users.
		if ( empty( $options['filter'] ) || ! in_array( $options['filter'], array_keys( $this->get_filters() ) ) ) {
			$options['filter'] = '';
		}

		// Users per page in table (min 1, max 500). Default: 10 users.
		if ( empty( $options['per_page'] ) || $options['per_page'] < 1 || $options['per_page'] > 500 ) {
			$options['per_page'] = 10;
		}

		return $options;
	}


	/**
	 * Get the data (value, label, time) for the available filters on the user list.
	 */
	public function get_filters() {
		$now                = time();
		$first_day_of_week  = strtotime( '-' . date( 'w' ) . ' days ' );
		$first_day_of_month = strtotime( '-' . ( date( 'j' ) - 1 ) . ' days ' );
		$first_day_of_year  = strtotime( '-' . date( 'z' ) . ' days ' );
		$last_jan_1         = strtotime( 'first day of January' );
		$last_may_1         = strtotime( 'first day of May' );
		$last_aug_1         = strtotime( 'first day of August' );
		if ( $last_jan_1 > $now ) {
			$last_jan_1 = strtotime('first day of January ' . ( date( 'Y' ) - 1 ) );
		}
		if ( $last_may_1 > $now ) {
			$last_may_1 = strtotime('first day of May ' . ( date( 'Y' ) - 1 ) );
		}
		if ( $last_aug_1 > $now ) {
			$last_aug_1 = strtotime('first day of August ' . ( date( 'Y' ) - 1 ) );
		}

		return array(
			'registered_this_week'  => array(
				'label' => __( 'Registered this week', 'p22d' ),
				'time'  => $first_day_of_week,
				'type'  => 'registered',
			),
			'registered_this_month' => array(
				'label' => __( 'Registered this month', 'p22d' ),
				'time'  => $first_day_of_month,
				'type'  => 'registered',
			),
			'registered_this_year'  => array(
				'label' => __( 'Registered this year', 'p22d' ),
				'time'  => $first_day_of_year,
				'type'  => 'registered',
			),
			'registered_since_jan'  => array(
				'label' => __( 'Registered since January', 'p22d' ),
				'time'  => $last_jan_1,
				'type'  => 'registered',
			),
			'registered_since_may'  => array(
				'label' => __( 'Registered since May', 'p22d' ),
				'time'  => $last_may_1,
				'type'  => 'registered',
			),
			'registered_since_aug'  => array(
				'label' => __( 'Registered since August', 'p22d' ),
				'time'  => $last_aug_1,
				'type'  => 'registered',
			),
			'signed_in_this_week'   => array(
				'label' => __( 'Signed in this week', 'p22d' ),
				'time'  => $first_day_of_week,
				'type'  => 'signed_in',
			),
			'signed_in_this_month'  => array(
				'label' => __( 'Signed in this month', 'p22d' ),
				'time'  => $first_day_of_month,
				'type'  => 'signed_in',
			),
			'signed_in_this_year'   => array(
				'label' => __( 'Signed in this year', 'p22d' ),
				'time'  => $first_day_of_year,
				'type'  => 'signed_in',
			),
			'signed_in_since_jan'   => array(
				'label' => __( 'Signed in since January', 'p22d' ),
				'time'  => $last_jan_1,
				'type'  => 'signed_in',
			),
			'signed_in_since_may'   => array(
				'label' => __( 'Signed in since May', 'p22d' ),
				'time'  => $last_may_1,
				'type'  => 'signed_in',
			),
			'signed_in_since_aug'   => array(
				'label' => __( 'Signed in since August', 'p22d' ),
				'time'  => $last_aug_1,
				'type'  => 'signed_in',
			),
		);
	}


	/**
	 * Load the scripts and styles for the dashboard widget.
	 *
	 * @hook admin_enqueue_scripts
	 *
	 * @param  string $hook_suffix The current admin page.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		// Skip loading if not on admin dashboard.
		if ( 'index.php' !== $hook_suffix ) {
			return;
		}

		// Load tippy.js (tooltips) from CDN.
		// See: https://atomiks.github.io/tippyjs/v6/getting-started/.
		wp_enqueue_script( '@popperjs/core@2', 'https://unpkg.com/@popperjs/core@2', array(), '2', true );
		wp_enqueue_script( 'tippy.js@6', 'https://unpkg.com/tippy.js@6', array(), '6', true );
		wp_enqueue_style( 'tippy.js@6/themes/light-border', 'https://unpkg.com/tippy.js@6/themes/light-border.css', array(), '6', 'all' );

		wp_enqueue_style( 'p22d/dashboard-widget', plugins_url( 'styles/dashboard-widget.css', plugin_root() ), array(), plugin_version(), 'all' );

		wp_enqueue_script( 'p22d/dashboard-widget', plugins_url( 'scripts/dashboard-widget.js', plugin_root() ), array( 'jquery' ), plugin_version(), true );
		wp_localize_script(
			'p22d/dashboard-widget',
			'Data',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'p22d' ),
			)
		);
	}


	/**
	 * Add dashboard widget.
	 *
	 * Action: wp_dashboard_setup
	 */
	public function add_dashboard_widget() {
		// Bail if user doesn't have permission.
		if ( ! current_user_can( 'list_users' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'p22d_dashboard_widget', // Widget slug.
			esc_html__( 'H5P Results', 'p22d' ), // Title.
			array( $this, 'render' ) // Function that renders the widget.
		);

		// Move widget to top of right side (only for users that haven't already moved a widget).
		global $wp_meta_boxes;
		$widget = $wp_meta_boxes['dashboard']['normal']['core']['p22d_dashboard_widget'];
		unset( $wp_meta_boxes['dashboard']['normal']['core']['p22d_dashboard_widget'] );
		$wp_meta_boxes['dashboard']['side']['high'] = array_merge(
			array( 'p22d_dashboard_widget' => $widget ),
			$wp_meta_boxes['dashboard']['side']['high']
		);
	}


	/**
	 * Render dashboard widget (callback).
	 */
	public function render() {
		?>
		<div class="filters">
			<select name="role" class="option">
				<option value=""><?php esc_html_e( 'Filter role to...', 'p22d' ); ?></option>
				<?php wp_dropdown_roles( $this->get_option('role') ); ?>
			</select>
			<select name="filter" class="option">
				<option value=""><?php esc_html_e( 'Filter users to...', 'p22d' ); ?></option>
				<?php foreach ( $this->get_filters() as $value => $data ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $this->get_option('filter') ); ?>><?php echo esc_html( $data['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="per_page" class="option">
				<?php foreach ( array( 10, 25, 50, 100 ) as $value ) : ?>
					<option value="<?php echo $value; ?>"<?php selected( $value, $this->get_option( 'per_page' ) ); ?>><?php echo $value; ?> per page</option>
				<?php endforeach; ?>
			</select>
			</p>
		</div>
		<?php
		$this->render_users();
	}


	public function render_users( $page = 1, $search = '' ) {
		// Build WP_User_Query args.
		$per_page = $this->get_option( 'per_page' );
		$args     = array(
			'number'  => $per_page,
			'paged'   => $page,
			'orderby' => 'login',
			'order'   => 'ASC',
			'search'  => $search,
		);

		// Add any role filters.
		$role = $this->get_option( 'role' );
		if ( $role ) {
			$args['role'] = $role;
		}

		// Add any time filters (when user registered or last logged in).
		$filter  = $this->get_option( 'filter' );
		$filters = $this->get_filters();
		if ( ! empty( $filters[ $filter ] ) ) {
			if ( 'registered' === $filters[ $filter ]['type'] ) {
				global $wpdb;
				$users_too_old = $wpdb->get_col( $wpdb->prepare(
					"SELECT ID FROM $wpdb->users WHERE user_registered < FROM_UNIXTIME(%d)",
					$filters[ $filter ]['time']
				) );
				$args['exclude'] = $users_too_old;
			} elseif ( 'signed_in' === $filters[ $filter ]['type'] ) {
				$args['meta_query'] = array(
					array(
            'key'     => 'pressbooks_h5p_dashboard_last_login',
            'value'   => $filters[ $filter ]['time'],
            'type'    => 'UNSIGNED',
            'compare' => '>=',
					),
				);
			}
		}

		// Get matching users.
		$users = new \WP_User_Query( $args );

		// Get H5P data.
		$data    = Data::get_instance();
		$h5p_ids = $data->get_chapters_with_h5p();
		$num_h5p = array_sum( array_map( function ( $h5p_ids_in_chapter ) {
			return count( $h5p_ids_in_chapter );
		}, $h5p_ids ) );

		?>
		<div class="users">
			<div class="heading">
				<p>Showing <strong><?php echo empty( $role ) ? 'all user' : $role; ?>s</strong><?php if ( $filter = $this->get_option( 'filter' ) ) : ?>	who <strong><?php echo strtolower( $this->get_filters()[ $filter ]['label'] ); ?></strong><?php endif; ?>:</p>
			</div>

			<?php $this->render_pager( $page, $per_page, $users->total_users, 'top', $search ); ?>
			<table class="wp-list-table widefat fixed striped" cellspacing="0">
				<thead>
					<tr>
						<th class="manage-column column-username" scope="col">User</th>
						<th class="manage-column column-results num" scope="col">Results</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $users->results as $user ) : ?>
						<?php $results = $data->get_h5p_results_by_user_id( $user->ID ); ?>
						<tr>
							<td class="column-username">
								<?php echo get_avatar( $user->ID, 32 ); ?>
								<?php echo esc_html( $user->user_nicename ); ?><br>
								<?php echo esc_html( $user->user_email ); ?>
							</td>
							<td class="column-results num">
								<button class="button-primary" data-tippy-content=""><?php echo esc_html( count( $results ) . ' / ' . $num_h5p ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php $this->render_pager( $page, $per_page, $users->total_users, 'bottom', $search ); ?>
		</div>

		<?php
	}


	/**
	 * Renders the html elements for the pager above and below the table.
	 *
	 * @param  integer $current_page Which page we are currently viewing.
	 * @param  integer $per_page     How many users to show per page.
	 * @param  integer $total        Total count of users in list.
	 * @param  string  $which        Where to render the pager ('top' or 'bottom').
	 * @return void
	 */
	public function render_pager( $current_page = 1, $per_page = 10, $total = 0, $which = 'top', $search = '' ) {
		$total_pages = ceil( $total / $per_page );
		if ( $total_pages < 1 ) {
			$total_pages = 1;
		}

		/* TRANSLATORS: %s: number of users */
		$output = ' <span class="displaying-num">' . sprintf( _n( '%s user', '%s users', $total, 'p22d' ), number_format_i18n( $total ) ) . '</span>';

		$disable_first = $current_page <= 1;
		$disable_prev  = $current_page <= 1;
		$disable_next  = $current_page >= $total_pages;
		$disable_last  = $current_page >= $total_pages;

		$current_url = '';
		if ( isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ) {
			$current_url = set_url_scheme( esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			$current_url = remove_query_arg( wp_removable_query_args(), $current_url );
		}

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		if ( $disable_first ) {
			$page_links[] = '<span class="button disabled first-page tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='button first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="button disabled prev-page tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='button prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', max( 1, $current_page - 1 ), $current_url ) ),
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = '<span class="current-page-text">' . $current_page . '</span>';
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="p22d-table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf(
				"%s<input class='current-page' id='p22d-current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
				$current_page,
				strlen( $total_pages )
			);
		}
		/* TRANSLATORS: %s: number of pages */
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		/* TRANSLATORS: 1: number of current page 2: number of total pages */
		$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="button disabled next-page tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='button next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', min( $total_pages, $current_page + 1 ), $current_url ) ),
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="button disabled last-page tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='button last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				__( 'Last page' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		$output                .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		$search_form = array();
		if ( 'top' === $which ) {
			$search_form[] = '<p class="search-box">';
			$search_form[] = '<label class="screen-reader-text" for="p22d-user-search-input">' . __( 'Search Users', 'p22d' ) . '</label>';
			$search_form[] = '<input type="search" id="p22d-user-search-input" name="search" value="' . $search . '">';
			$search_form[] = '<input type="button" id="p22d-search-submit" class="button" value="' . __( 'Search', 'p22d' ) . '">';
			$search_form[] = '</p>';
		}
		$search_form = join( "\n", $search_form );

		$output = "<div class='tablenav-pages'>$output</div>";
		?>
		<div class="tablenav">
			<?php echo $output; ?>
			<?php echo $search_form; ?>
		</div>
		<?php
	}


	/**
	 * Updates the user's filter choices and re-render the dashboard widget.
	 *
	 * Hook: wp_ajax_p22d_dashboard_widget_refresh
	 */
	public function update() {
		// Nonce check.
		if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), 'p22d' ) ) {
			die( '' );
		}

		// Permission check.
		if ( ! current_user_can( 'list_users' ) ) {
			die( '' );
		}

		// Build JSON response.
		$response = [
			'success' => false,
			'message' => '',
			'html'    => '',
		];

		$options = $this->get_options();
		$changed = false;

		// Update user's role selection if it's set.
		if ( isset( $_REQUEST['filters']['role'] ) ) {
			$options['role'] = $_REQUEST['filters']['role'];
			$changed = true;
		}

		// Update user's filter selection if it's set.
		if ( isset( $_REQUEST['filters']['filter'] ) ) {
			$options['filter'] = $_REQUEST['filters']['filter'];
			$changed = true;
		}

		// Update user's users-per-page selection if it's set.
		if ( isset( $_REQUEST['filters']['per_page'] ) ) {
			$options['per_page'] = $_REQUEST['filters']['per_page'];
			$changed = true;
		}

		// Update user meta if filters have changed.
		if ( $changed ) {
			$this->update_options( $options );
		}

		// Get page if it's set (default to 1).
		$page = 1;
		if ( isset( $_REQUEST['filters']['page'] ) ) {
			$page = intval( $_REQUEST['filters']['page'] );
		}

		// Get search term if it's set.
		$search = '';
		if ( isset( $_REQUEST['filters']['search'] ) ) {
			$search = sanitize_text_field( $_REQUEST['filters']['search'] );
		}

		// Start output buffering so we can save the output to a string.
		ob_start();

		$this->render_users( $page, $search );

		// Render user list.
		$response['html']    = ob_get_clean();
		$response['success'] = true;
		$response['message'] = 'Rendered user list.';

		// Return results to client.
		wp_send_json($response);
		exit;
	}

}
