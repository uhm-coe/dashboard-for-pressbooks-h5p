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
 * Create the plugin dashboard widget.
 */
class Dashboard_Widget extends Static_Instance {

	/**
	 * Dashboard widget user options (e.g., filters, users per page).
	 *
	 * @var array
	 */
	private $options;


	/**
	 * Get all user options.
	 *
	 * @return array All user options (defaults if none set yet).
	 */
	public function get_options() {
		if ( ! isset( $this->options ) ) {
			$this->options = $this->sanitized_defaults( get_user_option( 'dashboard_for_pressbooks_h5p', get_current_user_id() ) );
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
	 * @param  array $options User options.
	 */
	public function update_options( $options = array() ) {
		$this->options = $this->sanitized_defaults( $options );
		update_user_option( get_current_user_id(), 'dashboard_for_pressbooks_h5p', $this->options );
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
		if ( empty( $options['role'] ) || ! in_array( $options['role'], array_keys( get_editable_roles() ), true ) ) {
			$options['role'] = '';
		}

		// Usermeta to filter to (e.g., last logged in time). Default: all users.
		if ( empty( $options['filter'] ) || ! in_array( $options['filter'], array_keys( $this->get_filters() ), true ) ) {
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
		$first_day_of_week  = strtotime( '-' . gmdate( 'w' ) . ' days ' );
		$first_day_of_month = strtotime( '-' . ( gmdate( 'j' ) - 1 ) . ' days ' );
		$first_day_of_year  = strtotime( '-' . gmdate( 'z' ) . ' days ' );
		$last_jan_1         = strtotime( 'first day of January' );
		$last_may_1         = strtotime( 'first day of May' );
		$last_aug_1         = strtotime( 'first day of August' );
		if ( $last_jan_1 > $now ) {
			$last_jan_1 = strtotime( 'first day of January ' . ( gmdate( 'Y' ) - 1 ) );
		}
		if ( $last_may_1 > $now ) {
			$last_may_1 = strtotime( 'first day of May ' . ( gmdate( 'Y' ) - 1 ) );
		}
		if ( $last_aug_1 > $now ) {
			$last_aug_1 = strtotime( 'first day of August ' . ( gmdate( 'Y' ) - 1 ) );
		}

		return array(
			'registered_this_week'  => array(
				'label' => __( 'Registered this week', 'd4ph' ),
				'time'  => $first_day_of_week,
				'type'  => 'registered',
			),
			'registered_this_month' => array(
				'label' => __( 'Registered this month', 'd4ph' ),
				'time'  => $first_day_of_month,
				'type'  => 'registered',
			),
			'registered_this_year'  => array(
				'label' => __( 'Registered this year', 'd4ph' ),
				'time'  => $first_day_of_year,
				'type'  => 'registered',
			),
			'registered_since_jan'  => array(
				'label' => __( 'Registered since January', 'd4ph' ),
				'time'  => $last_jan_1,
				'type'  => 'registered',
			),
			'registered_since_may'  => array(
				'label' => __( 'Registered since May', 'd4ph' ),
				'time'  => $last_may_1,
				'type'  => 'registered',
			),
			'registered_since_aug'  => array(
				'label' => __( 'Registered since August', 'd4ph' ),
				'time'  => $last_aug_1,
				'type'  => 'registered',
			),
			'signed_in_this_week'   => array(
				'label' => __( 'Signed in this week', 'd4ph' ),
				'time'  => $first_day_of_week,
				'type'  => 'signed_in',
			),
			'signed_in_this_month'  => array(
				'label' => __( 'Signed in this month', 'd4ph' ),
				'time'  => $first_day_of_month,
				'type'  => 'signed_in',
			),
			'signed_in_this_year'   => array(
				'label' => __( 'Signed in this year', 'd4ph' ),
				'time'  => $first_day_of_year,
				'type'  => 'signed_in',
			),
			'signed_in_since_jan'   => array(
				'label' => __( 'Signed in since January', 'd4ph' ),
				'time'  => $last_jan_1,
				'type'  => 'signed_in',
			),
			'signed_in_since_may'   => array(
				'label' => __( 'Signed in since May', 'd4ph' ),
				'time'  => $last_may_1,
				'type'  => 'signed_in',
			),
			'signed_in_since_aug'   => array(
				'label' => __( 'Signed in since August', 'd4ph' ),
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

		// Load tippy.js (tooltips).
		// See: https://atomiks.github.io/tippyjs/v6/getting-started/.
		wp_enqueue_script( '@popperjs/core@2.4.2', plugins_url( 'vendor/popperjs/core/2.4.2/dist/umd/popper.min.js', plugin_root() ), array(), '2.4.2', true );
		wp_enqueue_script( 'tippy.js@6.2.3', plugins_url( 'vendor/tippy.js/6.2.3/dist/tippy-bundle.umd.min.js', plugin_root() ), array(), '6.2.3', true );
		wp_enqueue_style( 'tippy.js@6.2.3/themes/light-border', plugins_url( 'vendor/tippy.js/6.2.3/dist/themes/light-border.css', plugin_root() ), array(), '6.2.3', 'all' );

		wp_enqueue_style( 'd4ph/dashboard-widget', plugins_url( 'styles/dashboard-widget.css', plugin_root() ), array(), plugin_version(), 'all' );

		wp_enqueue_script( 'd4ph/dashboard-widget', plugins_url( 'scripts/dashboard-widget.js', plugin_root() ), array( 'jquery' ), plugin_version(), true );
		wp_localize_script(
			'd4ph/dashboard-widget',
			'Data',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'd4ph' ),
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

		// Bail if plugin dependencies are not installed and activated.
		if ( ! is_plugin_active( 'h5p/h5p.php' ) || ! is_plugin_active( 'pressbooks/pressbooks.php' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'd4ph_dashboard_widget', // Widget slug.
			esc_html__( 'Results for H5P', 'd4ph' ), // Title.
			array( $this, 'render' ) // Function that renders the widget.
		);

		// Move widget to top of right side (only for users that haven't already
		// moved a widget). See https://developer.wordpress.org/apis/handbook/dashboard-widgets/#forcing-your-widget-to-the-top
		// for more details.
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['d4ph_dashboard_widget'] ) ) {
			$widget = $wp_meta_boxes['dashboard']['normal']['core']['d4ph_dashboard_widget'];
			unset( $wp_meta_boxes['dashboard']['normal']['core']['d4ph_dashboard_widget'] );
			if ( ! isset( $wp_meta_boxes['dashboard']['side'] ) ) {
				$wp_meta_boxes['dashboard']['side'] = array();
			}
			if ( ! isset( $wp_meta_boxes['dashboard']['side']['high'] ) ) {
				$wp_meta_boxes['dashboard']['side']['high'] = array();
			}
			$wp_meta_boxes['dashboard']['side']['high'] = array_merge(
				array( 'd4ph_dashboard_widget' => $widget ),
				$wp_meta_boxes['dashboard']['side']['high']
			);
		}
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	}


	/**
	 * Render dashboard widget (callback).
	 */
	public function render() {
		?>
		<div class="filters">
			<select name="role" class="option">
				<option value=""><?php esc_html_e( 'Filter role to...', 'd4ph' ); ?></option>
				<?php wp_dropdown_roles( $this->get_option( 'role' ) ); ?>
			</select>
			<select name="filter" class="option">
				<option value=""><?php esc_html_e( 'Filter users to...', 'd4ph' ); ?></option>
				<?php foreach ( $this->get_filters() as $value => $data ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $this->get_option( 'filter' ) ); ?>><?php echo esc_html( $data['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="per_page" class="option">
				<?php foreach ( array( 10, 25, 50, 100 ) as $value ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $this->get_option( 'per_page' ) ); ?>><?php echo esc_html( $value ); ?> per page</option>
				<?php endforeach; ?>
			</select>
			</p>
		</div>
		<?php
		$this->render_users();
	}


	/**
	 * Render the heading, pager, and user table for the dashboard widget.
	 *
	 * @param  int    $page   Page of results to display.
	 * @param  string $search Optional search term to pass to WP_User_Query.
	 */
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
			global $wpdb;
			if ( 'registered' === $filters[ $filter ]['type'] ) {
				$users_too_old = wp_cache_get( 'users_too_old', 'd4ph' );
				if ( false === $users_too_old ) {
					$users_too_old = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
						$wpdb->prepare(
							"SELECT ID FROM $wpdb->users WHERE user_registered < FROM_UNIXTIME(%d)",
							$filters[ $filter ]['time']
						)
					);
					wp_cache_set( 'users_too_old', $users_too_old, 'd4ph', 24 * HOUR_IN_SECONDS );
				}
				$args['exclude'] = $users_too_old;
			} elseif ( 'signed_in' === $filters[ $filter ]['type'] ) {
				$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => $wpdb->get_blog_prefix() . 'dashboard_for_pressbooks_h5p_last_login',
						'value'   => $filters[ $filter ]['time'],
						'type'    => 'UNSIGNED',
						'compare' => '>=',
					),
				);
			}
		}

		// Get matching users.
		$users = new \WP_User_Query( $args );

		// Get total count of H5P content.
		$data      = Data::get_instance();
		$total_h5p = array_sum(
			array_map(
				function ( $h5p_ids_in_chapter ) {
					return count( $h5p_ids_in_chapter );
				},
				$data->get_chapters_with_h5p()
			)
		);

		// Create heading sentence.
		$heading = 'Showing <strong>' . ( empty( $role ) ? 'all' : $role ) . '</strong> users';
		if ( ! empty( $filter ) ) {
			$heading .= ' who <strong>' . lcfirst( $this->get_filters()[ $filter ]['label'] ) . '</strong>';
		}
		$heading .= ':';

		?>
		<div class="users">
			<div class="heading">
				<p><?php echo wp_kses_post( $heading ); ?></p>
			</div>

			<?php $this->render_pager( $page, $per_page, $users->total_users, 'top', $search ); ?>
			<table class="wp-list-table widefat striped" cellspacing="0">
				<thead>
					<tr>
						<th class="manage-column column-username" scope="col">User</th>
						<th class="manage-column column-results num" scope="col">Results</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $users->results as $user ) : ?>
						<?php
							$results_by_h5p_id = array_filter(
								$data->get_h5p_results_by_user_id( $user->ID ),
								function ( $result ) {
									return $result['score'] > 0;
								}
							);
						?>
						<tr>
							<td class="column-username">
								<?php echo get_avatar( $user->ID, 32 ); ?>
								<?php echo esc_html( $user->user_nicename ); ?><br>
								<?php echo esc_html( $user->user_email ); ?>
							</td>
							<td class="column-results num">
								<button class="button-primary" data-tippy-content="<?php echo esc_attr( htmlentities( $this->render_user_tooltip( $user, $results_by_h5p_id ) ) ); ?>"><?php echo esc_html( count( $results_by_h5p_id ) . ' / ' . $total_h5p ); ?></button>
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
	 * Generate the markup for the tippy tooltip for each user in the widget.
	 * Note: escape with htmlentities() to allow double quotes in the tooltip
	 * content.
	 *
	 * @param  WP_User $user               WP_User object for the user (passed by reference).
	 * @param  array   $results_by_h5p_id  H5P results for the user (passed by reference).
	 *
	 * @return string            HTML for the tooltip.
	 */
	public function render_user_tooltip( &$user, &$results_by_h5p_id ) {
		$data               = Data::get_instance();
		$h5p_ids_by_chapter = $data->get_chapters_with_h5p();

		$chapter_data = array();
		foreach ( $data->get_book_structure() as $section => $parts ) {
			if ( '__order' === $section ) {
				// Skip __order section (long array indicating book ordering).
				continue;
			}
			foreach ( $parts as $part ) {
				if ( ! empty( $part['chapters'] ) ) {
					foreach ( $part['chapters'] as $chapter ) {
						if ( ! empty( $h5p_ids_by_chapter[ $chapter['ID'] ] ) ) {
							$h5p_ids_in_chapter = array_keys( $h5p_ids_by_chapter[ $chapter['ID'] ] );
							$results_in_chapter = array_filter(
								$results_by_h5p_id,
								function ( $h5p_id ) use ( $h5p_ids_in_chapter ) {
									$h5p_id_key = 'h5p-id-' . $h5p_id;
									return in_array( $h5p_id_key, $h5p_ids_in_chapter, true );
								},
								ARRAY_FILTER_USE_KEY
							);

							$chapter_data[ $chapter['ID'] ] = array(
								'parent'     => $part['post_title'] ?? '—',
								'title'      => $chapter['post_title'] ?? '—',
								'results'    => $results_in_chapter,
								'h5p_ids'    => $h5p_ids_by_chapter[ $chapter['ID'] ],
								'h5p_passed' => count( $results_in_chapter ),
								'h5p_total'  => count( $h5p_ids_by_chapter[ $chapter['ID'] ] ),
							);
						}
					}
				}
			}
		}

		ob_start();
		?>
		<h1><?php echo esc_html( $user->user_nicename ); ?></h1>
		<table class="wp-list-table striped">
			<thead>
				<tr>
					<th><strong><?php esc_html_e( 'Part', 'd4ph' ); ?></strong></th>
					<th><?php esc_html_e( 'Chapter', 'd4ph' ); ?></th>
					<th><?php esc_html_e( 'Score', 'd4ph' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $chapter_data as $chapter_id => $data ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $data['parent'] ); ?></strong></td>
						<td><?php echo esc_html( $data['title'] ); ?></td>
						<td><button class="button-primary" data-tippy-content="<div class='dark-mode'><?php echo esc_attr( htmlentities( $this->render_chapter_tooltip( $data['results'], $data['h5p_ids'] ) ) ); ?></div>"><?php echo esc_html( $data['h5p_passed'] ); ?>/<?php echo esc_html( $data['h5p_total'] ); ?></button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php

		return ob_get_clean();
	}


	/**
	 * Render the markup for the user's results for each chapter.

	 * @param  array $results_in_chapter Array of user's H5P results (passed by reference).
	 * @param  array $h5p_ids_in_chapter Array of H5P Content in chapter (passed by reference).
	 *
	 * @return string            HTML for the tooltip.
	 */
	public function render_chapter_tooltip( &$results_in_chapter, &$h5p_ids_in_chapter ) {
		ob_start();
		?>
		<h1><?php esc_html_e( 'Results', 'd4ph' ); ?></h1>
		<table class='wp-list-table striped'>
			<thead>
				<tr>
					<th><strong><?php esc_html_e( 'H5P', 'd4ph' ); ?></strong></th>
					<th><?php esc_html_e( 'Score', 'd4ph' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $h5p_ids_in_chapter as $h5p_id_key => $data ) : ?>
				<?php $h5p_id = str_replace( 'h5p-id-', '', $h5p_id_key ); ?>
				<tr>
					<td><?php echo esc_html( $data['title'] ); ?></td>
					<td><strong><?php echo empty( $results_in_chapter[ $h5p_id ] ) ? '—' : esc_html( round( $results_in_chapter[ $h5p_id ]['score'] / $results_in_chapter[ $h5p_id ]['max_score'] * 100 ) ) . '%'; ?></strong></td>
				</tr>
			<?php endforeach; ?>
		</ol>
		<?php

		return ob_get_clean();
	}


	/**
	 * Renders the html elements for the pager above and below the table.
	 *
	 * @param  integer $current_page Which page we are currently viewing.
	 * @param  integer $per_page     How many users to show per page.
	 * @param  integer $total        Total count of users in list.
	 * @param  string  $which        Where to render the pager ('top' or 'bottom').
	 * @param  string  $search       An existing search string to render in the input.
	 *
	 * @return void
	 */
	public function render_pager( $current_page = 1, $per_page = 10, $total = 0, $which = 'top', $search = '' ) {
		$total_pages = ceil( $total / $per_page );
		if ( $total_pages < 1 ) {
			$total_pages = 1;
		}

		/* TRANSLATORS: %s: number of users */
		$output = ' <span class="displaying-num">' . sprintf( _n( '%s user', '%s users', $total, 'd4ph' ), number_format_i18n( $total ) ) . '</span>';

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
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="d4ph-table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf(
				"%s<input class='current-page' id='d4ph-current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
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
			$search_form[] = '<label class="screen-reader-text" for="d4ph-user-search-input">' . __( 'Search Users', 'd4ph' ) . '</label>';
			$search_form[] = '<input type="search" id="d4ph-user-search-input" name="search" value="' . $search . '">';
			$search_form[] = '<input type="button" id="d4ph-search-submit" class="button" value="' . __( 'Search', 'd4ph' ) . '">';
			$search_form[] = '</p>';
		}
		$search_form = join( "\n", $search_form );

		$output = "<div class='tablenav-pages'>$output</div>";
		?>
		<div class="tablenav">
			<?php echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo $search_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
	}


	/**
	 * Updates the user's filter choices and re-render the dashboard widget.
	 *
	 * Hook: wp_ajax_d4ph_dashboard_widget_refresh
	 */
	public function update() {
		// Nonce check.
		if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), 'd4ph' ) ) {
			die( '' );
		}

		// Permission check.
		if ( ! current_user_can( 'list_users' ) ) {
			die( '' );
		}

		// Build JSON response.
		$response = array(
			'success' => false,
			'message' => '',
			'html'    => '',
		);

		$options = $this->get_options();
		$changed = false;

		// Update user's role selection if it's set.
		if ( isset( $_REQUEST['filters']['role'] ) ) {
			$options['role'] = sanitize_text_field( wp_unslash( $_REQUEST['filters']['role'] ) );
			$changed         = true;
		}

		// Update user's filter selection if it's set.
		if ( isset( $_REQUEST['filters']['filter'] ) ) {
			$options['filter'] = sanitize_text_field( wp_unslash( $_REQUEST['filters']['filter'] ) );
			$changed           = true;
		}

		// Update user's users-per-page selection if it's set.
		if ( isset( $_REQUEST['filters']['per_page'] ) ) {
			$options['per_page'] = intval( wp_unslash( $_REQUEST['filters']['per_page'] ) );
			$changed             = true;
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
			$search = sanitize_text_field( wp_unslash( $_REQUEST['filters']['search'] ) );
		}

		// Start output buffering so we can save the output to a string.
		ob_start();

		$this->render_users( $page, $search );

		// Render user list.
		$response['html']    = ob_get_clean();
		$response['success'] = true;
		$response['message'] = 'Rendered user list.';

		// Return results to client.
		wp_send_json( $response );
		exit;
	}

}
