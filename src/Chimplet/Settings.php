<?php

namespace Locomotive\Chimplet;

use Locomotive\Singleton;

/**
 * File: Chimplet Settings Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Settings
 *
 * @version 2015-02-07
 * @since   0.0.0 (2015-02-07)
 */

class Settings extends AdminPage
{
	use Singleton;

	/**
	 * Constructor
	 *
	 * @version 2015-02-07
	 * @since   0.0.0 (2015-02-07)
	 * @access  public
	 * @param   WP  $facade  {@see WordPress\Facade::__construct}
	 */

	public function __construct( WP $facade = null )
	{
		$this->view['title'] = __('Settings', 'chimplet');
		$this->view['slug']  = 'chimplet-settings';

		parent::__construct( $facade );
	}

	/**
	 * Add pages to the WordPress administration menu
	 *
	 * @used-by Action: admin_menu
	 * @version 2015-02-07
	 * @since   0.0.0 (2015-02-07)
	 */

	public function append_to_menu()
	{
		$this->wp->add_submenu_page( 'chimplet-overview', $this->view['title'], $this->view['title'], 'manage_options', $this->view['slug'], [ $this, 'render_page' ] );
	}

	/**
	 * Display the settings
	 *
	 * @used-by Function: add_menu_page
	 * @version 2015-02-07
	 * @since   0.0.0 (2015-02-07)
	 */

	public function render_page()
	{
		// $this->view['mailchimp_key'] = $this->get_setting('mailchimp-key');

		$this->render_view( 'options-settings', $this->view );
	}

}
