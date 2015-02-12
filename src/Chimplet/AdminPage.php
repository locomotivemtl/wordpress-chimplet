<?php

namespace Locomotive\Chimplet;

use Locomotive\WordPress\WP;
use Locomotive\WordPress\Facade;

/**
 * File: Chimplet Administration Page Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Administration Page
 *
 * @version 2015-02-09
 * @since   0.0.0 (2015-02-07)
 */

class AdminPage extends Base
{
	use Facade;

	protected $view = [];
	protected $hook = '';

	/**
	 * Constructor
	 *
	 * Prepares all the necessary actions, filters, and functions
	 * for the plugin to operate.
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 * @access  public
	 * @param   WP  $facade  {@see WordPress\Facade::__construct}
	 */

	public function __construct( WP $facade = null )
	{
		$this->set_facade( $facade );

		if ( ! $this->wp->is_admin() ) {
			return;
		}

		$this->wp->add_action( 'admin_menu', [ $this, 'append_to_menu' ] );
		$this->wp->add_action( 'admin_init', [ $this, 'register_settings' ] );

		$this->wp->add_filter( 'pre_update_option_chimplet', [ $this, 'pre_update_option' ], 1, 2 );
	}

	/**
	 * Add pages to the WordPress administration menu
	 *
	 * @used-by Action: admin_menu
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 */

	public function append_to_menu()
	{
	}

	/**
	 * Register settings sections and fields
	 *
	 * @used-by Action: "admin_init"
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 */

	public function register_settings()
	{
	}

	/**
	 * Filter the Chimplet option before its value is (maybe) serialized and updated.
	 *
	 * Merges the old value with new value to preserve supplementary data
	 * (such as plugin meta data).
	 *
	 * @used-by Filter: "pre_update_option_$option"
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-10)
	 *
	 * @param mixed $value The new, unserialized option value.
	 * @param mixed $old_value The old option value.
	 *
	 * @return array|mixed
	 */

	public function pre_update_option( $value, $old_value )
	{
		$value = array_merge( $old_value, $value );

		return $value;
	}

	/**
	 * Load Page
	 *
	 * @used-by Action: "load-{$page}"
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 */

	public function load_page()
	{
		$this->wp->add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue assets
	 *
	 * @used-by Action: admin_enqueue_scripts
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 */

	public function enqueue_assets()
	{
		$this->wp->wp_enqueue_style( 'chimplet-global' );
	}

	/**
	 * Retrieve plugin menu slug
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-07)
	 *
	 * @param   string  $page_slug  Class name of requested menu slug
	 * @return  string
	 */

	public function get_menu_slug( $page_slug = null )
	{
		if ( empty( $page_slug ) ) {

			if ( isset( $this->view['menu_slug'] ) ) {
				return $this->view['menu_slug'];
			}
		}
		else {
			$namespaced_slug = __NAMESPACE__ . '\\' . $page_slug;

			if ( class_exists( $namespaced_slug ) ) {

				$page_object = $namespaced_slug::get_singleton();

				return $page_object->get_menu_slug();
			}
		}

		return '';
	}

}
