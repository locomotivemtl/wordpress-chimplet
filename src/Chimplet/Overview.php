<?php

namespace Locomotive\Chimplet;

use Locomotive\Singleton;

/**
 * File: Chimplet Overview Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Overview
 *
 * @version 2015-02-09
 * @since   0.0.0 (2015-02-05)
 */

class Overview extends AdminPage
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
		$this->view['document_title'] = __('Chimplet Overview', 'chimplet');

		$this->view['page_title'] = __('Overview', 'chimplet');
		$this->view['menu_title'] = $this->view['page_title'];
		$this->view['menu_slug']  = 'chimplet-overview';

		parent::__construct( $facade );
	}

	/**
	 * Add pages to the WordPress administration menu
	 *
	 * @used-by Action: admin_menu
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 */

	public function append_to_menu()
	{
		$this->wp->add_menu_page( $this->view['document_title'], $this->get_info('name') . $this->append_badge(), 'manage_options', $this->view['menu_slug'], [ $this, 'render_page' ], 'dashicons-email-alt', 81 );
		$this->wp->add_submenu_page( $this->view['menu_slug'], $this->view['document_title'], $this->view['page_title'], 'manage_options', $this->view['menu_slug'], [ $this, 'render_page' ] );
	}

	/**
	 * Append menu badge
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-07)
	 */

	public function append_badge()
	{
		$badge = '';

		$mailchimp_key = $this->get_option('mailchimp-api-key');
		// $version_info  = $this->get_version_info();

		if ( empty( $mailchimp_key ) /* || isset( $version_info['is_valid_key'] ) */ ) {
			$badge = sprintf(
				__('You need to register a %s to use %s.', 'chimplet'),
				__('MailChimp API key', 'chimplet'),
				__('Chimplet', 'chimplet')
			);

			$badge = ' ' . '<span class="update-plugins dashicons" title="' . $badge . '"><span class="dashicons-admin-network"></span></span>';
		}

		return $badge;
	}

	/**
	 * Display the overview
	 *
	 * @used-by Function: add_menu_page
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 */

	public function render_page()
	{
		$this->render_view( 'options-overview', $this->view );
	}

}
