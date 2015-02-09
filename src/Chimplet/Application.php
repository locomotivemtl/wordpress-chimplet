<?php

namespace Locomotive\Chimplet;

use Locomotive\Singleton;
use Locomotive\WordPress\WP;
use Locomotive\WordPress\Facade;
use Locomotive\WordPress\AdminNotices;

/**
 * File: Chimplet Application Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Application
 *
 * @version 2015-02-09
 * @since   0.0.0 (2015-02-05)
 */

class Application extends Base
{
	use Singleton, Facade;

	protected $notices;
	protected $overview;
	protected $configure;

	public $information;
	public $options;

	/**
	 * Chimplet Initialization
	 *
	 * Prepares all the necessary actions, filters, and functions
	 * for the plugin to operate.
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 * @uses    self::$information
	 * @uses    self::$wp
	 *
	 * @param   string  $file  The filename of the plugin (__FILE__).
	 */

	public function initialize( $file = __FILE__ )
	{
		// var_dump( __CLASS__ . '::' . __FUNCTION__ );

		$this->set_facade();

		if ( ! $this->wp->is_admin() ) {
			return;
		}

		$this->information = [
			'name'     => __('Chimplet', 'chimplet'),
			'version'  => '0.0.0',

			'basename' => LOCOMOTIVE_CHIMPLET_ABS, // plugin_basename( $file ),
			'path'     => LOCOMOTIVE_CHIMPLET_DIR, // plugin_dir_path( $file ),
			'url'      => LOCOMOTIVE_CHIMPLET_URL  // plugin_dir_url(  $file )
		];

		$this->wp->load_textdomain( 'chimplet', $this->information['path'] . 'languages/chimplet-' . get_locale() . '.mo' );

		$this->notices   = AdminNotices::get_singleton();
		$this->overview  = Overview::get_singleton();
		$this->configure = Settings::get_singleton();

		$this->wp->add_action( 'init', [ $this, 'wp_init' ] );

		$this->wp->add_filter( 'plugin_row_meta', [ &$this, 'plugin_meta' ], 10, 4 );

		$this->wp->register_activation_hook( LOCOMOTIVE_CHIMPLET_ABS, [ $this, 'activation_hook' ] );

		// plugins.php
		// $this->wp->add_action( "after_plugin_row_{$this->information['basename']}", [ $this, 'plugin_row' ], 1, 3 );
	}

	/**
	 * WordPress Initialization
	 *
	 * @used-by Action: "init"
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 * @link    AdvancedCustomFields\acf::wp_init() Based on ACF method
	 * @todo    Register assets, post types, taxonomies
	 */

	public function wp_init()
	{
		// var_dump( __CLASS__ . '::' . __FUNCTION__ );

		$min = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min' );

		if ( $this->is_related_page() ) {

			$mailchimp_key = $this->get_info('mailchimp-key');
			// $version_info  = $this->get_version_info();

			if ( ( empty( $mailchimp_key ) /* || isset( $version_info['is_valid_key'] ) */ ) && $this->notices instanceof AdminNotices ) {
				$message = sprintf(
					__('You need to register a %s to use %s.', 'chimplet'),
					'<strong>' . __('MailChimp API key', 'chimplet') . '</strong>',
					'<em>' . __('Chimplet', 'chimplet') . '</em>'
				);

				if ( ! $this->is_page( $this->configure->get_menu_slug() ) ) {
					$message .= ' ' . '<a href="' . admin_url( 'admin.php?page=' . $this->configure->get_menu_slug() ) . '">' . __('Settings') . '</a>';
				}

				$this->notices->add(
					'chimplet/mailchimp/api-key-missing',
					$message,
					[ 'type' => 'error' ]
				);
			}

		}

		$this->register_assets();
	}

	/**
	 * Register Assets
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-06)
	 */

	public function register_assets()
	{
		$scripts = [];

		foreach ( $scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], $script['deps'], $this->get_info('version') );
		}

		$styles = [
			[
				'handle' => 'chimplet-global',
				'src'    => $this->get_asset('styles/dist/global.css'),
				'deps'   => false,
			]
		];

		foreach ( $styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], $style['deps'], $this->get_info('version') );
		}
	}

	/**
	 * Plugin Activation
	 *
	 * @used-by Action: "register_activation_hook"
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 */

	public function activation_hook()
	{
		$mailchimp_key = $this->get_info('mailchimp-key');
		// $version_info  = $this->get_version_info();

		if ( ( empty( $mailchimp_key ) /* || isset( $version_info['is_valid_key'] ) */ ) && $this->notices instanceof AdminNotices ) {
			$this->notices->add(
				'chimplet/mailchimp/api-key-missing',
				__('The first thing to do is set your MailChimp API key.', 'chimplet')
			);
		}
	}

	/**
	 * Append meta data to a plugin in the Plugins list table
	 *
	 * @used-by Filter: "plugin_row_meta"
	 * @version 2015-02-06
	 * @since   0.0.0 (2015-02-06)
	 *
	 * @param   array   $plugin_meta  An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
	 * @param   string  $plugin_file  Path to the plugin file, relative to the plugins directory.
	 * @param   array   $plugin_data  An array of plugin data.
	 * @param   string  $status       {
	 *     Status of the plugin. Defaults are:
	 *     - All (all)
	 *     - Active (active)
	 *     - Inactive (inactive)
	 *     - Recently Activated (recently_activated)
	 *     - Upgrade (upgrade)
	 *     - Must-Use (mustuse)
	 *     - Drop-ins (dropins)
	 *     - Search (search)
	 * }
	 */

	public function plugin_meta( $plugin_meta, $plugin_file, $plugin_data, $status )
	{
		if ( LOCOMOTIVE_CHIMPLET_ABS === $plugin_file ) {
			$plugin_meta[] = '<a href="' . admin_url( 'admin.php?page=' . $this->configure->get_menu_slug() ) . '">' . __('Settings') . '</a>';
		}

		return $plugin_meta;
	}

	/**
	 * Append a row for a plugin in the Plugins list table
	 *
	 * @used-by Action: "after_plugin_row_$plugin_file"
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $plugin_file  Path to the plugin file, relative to the plugins directory.
	 * @param   array   $plugin_data  An array of plugin data.
	 * @param   string  $status       {@see self::plugin_meta()} for possible values
	 */

	public function plugin_row( $plugin_file, $plugin_data, $status )
	{
		$mailchimp_key = $this->get_info('mailchimp-key');
		// $version_info  = $this->get_version_info();

		if ( empty( $mailchimp_key ) /* || isset( $version_info['is_valid_key'] ) */ ) {
			echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message">';

			printf(
				__('This plugin requires a %s to operate.', 'chimplet'),
				'<strong>' . __('MailChimp API key', 'chimplet') . '</strong>'
			);

			echo ' ' . '<a href="' . admin_url( 'admin.php?page=' . $this->configure->get_menu_slug() ) . '">' . __('Settings') . '</a>';

			echo '</div></td></tr>';
		}
	}

}
