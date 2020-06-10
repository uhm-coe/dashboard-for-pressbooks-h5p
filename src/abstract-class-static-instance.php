<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Dashboard for Pressbook and H5P
 *
 * @license  GPL-3.0+
 * @link     https://github.com/uhm-coe/dashboard-for-pressbooks-h5p
 * @package  dashboard-for-pressbooks-h5p
 */

namespace Dashboard_For_Pressbooks_H5P;

/**
 * Base class that all other classes extend (provides static accessor variable).
 */
abstract class Static_Instance {
	/**
	 * Plugin instance.
	 *
	 * @var object Plugin instance.
	 */
	protected static $instance = null;


	/**
	 * Access this plugin's working instance.
	 *
	 * @return object Object of this class.
	 */
	public static function get_instance() {
		return null === static::$instance ? new static() : static::$instance;
	}


	/**
	 * Constructor intentionally left empty and public.
	 */
	public function __construct() {
	}

}
