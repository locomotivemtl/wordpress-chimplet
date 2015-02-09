<?php

namespace Locomotive\Chimplet;

use Locomotive\Singleton;

/**
 * File: Chimplet Settings Page Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Settings Page
 *
 * @version 2015-02-09
 * @since   0.0.0 (2015-02-07)
 */

class SettingsPage extends AdminPage
{
	use Singleton;

	/**
	 * Constructor
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-07)
	 * @access  public
	 * @param   WP  $facade  {@see WordPress\Facade::__construct}
	 */

	public function __construct( WP $facade = null )
	{
		$mailchimp_key = $this->get_option('mailchimp-api-key');

		$this->nonce = ( empty( $mailchimp_key ) ? 'activate_mailchimp_api_key' : 'deactivate_mailchimp_api_key' );

		$this->view['document_title'] = __('Chimplet Settings', 'chimplet');

		$this->view['page_title'] = __('Settings', 'chimplet');
		$this->view['menu_title'] = $this->view['page_title'];
		$this->view['menu_slug']  = 'chimplet-settings';

		parent::__construct( $facade );
	}

	/**
	 * Add pages to the WordPress administration menu
	 *
	 * @used-by Action: admin_menu
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-07)
	 */

	public function append_to_menu()
	{
		$this->hook = $this->wp->add_submenu_page( 'chimplet-overview', $this->view['document_title'], $this->view['menu_title'], 'manage_options', $this->view['menu_slug'], [ $this, 'render_page' ] );

		add_action( "load-{$this->hook}", [ $this, 'load_page' ] );
	}

	/**
	 * Display the settings page
	 *
	 * @used-by Function: add_menu_page
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-07)
	 */

	public function render_page()
	{
		// $this->view['mailchimp_key'] = $this->get_option('mailchimp-api-key');

		$this->render_view( 'options-settings', $this->view );
	}

}
