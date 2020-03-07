<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Pressbook H5P Dashboard
 *
 * @license  GPL-3.0+
 * @link     https://github.com/uhm-coe/pressbooks-h5p-dashboard
 * @package  pressbooks-h5p-dashboard
 */

namespace Pressbooks_H5P_Dashboard;

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
