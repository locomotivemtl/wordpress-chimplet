<?php

namespace Locomotive\Chimplet;

use Locomotive\WordPress\AdminNotices;
use Locomotive\WordPress\Facade as WP;
use Locomotive\MailChimp\Facade as MC;

/**
 * File: Chimplet Application Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Escaping for PHPCodeSniffer.
 *
 * Display content that has already been escaped and sanitized.
 *
 * @param string $data Escaped data
 * @return string
 */

function unesc( $data )
{
	echo wp_kses_post( $data );
}

/**
 * Class: Chimplet Application
 *
 * @version 2015-02-13
 * @since   0.0.0 (2015-02-05)
 */

class Application extends Base
{

	/**
	 * @var MC  $mc  Facade for MailChimp
	 * @var WP  $wp  Facade for WordPress
	 */

	public $wp;
	public $mc;

	/**
	 * @var AdminNotices  $notices   WordPress Admin Notifications controller
	 * @var OverviewPage  $overview  Overview Dashboard page
	 * @var SettingsPage  $settings  Plugin Settings page
	 */

	public $notices;
	public $overview;
	public $settings;
	public $rss;

	/**
	 * Chimplet Initialization
	 *
	 * Prepares all the necessary actions, filters, and functions
	 * for the plugin to operate.
	 *
	 * @version 2015-02-13
	 * @since   0.0.0 (2015-02-05)
	 * @uses    self::$information
	 * @uses    self::$wp
	 *
	 * @param   string  $file  The filename of the plugin (__FILE__).
	 */

	public function initialize( $file = __FILE__ )
	{
		$this->wp = new WP;
		$this->mc = new MC;

		self::$information = [
			'name'     => __( 'Chimplet', 'chimplet' ),
			'version'  => '0.0.0',
			'basename' => LOCOMOTIVE_CHIMPLET_ABS, // plugin_basename( $file ),
			'path'     => LOCOMOTIVE_CHIMPLET_DIR, // plugin_dir_path( $file ),
			'url'      => LOCOMOTIVE_CHIMPLET_URL  // plugin_dir_url(  $file )
		];

		$this->wp->load_textdomain( 'chimplet', self::$information['path'] . 'languages/chimplet-' . get_locale() . '.mo' );

		$this->rss = new RSS( $this->wp );

		if ( ! $this->wp->is_admin() ) {
			return;
		}

		$this->verify_version();

		$this->verify_mailchimp_api();

		$this->notices  = new AdminNotices( $this->wp );
		$this->overview = new OverviewPage( $this );
		$this->settings = new SettingsPage( $this );

		$this->wp->add_action( 'init',            [ $this, 'wp_init' ] );
		$this->wp->add_filter( 'plugin_row_meta', [ $this, 'plugin_meta' ], 10, 4 );

		// Ajax function for user sync
		$this->wp->add_action( 'wp_ajax_subscribers_sync', [ $this, 'initial_subscribers_sync' ] );

		// Hook for when a user gets added or udated
		if ( $this->get_option( 'mailchimp.subscribers.automate' ) ) {
			$this->wp->add_action( 'profile_update',     [ $this, 'subscribers_sync' ], 10, 1 );
			$this->wp->add_action( 'user_register',      [ $this, 'subscribers_sync' ], 10, 1 );
		}

		// Third party can use this do initiate user sync
		$this->wp->add_action( 'chimplet/user/sync', [ $this, 'subscribers_sync' ], 10, 1 );

		$this->wp->register_activation_hook( LOCOMOTIVE_CHIMPLET_ABS, [ $this, 'activation_hook' ] );
	}

	/**
	 * Verify versions saved in Options Table
	 *
	 * @version 2015-02-13
	 * @since   0.0.0 (2015-02-09)
	 * @todo    Return a value, maybe a constant to identify any issues.
	 */

	public function verify_version()
	{
		$options = $this->get_options();
		$version = $this->get_info( 'version' );

		if ( empty( $options['initial_version'] ) ) {
			$this->update_option( 'initial_version', $version );
		}

		if ( empty( $options['current_version'] ) || $options['current_version'] !== $version ) {
			$this->update_option( 'current_version', $version );
		}
	}

	/**
	 * Verify MailChimp API
	 *
	 * @version 2015-02-13
	 * @since   0.0.0 (2015-02-13)
	 */

	public function verify_mailchimp_api()
	{
		if ( $this->get_option( 'mailchimp.valid' ) ) {
			return $this->mc->is_api_key_valid( $this->get_option( 'mailchimp.api_key' ) );
		}

		return false;
	}

	/**
	 * WordPress Initialization
	 *
	 * @used-by Action: "init"
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-05)
	 * @link    AdvancedCustomFields\acf::wp_init() Based on ACF method
	 */

	public function wp_init()
	{
		if ( $this->is_related_page() && ! isset( $_GET['settings-updated'] ) ) {

			$mailchimp_key = $this->get_option( 'mailchimp.api_key' );
			$settings_link = '';

			if ( ! $this->is_page( $this->settings->get_menu_slug() ) ) {
				$settings_link = sprintf(
					' <a href="%s">%s</a>',
					admin_url( 'admin.php?page=' . $this->settings->get_menu_slug() ),
					esc_html__( 'Settings' )
				);
			}

			if ( empty( $mailchimp_key ) ) {
				$message = sprintf(
					__( 'You need to register a %s to use %s.', 'chimplet' ),
					'<strong>' . esc_html__( 'MailChimp API key', 'chimplet' ) . '</strong>',
					'<em>' . esc_html__( 'Chimplet', 'chimplet' ) . '</em>'
				);

				$this->notices->add(
					'chimplet/mailchimp/api-key-missing',
					$message . $settings_link,
					[ 'type' => 'error' ]
				);
			}
			else if ( ! $this->get_option( 'mailchimp.valid' ) ) {
				$this->notices->add(
					'chimplet/mailchimp/invalid-api-key',
					sprintf( __( 'Invalid MailChimp API Key: %s.' ), $mailchimp_key ) . ' ' . esc_html( 'Please go to ', 'chimplet' ) . $settings_link, // xss ok
					[ 'type' => 'error' ]
				);
			}
		}

		$this->register_assets();
	}

	/**
	 * Sync WordPress users with MailChimp
	 * @todo Should we update the automate option here
	 * @return array
	 */

	public function initial_subscribers_sync()
	{
		check_admin_referer( 'chimplet-subscribers-sync', 'subscribersNonce' );

		$limit  = 100;
		$offset = isset( $_REQUEST['offset'] ) ? absint( $_REQUEST['offset'] ) : 0;

		$roles   = $this->get_option( 'mailchimp.user_roles' );
		$list_id = $this->get_option( 'mailchimp.list' );

		if ( ! $roles || ! $list_id ) {
			wp_send_json_error( [ 'message' => 'No roles found' ] );
		}

		$users = [];

		// Get all users with the specified user roles
		// See https://tommcfarlin.com/wp_user_query-multiple-roles/
		foreach ( $roles as $role ) {

			$query = new \WP_User_Query(
				[
					'role'   => $role,
					'offset' => $offset,
					'number' => $limit,
				]
			);

			foreach ( $query->get_results() as $user ) {
				$user = $user;
				$users[] = $this->get_user_object( $user, $role );
			}
		}

		if ( ! empty( $users ) ) {
			$result = $this->mc->sync_list_users( $list_id, $users );

			if ( $result ) {
				if ( $limit === count( $users ) ) {
					wp_send_json_success( [ 'message' => 'Users processed','next' => $offset + $limit ] );
				}
			}
			else {
				wp_send_json_error( [ 'message' => 'Error syncing with MailChimp' ] );
			}
		}

		wp_send_json_success( [ 'message' => 'done', 'next' => false ] );
	}

	/**
	 * Sync the current user with the corresponding list if he has one of the roles specified
	 *
	 * @param $user_id
	 */

	public function subscribers_sync( $user_id )
	{
		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return;
		}

		if ( $roles = $this->get_option( 'mailchimp.user_roles' ) ) {
			$role = reset( $user->roles );

			if ( in_array( $role, $roles ) ) {
				$this->mc->sync_list_users( $this->get_option( 'mailchimp.list' ), [ $this->get_user_object( $user, $role ) ] );
			}
		}
	}

	/**
	 * Get user object for MailChimp
	 *
	 * @param WP_User $user WordPress User object
	 * @param string $role Chimplet-associated WordPress Role.
	 * @return mixed|void
	 */

	private function get_user_object( $user, $role )
	{
		static $groupings = null;

		if ( is_null( $groupings ) ) {
			$this->mc->get_list_by_id( $this->get_option( 'mailchimp.list' ) );
			$groupings = $this->mc->get_all_groupings();

			foreach ( $groupings as $key => &$grouping ) {
				$grouping['groups'] = $this->wp->wp_list_pluck( $grouping['groups'], 'name' );
			}

			unset( $groupings['display_order'] );
			unset( $groupings['form_field'] );
		}

		$language = get_locale();

		if ( $language ) {
			$language = substr( $language, 0, 2 );
		}
		else {
			$language = null;
		}

		/**
		 * Filter MailChimp subscriber object from a WordPress User
		 *
		 * @link https://apidocs.mailchimp.com/api/2.0/lists/subscribe.php for merge variables
		 *
		 * @param WP_User $user WordPress User object
		 * @param string $role Chimplet-associated WordPress Role.
		 * @return array|void MailChimp subscriber object
		 */

		return apply_filters( 'chimplet/user/subscribe', [
			'email' => [
				'email' => $user->user_email,
			],

			/**
			 * Filter the email type preference for the MailChimp Subscriber
			 *
			 * Either 'html' (rich-text emails) or 'text' (plain-text emails).
			 *
			 * @param string $email_type Either 'html' or 'text'. Defaults to 'html'.
			 * @param int $user WordPress User ID
			 * @return string
			 */

			'email_type' => apply_filters( 'chimplet/user/email_type', 'html', $user->ID ),
			'merge_vars' => [
				'FNAME'   => $user->first_name,
				'LNAME'   => $user->last_name,
				'WP_ROLE' => $role,

				/**
				 * Filter MailChimp Interest Groupings to associate to a Subscriber
				 *
				 * @param array  $groupings {
				 *     An array of Groupings. Each Grouping is an associative array that contains:
				 *
				 *     @type int      $id      Grouping "id" from `lists/interest-groupings` (either this or $name must be present).
				 *                             This $id takes precedence and can't change (unlike the $name)
				 *     @type string   $name    Grouping "name" from lists/interest-groupings (either this or $id must be present).
				 *     @type array    $groups  An array of valid group names for this grouping.
				 * }
				 * @param int $user WordPress User ID
				 * @return mixed|void
				 */

				'groupings'   => apply_filters( 'chimplet/user/groupings', $groupings, $user->ID ),

				/**
				 * Filter the language preference for the MailChimp Subscriber
				 *
				 * @link http://kb.mailchimp.com/lists/managing-subscribers/view-and-edit-subscriber-languages#code for supported codes that are fully case-sensitive.
				 *
				 * @param string $language Two-letter language code based on current locale.
				 * @param int $user WordPress User ID
				 * @return string|null
				 */

				'mc_language' => apply_filters( 'chimplet/user/language', $language, $user->ID ),
			]
		], $user, $role );
	}

	/**
	 * Register Assets
	 *
	 * @version 2015-02-13
	 * @since   0.0.0 (2015-02-06)
	 */

	public function register_assets()
	{
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );

		$scripts = [
			[
				'handle' => 'chimplet-common',
				'src'    => $this->get_asset( 'scripts/dist/common' . $min . '.js' ),
				'deps'   => [ 'jquery' ],
				'foot'   => true,
				'localized' => [
					'object_name' => 'chimpletCommon',
					'data'        => [
						'action' => 'subscribers_sync',
						'subscriberSyncNonce' => wp_create_nonce( 'chimplet-subscribers-sync' )
					]
				]
			]
		];

		foreach ( $scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], $script['deps'], $this->get_info( 'version' ), $script['foot'] );

			if ( isset( $script['localized'] ) ) {
				wp_localize_script( $script['handle'], $script['localized']['object_name'], $script['localized']['data'] );
			}
		}

		$styles = [
			[
				'handle' => 'chimplet-global',
				'src'    => $this->get_asset( 'styles/dist/global.css' ),
				'deps'   => false,
			]
		];

		foreach ( $styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], $style['deps'], $this->get_info( 'version' ) );
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
		$mailchimp_key = $this->get_option( 'mailchimp.api_key' );

		if ( empty( $mailchimp_key ) && $this->notices instanceof AdminNotices ) {
			$this->notices->add(
				'chimplet/mailchimp/api-key-missing',
				esc_html__( 'The first thing to do is set your MailChimp API key.', 'chimplet' )
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
	 * @param   array $plugin_meta An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
	 * @param   string $plugin_file Path to the plugin file, relative to the plugins directory.
	 * @param   array $plugin_data An array of plugin data.
	 * @param   string $status {
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
	 *
	 * @return array
	 */

	public function plugin_meta( $plugin_meta, $plugin_file, $plugin_data, $status )
	{
		if ( LOCOMOTIVE_CHIMPLET_ABS === $plugin_file ) {
			$plugin_meta[] = '<a href="' . admin_url( 'admin.php?page=' . $this->settings->get_menu_slug() ) . '">' . __( 'Settings' ) . '</a>';
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
		$mailchimp_key = $this->get_option( 'mailchimp.api_key' );

		if ( empty( $mailchimp_key ) ) {
			printf('
				<tr class="plugin-update-tr">
					<td colspan="3" class="plugin-update colspanchange">
						<div class="update-message">%s <a href="%s">%s</a></div>
					</td>
				</tr>
				',
				sprintf( esc_html__( 'This plugin requires a %s to operate.', 'chimplet' ), '<strong>' . esc_html__( 'MailChimp API key', 'chimplet' ) . '</strong>' ),
				admin_url( 'admin.php?page=' . $this->settings->get_menu_slug() ),
				esc_html__( 'Settings' )
			);
		}
	}

}
