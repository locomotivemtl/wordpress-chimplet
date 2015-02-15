<?php

namespace Locomotive\Chimplet;

/**
 * File: Chimplet Overview Page Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Overview Page
 *
 * @version 2015-02-10
 * @since   0.0.0 (2015-02-05)
 */

class OverviewPage extends BasePage
{

	/**
	 * Before WordPress, mid-initialization
	 *
	 * @version 2015-02-12
	 * @since   0.0.0 (2015-02-07)
	 * @access  public
	 */

	public function before_wp_hooks()
	{
		$this->view['document_title'] = __( 'Chimplet Overview', 'chimplet' );
		$this->view['page_title']     = __( 'Overview', 'chimplet' );
		$this->view['menu_title']     = $this->view['page_title'];
		$this->view['menu_slug']      = 'chimplet-overview';
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
		$this->wp->add_menu_page(
			$this->view['document_title'],
			$this->get_info( 'name' ) . $this->append_badge(),
			apply_filters( 'chimplet/manage/capability', 'manage_options' ),
			$this->view['menu_slug'],
			[ $this, 'render_page' ],
			'dashicons-email-alt',
			81
		);
		$this->wp->add_submenu_page(
			$this->view['menu_slug'],
			$this->view['document_title'],
			$this->view['page_title'],
			apply_filters( 'chimplet/manage/capability', 'manage_options' ),
			$this->view['menu_slug'],
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Append menu badge
	 *
	 * @version 2015-02-12
	 * @since   0.0.0 (2015-02-07)
	 */

	public function append_badge()
	{
		$badge = '';

		if ( ! $this->get_option( 'mailchimp.api_key' ) ) {
			$title = sprintf(
				__( 'You need to register a %s to use %s.', 'chimplet' ),
				__( 'MailChimp API key', 'chimplet' ),
				__( 'Chimplet', 'chimplet' )
			);

			$badge = sprintf( ' <span class="update-plugins dashicons" title="%s"><span class="dashicons-admin-network"></span></span>', esc_attr( $title ) );
		}
		else if ( ! $this->get_option( 'mailchimp.valid' ) ) {
			$title = __( 'You need a valid API key for Chimplet to work', 'chimplet' );

			$badge = sprintf( ' <span class="update-plugins" title="%s"><span class="plugin-count">%s</span></span>', esc_attr( $title ), esc_html( 'error', 'chimplet' ) );
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
