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

	/**
	 * Constructor
	 *
	 * Prepares all the necessary actions, filters, and functions
	 * for the plugin to operate.
	 *
	 * @version 2015-02-05
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

		$this->wp->add_action( 'admin_menu',            [ $this, 'append_to_menu' ] );
		$this->wp->add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
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
	 * @return  string
	 */

	public function get_menu_slug()
	{
		if ( isset( $this->view['menu_slug'] ) ) {
			return $this->view['menu_slug'];
		}
	}

}
