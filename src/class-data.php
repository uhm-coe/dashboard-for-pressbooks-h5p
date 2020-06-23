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
 * Contains features added in the WordPress Dashboard.
 */
class Data extends Static_Instance {
	/**
	 * Cached value of get_chapters_with_h5p().
	 *
	 * @var array
	 */
	private $chapters_with_h5p;

	/**
	 * Cached H5P results by user id.
	 *
	 * @var array
	 */
	private $h5p_results;

	/**
	 * Cached value of pb_get_book_structure() from Pressbooks.
	 *
	 * @var  array
	 */
	private $book_structure;

	/**
	 * Returns H5P ids grouped by the post_id they are embedded on.
	 *
	 * @return array Post ID keys with an array of H5P ids under each.
	 */
	public function get_chapters_with_h5p() {
		// Return empty array if H5P is not available.
		if ( ! class_exists( 'H5P_Plugin' ) ) {
			return array();
		}

		// Return cached value if it exists.
		if ( isset( $this->chapters_with_h5p ) ) {
			return $this->chapters_with_h5p;
		}

		global $wpdb;
		$H5P_Plugin = \H5P_Plugin::get_instance(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$h5p_by_chapter = array();

		$chapters_with_h5p = get_posts(
			array(
				'post_type'      => 'chapter',
				'posts_per_page' => -1,
				's'              => '[h5p',
			)
		);

		foreach ( $chapters_with_h5p as $chapter ) {
			// Get H5P IDs in each chapter (e.g., [h5p id="123"]).
			preg_match_all( '/\[h5p id="([0-9]*)"/', $chapter->post_content, $matches );
			foreach ( $matches[1] as $h5p_id ) {
				$h5p_id_key = 'h5p-id-' . $h5p_id;

				// Get H5P content details.
				$h5p = $H5P_Plugin->get_content( $h5p_id ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				if ( ! empty( $h5p['title'] ) && ! empty( $h5p['library']['name'] ) ) {
					if ( ! isset( $h5p_by_chapter[ $chapter->ID ] ) ) {
						$h5p_by_chapter[ $chapter->ID ] = array();
					}
					$h5p_by_chapter[ $chapter->ID ][ $h5p_id_key ] = array(
						'title'   => $h5p['title'],
						'library' => str_replace( 'H5P.', '', $h5p['library']['name'] ),
						'passing' => json_decode( $h5p['params'] ?? '{}' )->behaviour->passPercentage ?? 0,
					);
				}
			}

			// Get H5P slugs in each chapter (e.g., [h5p id="sample-page"]).
			// Note: this is if the H5P "Add Content Method" setting is set to
			// "Reference content by slug" and requires a separate lookup of the slug
			// to ID mapping.
			preg_match_all( '/\[h5p slug="([^"]*)"/', $chapter->post_content, $matches );
			foreach ( $matches[1] as $maybe_h5p_slug ) {
				// Look up H5P id from slug.
				$h5p_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->prepare(
						"SELECT id FROM {$wpdb->prefix}h5p_contents WHERE slug = %s",
						$maybe_h5p_slug
					)
				);
				if ( empty( $h5p_id ) ) {
					continue;
				}
				$h5p_id_key = 'h5p-id-' . $h5p_id;

				// Get H5P content details.
				$h5p = $H5P_Plugin->get_content( $h5p_id ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				if ( ! empty( $h5p['title'] ) && ! empty( $h5p['library']['name'] ) ) {
					if ( ! isset( $h5p_by_chapter[ $chapter->ID ] ) ) {
						$h5p_by_chapter[ $chapter->ID ] = array();
					}
					$h5p_by_chapter[ $chapter->ID ][ $h5p_id_key ] = array(
						'title'   => $h5p['title'],
						'library' => str_replace( 'H5P.', '', $h5p['library']['name'] ),
						'passing' => json_decode( $h5p['params'] ?? '{}' )->behaviour->passPercentage ?? 0,
					);
				}
			}
		}

		// Save value to cache.
		$this->chapters_with_h5p = $h5p_by_chapter;

		return $this->chapters_with_h5p;
	}

	/**
	 * Get the current user's results on all H5P content they've attempted.
	 *
	 * @return array Array of current user's H5P results indexed by h5p_id.
	 */
	public function get_my_h5p_results() {

		// Return nothing for anonymous users.
		if ( ! is_user_logged_in() ) {
			return array();
		}

		return $this->get_h5p_results_by_user_id( get_current_user_id() );
	}

	/**
	 * Get a user's results on all H5P content they've attempted (only highest
	 * score is recorded for content with multiple attempts), with the h5p id as
	 * the array index.
	 *
	 * @param  int $user_id User ID to get results for.
	 *
	 * @return array        Array h5p_id => H5P results.
	 */
	public function get_h5p_results_by_user_id( $user_id = 0 ) {
		$h5p_results_by_chapter = array();
		foreach ( $this->get_raw_h5p_results_by_user_id( $user_id ) as $result ) {
			if (
				! isset( $h5p_results_by_chapter[ $result->content_id ] ) ||
				$result->score > $h5p_results_by_chapter[ $result->content_id ]['score']
			) {
				$h5p_results_by_chapter[ $result->content_id ] = array(
					'title'     => $result->content_title,
					'score'     => $result->score,
					'max_score' => $result->max_score,
					'finished'  => $result->finished,
					'duration'  => $result->finished - $result->opened,
				);
			}
		}

		return $h5p_results_by_chapter;
	}

	/**
	 * Get the current user's results on all H5P content they've attempted.
	 *
	 * @return array Current user's H5P results.
	 */
	public function get_my_raw_h5p_results() {

		// Return nothing for anonymous users.
		if ( ! is_user_logged_in() ) {
			return array();
		}

		return $this->get_raw_h5p_results_by_user_id( get_current_user_id() );
	}

	/**
	 * Get a user's results on all H5P content they've attempted.
	 *
	 * @param  int $user_id User ID to get results for.
	 *
	 * @return array        User's H5P results.
	 */
	public function get_raw_h5p_results_by_user_id( $user_id ) {
		// Fail gracefully if H5P isn't active.
		if ( ! class_exists( 'H5P_Plugin_Admin' ) ) {
			return array();
		}

		// Fail if current user doesn't have permission to fetch other's results.
		if ( get_current_user_id() !== $user_id && ! current_user_can( 'list_users' ) ) {
			return array();
		}

		$H5P_Plugin_Admin = \H5P_Plugin_Admin::get_instance(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		// Return cached value if it exists.
		if ( isset( $this->h5p_results[ $user_id ] ) ) {
			return $this->h5p_results[ $user_id ];
		}

		/**
		 * H5P get_results returns an array of:
		 *   stdClass Object(
		 *     [id]            => 123
		 *     [content_id]    => 123
		 *     [content_title] => Title
		 *     [score]         => 1
		 *     [max_score]     => 1
		 *     [opened]        => 1234567890 (unit timestamp)
		 *     [finished]      => 1234567890 (unix timestamp)
		 *     [time]          => 0
		 *   )
		 */
		$my_h5p_results = array_filter(
			$H5P_Plugin_Admin->get_results( null, $user_id, 0, PHP_INT_MAX ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			function ( $result ) {
				// Skip invalid results.
				return isset(
					$result->id,
					$result->content_id,
					$result->content_title,
					$result->score,
					$result->max_score,
					$result->opened,
					$result->finished
				);
			}
		);

		// Save value to cache.
		$this->h5p_results[ $user_id ] = $my_h5p_results;

		return $my_h5p_results;
	}


	/**
	 * Wrapper for Pressbooks' pb_get_book_structure(). See
	 * https://docs.pressbooks.org/book-themes/.
	 *
	 * @return array Array of sections, parts, and chapters in sorted order.
	 */
	public function get_book_structure() {
		// Return cached value if it exists.
		if ( isset( $this->book_structure ) ) {
			return $this->book_structure;
		}

		$book_structure = array();
		if ( function_exists( 'pb_get_book_structure' ) ) {
			$book_structure = pb_get_book_structure();
		}

		// Save value to cache.
		$this->book_structure = $book_structure;

		return $book_structure;
	}
}
