<?php

namespace Locomotive\Chimplet;

use Locomotive\Singleton;
use Locomotive\WordPress\WP;
use Locomotive\WordPress\Facade;

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
	use Singleton, Facade;

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
	 * @param   WP  $facade  Allows inserting a different facade object for testing.
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

	function append_to_menu()
	{
		// var_dump( __CLASS__ . '::' . __FUNCTION__ );

		$this->view['title'] = __('Overview', 'chimplet');
		$this->view['slug']  = 'chimplet-overview';

		$mailchimp_key = $this->get_setting('mailchimp-key');
		// $version_info  = $this->get_version_info();
		$badge = '';

		if ( empty( $mailchimp_key ) /* || isset( $version_info['is_valid_key'] ) */ ) {
			$badge = sprintf(
				__('You need to register a %s to use %s.', 'chimplet'),
				__('MailChimp API key', 'chimplet'),
				__('Chimplet', 'chimplet')
			);

			$badge = ' ' . '<span class="update-plugins" title="' . $badge . '"><span class="update-count">&#9679;</span></span>';
		}

		$this->wp->add_menu_page( $this->view['title'], $this->get_setting('name') . $badge, 'manage_options', $this->view['slug'], [ $this, 'render_page' ], 'dashicons-email-alt', 81 );
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
