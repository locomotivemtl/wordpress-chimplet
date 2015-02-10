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
	use AdminForm;

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
	 * Load Page
	 *
	 * @used-by Action: "load-{$page}"
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 */

	public function load_page()
	{
		$this->wp->add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		if ( $this->validate_submitted_values() ) {

			// $this->save

		}
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
		$this->wp->wp_enqueue_style('chimplet-global');
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
