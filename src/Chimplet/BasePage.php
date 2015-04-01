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
 * @version 2015-03-24
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
	 * @version 2015-03-03
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

		$this->wp->add_action( 'admin_init', [ $this, 'register_sections' ] );
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
	 * @version 2015-03-31
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
	}

	/**
	 * Register settings sections; optionally including fields
	 *
	 * @used-by Action: "admin_init"
	 * @version 2015-03-03
	 * @since   0.0.0 (2015-03-03)
	 */

	public function register_sections()
	{
	}

	/**
	 * Register settings fields, optionally including sections
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

	public function pre_update_option( $value = [], $old_value = [] )
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
	 * Prints out all settings sections added to a particular settings page
	 *
	 * Replaces do_settings_sections().
	 *
	 * Part of the Settings API. Use this in a settings page callback function
	 * to output all the sections and fields that were added to that $page with
	 * add_settings_section() and add_settings_field()
	 *
	 * @global $wp_settings_sections Storage array of all settings sections added to admin pages
	 * @global $wp_settings_fields Storage array of settings fields and info about their pages/sections
	 * @see    \WordPress\do_settings_sections()
	 *
	 * @param string $page The slug name of the page whos settings sections you want to output
	 */

	public function render_sections( $page )
	{
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		$path = $this->get_path( 'assets/views/section-settings.php' );

		if ( ! file_exists( $path ) ) {
			return do_settings_sections( $page );
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			include $path;
		}
	}

	/**
	 * Print out the settings fields for a particular settings section
	 *
	 * Replaces do_settings_fields().
	 *
	 * Part of the Settings API. Use this in a settings page to output
	 * a specific section. Should normally be called by do_settings_sections()
	 * rather than directly.
	 *
	 * @global $wp_settings_fields Storage array of settings fields and their pages/sections
	 * @see    \WordPress\do_settings_fields()
	 *
	 * @param string $page Slug title of the admin page who's settings fields you want to show.
	 * @param string $section Slug title of the settings section who's fields you want to show.
	 */

	public function render_fields( $page, $section )
	{
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			$field['args']['title'] = $field['title'];

			if ( isset( $field['args']['layout'] ) && 'custom' === $field['args']['layout'] ) {
				echo '</tbody>';
				echo '</table>';
				call_user_func( $field['callback'], $field['args'] );
				echo '<table class="form-table">';
				echo '<tbody>';
			}
			else {

				echo '<tr>';

				$colspan = '';
				$rowspan = '';
				$scope   = ' scope="row"';

				if ( isset( $field['args']['colspan'] ) && $field['args']['colspan'] ) {
					$colspan = ' colspan="' . $field['args']['colspan'] . '"';
					$scope   = ' scope="col"';
				}
				/*
				if ( isset( $field['args']['rowspan'] ) && $field['args']['rowspan'] ) {
					$rowspan = ' rowspan="' . $field['args']['rowspan'] . '"';
				}
				*/
				$span = $colspan . $rowspan;

				$th = $scope . $span;

				if ( ! empty( $field['args']['label_for'] ) ) {
					printf( '<th%s><label for="%s">%s</label></th>', $th, esc_attr( $field['args']['label_for'] ), $field['title'] );
				}
				else {
					printf( '<th%s>%s</th>', $th, $field['title'] );
				}

				if ( $colspan ) {
					echo '</tr>';
					echo '<tr>';
				}

				$td = $span;

				printf( '<td%s>', $td );
				call_user_func( $field['callback'], $field['args'] );
				echo '</td>';
				echo '</tr>';
			}
		}
	}

}
