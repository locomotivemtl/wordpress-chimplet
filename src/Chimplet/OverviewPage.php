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
		parent::{ __FUNCTION__ }();

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
