<?php

namespace Locomotive\Chimplet;

use Locomotive\WordPress\AdminNotices;
use Locomotive\WordPress\Facade as WP;
use Locomotive\MailChimp\Facade as MC;

/**
 * File: Chimplet Administration Page Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Administration Page
 *
 * @version 2015-02-12
 * @since   0.0.0 (2015-02-07)
 */

abstract class BasePage extends Base
{

	/**
	 * @var array        $view  Collection of key/value pairs to passed on to the template.
	 * @var string|bool  $hook  The resulting page's hook suffix, or false if the user does not have the capability required.
	 */

	protected $view = [];
	protected $hook = '';

	/**
	 * @var MC            $mc       Facade for MailChimp
	 * @var WP            $wp       Facade for WordPress
	 * @var Application   $app      Plugin master class
	 * @var AdminNotices  $notices  AdminNotices Controller Object
	 */

	public $mc;
	public $wp;
	public $app;
	public $notices;

	/**
	 * Constructor
	 *
	 * Prepares all the necessary actions, filters, and functions
	 * for the plugin to operate.
	 *
	 * @version 2015-02-12
	 * @since   0.0.0 (2015-02-05)
	 * @access  public
	 * @param   Application $app
	 */

	public function __construct( Application $app = null )
	{
		$this->set_app( $app );

		if ( ! $this->wp->is_admin() ) {
			return;
		}

		$this->before_wp_hooks();

		$this->wp->add_action( 'admin_menu', [ $this, 'append_to_menu' ] );
		$this->wp->add_action( 'admin_init', [ $this, 'register_settings' ] );

		$this->wp->add_filter( 'pre_update_option_chimplet', [ $this, 'pre_update_option' ], 1, 2 );

		$this->after_wp_hooks();
	}

	/**
	 * During class initialization, before WordPress hooks.
	 *
	 * Placeholder method to be replaced in inherited class.
	 *
	 * @used-by self::__construct()
	 * @version 2015-02-12
	 * @since   0.0.0 (2015-02-12)
	 */

	protected function before_wp_hooks()
	{
	}

	/**
	 * During class initialization, after WordPress hooks.
	 *
	 * Placeholder method to be replaced in inherited class.
	 *
	 * @used-by self::__construct()
	 * @version 2015-02-12
	 * @since   0.0.0 (2015-02-12)
	 */

	protected function after_wp_hooks()
	{
	}

	/**
	 * Set reference to Application object, MailChimp facade, and WordPress facade
	 *
	 * @version 2015-02-12
	 * @since   0.0.0 (2015-02-12)
	 * @access  public
	 * @param   Application $app
	 */

	public function set_app( Application $app = null )
	{
		if ( empty( $app ) && ! is_object( $this->app ) ) {
			return;
		}

		if ( is_object( $app ) ) {
			$this->app = $app;
		}

		// Shortcuts
		if ( $this->app->mc instanceof MC ) {
			$this->mc = &$this->app->mc;
		}

		if ( $this->app->wp instanceof WP ) {
			$this->wp = &$this->app->wp;
		}

		if ( $this->app->notices instanceof AdminNotices ) {
			$this->notices = &$this->app->notices;
		}
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
		$this->wp->wp_enqueue_script( 'chimplet-common' );

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

			if ( is_object( $this->app->{ $page_slug } ) ) {
				return $this->app->{ $page_slug }->get_menu_slug();
			}
		}

		return '';
	}

}
