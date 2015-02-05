<?php

namespace Locomotive\Chimplet;

use Locomotive\Facade\WordPress;

/**
 * File: Chimplet Application Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Application
 *
 * @version 2015-02-05
 * @since   0.0.0 (2015-02-05)
 */

class Application extends Base
{
	protected $wp;

	public $settings;

	/**
	 * Constructor
	 *
	 * @param   $facade \WordPress Allows inserting a different facade object for testing.
	 * @return  void
	 */

	public function __construct( WordPress $facade = null )
	{
		$this->setFacade( $facade );
	}

	/**
	 * Set Facade
	 *
	 * @param   $facade \WordPress Allows inserting a different facade object for testing.
	 * @return  void
	 */

	public function setFacade( WordPress $facade = null )
	{
		$this->wp = ( empty( $facade ) ? new WordPress : $facade );
	}

	/**
	 * Chimplet Initialization
	 *
	 * Prepares all the necessary actions, filters, and functions
	 * for the plugin to operate.
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 * @uses    self::$settings
	 * @uses    self::$wp
	 *
	 * @param   string  $file  The filename of the plugin (__FILE__).
	 */

	public function initialize( $file = __FILE__ )
	{
		// var_dump( __CLASS__ . '::' . __FUNCTION__ );

		$this->settings = [
			'name'     => __('Chimplet', 'chimplet'),
			'version'  => '0.0.0',

			'basename' => LOCOMOTIVE_CHIMPLET_ABS, // plugin_basename( $file ),
			'path'     => LOCOMOTIVE_CHIMPLET_DIR, // plugin_dir_path( $file ),
			'url'      => LOCOMOTIVE_CHIMPLET_URL  // plugin_dir_url(  $file )
		];

		$this->wp->load_textdomain( 'chimplet', $this->settings['path'] . 'languages/chimplet-' . get_locale() . '.mo' );

		if ( $this->wp->is_admin() /* && $this->get_setting('show_admin') */ )
		{
			$this->overview = new Overview;

			# $this->include('admin/admin.php');
			# $this->include('admin/options-overview.php');

		}

		$this->wp->add_action( 'init',          [ $this, 'wp_init' ], 1 );
		$this->wp->add_action( 'admin_notices', [ $this, 'render_notices' ] );

		$this->wp->register_activation_hook( LOCOMOTIVE_CHIMPLET_ABS, [ $this, 'activation_hook' ] );

		// plugins.php
		// $this->wp->add_action( "after_plugin_row_{$this->settings['basename']}", [ $this, 'plugin_row' ], 1, 3 );
	}

	/**
	 *
	 *
	 * @used-by Action: register_activation_hook
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 */

	public function activation_hook()
	{
		// $mailchimp_key = GFCommon::get_key();
		// $version_info  = GFCommon::get_version_info();

		// if ( in_array( 'is_valid_key', $version_info ) || isset( $version_info['is_valid_key'] ) ) {
			$this->add_notice( __('The first thing to do is set your MailChimp API key.', 'chimplet') );
		// }
	}

	/**
	 * WordPress Initialization
	 *
	 * @used-by Action: init
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 * @link    AdvancedCustomFields\acf::wp_init() Based on ACF method
	 * @todo    Register assets, post types, taxonomies
	 */

	public function wp_init()
	{
		// var_dump( __CLASS__ . '::' . __FUNCTION__ );

		$min = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min' );

		$this->add_notice( __('You need to register your MailChimp API key to use Chimplet.' . ' ' . '<a href="' . admin_url('admin.php?page=chimplet-overview') . '">' . __('Settings', 'chimplet') . '</a>', 'chimplet') );
	}

	/**
	 * Display any notices
	 *
	 * @used-by Action: admin_notices
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 * @link    AdvancedCustomFields\acf_admin::admin_notices() Based on ACF method
	 */

	function render_notices()
	{
		// var_dump( __CLASS__ . '::' . __FUNCTION__ );

		$notices = $this->get_notices();

		if ( ! empty( $notices ) )
		{
			foreach ( $notices as $notice )
			{
				$open  = '';
				$close = '';

				if ( $notice['wrap'] )
				{
					$open  = '<'  . $notice['wrap'] . '>';
					$close = '</' . $notice['wrap'] . '>';
				}

?>
				<div class="<?php echo $notice['class']; ?>">
					<?php echo $open . $notice['text'] . $close; ?>
				</div>
<?php

			}
		}
	}

	/**
	 * Append a row to the Plugins list table
	 *
	 * @used-by Action: after_plugin_row_$plugin_file
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
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

	public function plugin_row( $plugin_file, $plugin_data, $status )
	{
		// var_dump( __CLASS__ . '::' . __FUNCTION__ );

		// var_dump( $plugin_file, $plugin_data, $status );

		// $mailchimp_key = GFCommon::get_key();
		// $version_info  = GFCommon::get_version_info();

		// if ( in_array( 'is_valid_key', $version_info ) || isset( $version_info['is_valid_key'] ) ) {
			echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message">';

			printf( __('This plugin requires a MailChimp API Key to operate properly.') );

			echo '</div></td></tr>';
		// }
	}

}
