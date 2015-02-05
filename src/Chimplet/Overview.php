<?php

namespace Locomotive\Chimplet;

/**
 * File: Administration Overview Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Administration Overview
 *
 * @version 2015-02-05
 * @since   0.0.0 (2015-02-05)
 */

class Overview extends Base
{
	protected $view = [];

	/**
	 * Constructor
	 *
	 * Prepares all the necessary actions, filters, and functions
	 * for the plugin to operate.
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   Application  $app
	 */

	function __construct()
	{
		add_action( 'admin_menu',            [ $this, 'append_to_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Add pages to the WordPress administration menu
	 *
	 * @used-by Action: admin_menu
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 */

	function append_to_menu()
	{
		// var_dump( __CLASS__ . '::' . __FUNCTION__ );

		$this->view['title'] = __('Overview', 'chimplet');
		$this->view['slug']  = 'chimplet-overview';

		add_menu_page( $this->view['title'], $this->get_setting('name'), 'manage_options', $this->view['slug'], [ $this, 'render_page' ], 'dashicons-email-alt', 81 );
	}

	/**
	 * Enqueue assets
	 *
	 * @used-by Action: admin_enqueue_scripts
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 */

	function enqueue_assets()
	{
		// var_dump( __CLASS__ . '::' . __FUNCTION__ );

		// wp_enqueue_style( 'chimplet-global' );
	}

	/**
	 * Display the overview
	 *
	 * @used-by Function: add_menu_page
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 */

	function render_page()
	{
		$this->render_view( 'options-overview', $this->view );
	}

}
