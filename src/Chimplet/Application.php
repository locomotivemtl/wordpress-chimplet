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
	echo '' . $data;
}

/**
 * Retrieve the image sizes or the the requested size, if available.
 *
 * @global $_wp_additional_image_sizes
 * @uses get_intermediate_image_sizes()
 * @link https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
 *
 * @param string|array $sizes Optional, image size or list of sizes. Default is to return all sizes.
 * @return array Returns an array of image size data
 */

function get_image_sizes( $sizes = [] )
{
	global $_wp_additional_image_sizes;

	$single_size = ( ! is_array( $sizes ) || 1 === count( $sizes ) );

	if ( ! is_array( $sizes ) ) {
		if ( ! empty( $sizes ) ) {
			$sizes = [ $sizes ];
		}
		else {
			$sizes = [];
		}
	}

	$image_sizes = [];
	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach ( $get_intermediate_image_sizes as $_size ) {
		if ( in_array( $_size, [ 'thumbnail', 'medium', 'large' ] ) ) {
			$image_sizes[ $_size ] = [
				'width'  => get_option( $_size . '_size_w' ),
				'height' => get_option( $_size . '_size_h' ),
				'crop'   => (bool) get_option( $_size . '_crop' )
			];
		}
		elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$image_sizes[ $_size ] = [
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop']
			];
		}
	}

	if ( $single_size ) {
		if ( isset( $image_sizes[ reset( $sizes ) ] ) ) {
			return $image_sizes[ reset( $sizes ) ];
		} else {
			return false;
		}
	}
	else if ( count( $sizes ) ) {
		return array_filter(
			$image_sizes,
			function ( $s ) use ( $sizes ) {
				return in_array( $s, $sizes );
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	return $image_sizes;
}

/**
 * Finding all element combinations of an array
 *
 * Iterating over a large set of elements takes a long time.
 * A set of n elements generates 2n+1 sets. In other words,
 * as n grows by 1, the number of elements doubles.
 *
 * A combination focuses on the selection of objects without regard
 * to the order in which they are selected. A permutation, in contrast,
 * focuses on the arrangement of objects with regard to the order in
 * which they are arranged.
 *
 * @link http://docstore.mik.ua/orelly/webprog/pcook/ch04_25.htm Source of function
 * @link http://docstore.mik.ua/orelly/webprog/pcook/ch04_26.htm For a function that finds all permutations of an array.
 * @param array $array The array to work on
 * @return array
 */

function array_power_set( array $array )
{
	$results = [ [] ];

	foreach ( $array as $element ) {
		foreach ( $results as $combination ) {
			array_push( $results, array_merge( [ $element ], $combination ) );
		}
	}

	array_shift( $results );

	return $results;
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
	public $feed;

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

		$this->feed = new Feed( $this->wp );

		if ( ! $this->wp->is_admin() ) {
			return;
		}

		$this->verify_version();

		$this->verify_mailchimp_api();

		$this->notices  = new AdminNotices( $this->wp );
		# $this->overview = new OverviewPage( $this );
		$this->settings = new SettingsPage( $this );

		$this->wp->add_action( 'init',            [ $this, 'wp_init' ] );
		$this->wp->add_filter( 'plugin_row_meta', [ $this, 'plugin_meta' ], 10, 4 );

		if ( $this->get_option( 'mailchimp.campaigns.automate' ) ) {
			$this->wp->add_action( 'wp_ajax_chimplet/campaigns/sync', [ $this, 'sync_all_campaigns' ] );
		}

		if ( $this->get_option( 'mailchimp.subscribers.automate' ) ) {
			$this->wp->add_action( 'wp_ajax_chimplet/subscribers/sync', [ $this, 'sync_all_subscribers' ] );
			$this->wp->add_action( 'profile_update', [ $this, 'sync_subscriber' ], 10, 1 );
			$this->wp->add_action( 'user_register',  [ $this, 'sync_subscriber' ], 10, 1 );
		}

		// Third party can use this do initiate user sync
		$this->wp->add_action( 'chimplet/user/sync', [ $this, 'sync_subscriber' ], 10, 1 );

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
	 * Create MailChimp Campaigns
	 * @return array
	 */

	public function sync_all_campaigns()
	{
		check_admin_referer( 'chimplet-campaigns-sync', 'nonce' );

		$options = $this->get_options();

		$segmented_taxonomies = $this->settings->get_segments_and_groupings();

		if ( empty( $segmented_taxonomies ) ) {
			wp_send_json_error([
				'message' => [
					'type' => 'error',
					'text' => __( 'No WordPress Terms selected or Interest Groupings and Segments unsuccessfully generated.', 'chimplet' )
				]
			]);
		}

		$list_id = $this->get_option( 'mailchimp.list' );

		if ( ! $list_id ) {
			wp_send_json_error([
				'message' => [
					'type' => 'error',
					'text' => __( 'No MailChimp List found.', 'chimplet' )
				]
			]);
		}

		$rss_opts = $this->generate_rss_options();

		if ( ! $rss_opts ) {
			wp_send_json_error([
				'message' => [
					'type' => 'error',
					'text' => __( 'An error occurred while generating RSS options for the Campaigns found.', 'chimplet' )
				]
			]);
		}

		// Delete all campaigns saved and recreate segments
		$this->settings->delete_active_campaigns();

		// From core
		$sitename = strtolower( $_SERVER['SERVER_NAME'] ); //input var okay
		if ( 'www.' == substr( $sitename, 0, 4 ) ) {
			$sitename = substr( $sitename, 4 );
		}

		$active_campaigns  = [];
		$failed_campaigns  = [];
		$unsent_campaigns  = [];
		$empty_segments    = 0;
		$consecutive_fails = 0;
		$consecutive_limit = 5;

		foreach ( $segmented_taxonomies as $taxonomy_name => $taxonomy_set ) {
			foreach ( $taxonomy_set['segments'] as $segmented_terms ) {
				if ( empty( $segmented_terms['rules']['conditions'][0]['value'] ) ) {
					continue;
				}

				// Here we must generate the url for mailchimp to fetch
				// Build the RSS url on format: /chimplet/monthly/?tax[category]=6,5
				$rss_opts['url'] = $this->feed->url_from_segmented_terms( $segmented_terms, $rss_opts['schedule'] );

				$title   = sprintf( __( 'Chimplet Digest - %s', 'chimplet' ), str_replace( ',', ', ', $segmented_terms['rules']['conditions'][0]['value'] ) );
				$subject = sprintf( __( 'Chimplet %s Digest', 'chimplet' ), ucfirst( $rss_opts['schedule'] ) );

				$campaign_opts = $this->wp->apply_filters( 'chimplet/campaign', [
					'type'    => 'rss',
					'options' => $this->wp->apply_filters( 'chimplet/campaign/options', [
						'list_id'     => $list_id,
						'title'       => $title,
						'subject'     => $subject,
						'from_email'  => $this->wp->apply_filters( 'wp_mail_from', 'chimplet@' . $sitename ), // xss ok
						'from_name'   => $this->wp->apply_filters( 'wp_mail_from_name', 'Chimplet' ),
						'to_name'     => '*|FNAME|* *|LNAME|*',
						'template_id' => absint( $options['mailchimp']['campaigns']['template'] ),
					], $segmented_terms, $rss_opts['schedule'] ),
					'content'      => $this->wp->apply_filters( 'chimplet/campaign/content', [], $segmented_terms, $rss_opts['schedule'] ),
					'segment_opts' => $this->wp->apply_filters( 'chimplet/campaign/segment_opts', $segmented_terms['rules'], $segmented_terms, $rss_opts['schedule'] ),
					'type_opts' => $this->wp->apply_filters( 'chimplet/campaign/type_opts', [
						'rss'   => $rss_opts
					], $segmented_terms, $rss_opts['schedule'] ),
				], $segmented_terms, $rss_opts['schedule'] );

				if ( ! isset( $campaign_opts['options']['folder_id'] ) ) {
					$folder_name = $this->wp->apply_filters( 'chimplet/campaign/folder_name', 'Chimplet', $segmented_terms, $rss_opts['schedule'] );
					$folder_id = $this->mc->get_campaign_folder_id( $folder_name );

					if ( is_int( $folder_id ) ) {
						$campaign_opts['options']['folder_id'] = $this->wp->apply_filters( 'chimplet/campaign/folder_id', $folder_id, $segmented_terms, $rss_opts['schedule'] );
					}
				}

				$campaign = $this->mc->create_campaign( $campaign_opts );

				$this->wp->do_action( 'chimplet/campaign/created', $campaign );

				if ( $campaign instanceof \Mailchimp_Error ) {
					switch ( get_class( $campaign ) ) {
						case 'Mailchimp_Invalid_Folder':
						case 'Mailchimp_Invalid_Options':
						case 'Mailchimp_Invalid_Template':
						case 'Mailchimp_List_DoesNotExist':
							error_log( var_export( $campaign_opts, true ) );
							wp_send_json_error([
								'message' => [
									'type' => 'error',
									'text' => sprintf( '<code>%1$s</code> (<code>%3$s</code>) &mdash; <q>%2$s</q>', get_class( $campaign ), $campaign->getMessage(), $campaign->getCode() )
								]
							]);
							break;

						case 'Mailchimp_Campaign_InvalidSegment':
							$empty_segments++;
							break;

						default:
							$failed_campaigns[] = $campaign;
							$consecutive_fails++;
							break;
					}
				}
				else if ( $campaign ) {
					$active_campaigns[] = $campaign['id'];

					if ( ! isset( $campaign['is_broadcast'] ) || ! $campaign['is_broadcast'] ) {
						$unsent_campaigns[] = $campaign['id'];
					}
				}
				else {
					$failed_campaigns[] = false;
					$consecutive_fails++;
				}

				if ( $consecutive_fails >= $consecutive_limit ) {
					if ( class_exists( '\NumberFormatter' ) ) {
						$nf = new \NumberFormatter( get_locale(), \NumberFormatter::SPELLOUT );
						$_limit = $nf->format( $consecutive_limit );
					}
					else {
						$_limit = $consecutive_limit;
					}

					$last_error = end( $failed_campaigns );

					$_text = sprintf( __( 'Chimplet has experienced %1$s consecutive failures to create Campaigns. Cancelling the rest.', 'chimplet' ), $_limit );

					if ( $last_error instanceof \Mailchimp_Error ) {
						$_text .= ' <br>' . sprintf( __( 'Last error: %s', 'chimplet' ), sprintf( '<code>%1$s</code> (<code>%3$s</code>) &mdash; <q>%2$s</q>', get_class( $last_error ), $last_error->getMessage(), $last_error->getCode() ) );
					}

					wp_send_json_error([
						'message' => [
							'type' => 'error',
							'text' => $_text
						]
					]);
				}
			}
		}

		$active_count = count( $active_campaigns );
		$failed_count = count( $failed_campaigns );
		$unsent_count = count( $unsent_campaigns );
		$total_count  = $active_count + $failed_count + $empty_segments;

		if ( $active_count ) {
			$options['mailchimp']['campaigns']['active'] = $active_campaigns;

			$this->update_options( $options );
		}

		if ( $active_count < 1 ) {
			$type = 'error';
			$message = sprintf( __( 'No Campaigns were created for MailChimp (<strong>%1$d failure(s)/%2$d success(es)</strong>).', 'chimplet' ), $failed_count, $active_count );
		}
		else if ( $failed_count > 0 ) {
			$type = 'warning';
			$message = sprintf( __( 'Not all Segments and Campaigns were synchronized with MailChimp (<strong>%1$d failure(s)/%2$d success(es)</strong>).', 'chimplet' ), $failed_count, $active_count );
		}
		else {
			$type = 'success';
			$message = sprintf( __( 'Successfully synchronized <strong>%1$d</strong> Campaigns with MailChimp.', 'chimplet' ), ( $total_count - $empty_segments ) );
		}

		if ( $unsent_count ) {
			$message .= ' <br>' . sprintf( __( 'Unfortunately, <strong>%d</strong> Campaign(s) could not be sent or started. Visit your MailChimp account for more details.', 'chimplet' ), $unsent_count );
		}

		if ( $empty_segments ) {
			$message .= ' <br>' . sprintf( __( 'Skipped <strong>%d</strong> Campaign(s) for having empty segments (0 recipients).', 'chimplet' ), $empty_segments );
		}

		wp_send_json_success([
			'message' => [
				'type' => $type,
				'text' => $message
			]
		]);
	}

	/**
	 * Sync WordPress users with MailChimp
	 * @return array
	 */

	public function sync_all_subscribers()
	{
		global $wpdb;

		check_admin_referer( 'chimplet-subscribers-sync', 'nonce' );

		$limit  = 100;
		$offset = ( isset( $_REQUEST['offset'] ) ? absint( $_REQUEST['offset'] ) : 0 );

		$roles   = $this->get_option( 'mailchimp.user_roles' );

		if ( ! $roles ) {
			wp_send_json_error([
				'message' => [
					'type' => 'error',
					'text' => __( 'No WordPress User Roles found.', 'chimplet' )
				]
			]);
		}

		$list_id = $this->get_option( 'mailchimp.list' );

		if ( ! $list_id ) {
			wp_send_json_error([
				'message' => [
					'type' => 'error',
					'text' => __( 'No MailChimp List found.', 'chimplet' )
				]
			]);
		}

		/**
		 * Retrieve users associated with certain roles.
		 *
		 * @link http://wordpress.stackexchange.com/a/88158/18350 Multiple roles
		 */

		$query_args = [
			'orderby' => 'registered',
			'order'   => 'ASC',
			'offset'  => $offset,
			'number'  => $limit
		];

		if ( count( $roles ) > 1 ) {
			$blog_id = get_current_blog_id();
			$prefix  = $wpdb->get_blog_prefix( $blog_id );

			$meta_query = [ 'relation' => 'OR' ];

			foreach ( $roles as $role ) {
				$meta_query[] = [
					'key'     => $prefix . 'capabilities',
					'value'   => '"' . $role . '"',
					'compare' => 'like'
				];
			}

			$query_args['meta_query'] = $meta_query;
		}
		else {
			$query_args['role'] = reset( $roles );
		}

		if ( isset( $query_args['role'] ) || isset( $query_args['meta_query'] ) ) {
			$user_query = new \WP_User_Query( $query_args );
		}
		else {
			wp_send_json_error([
				'message' => [
					'type' => 'error',
					'text' => __( 'An error occurred while attempting to select WordPress Users.', 'chimplet' )
				]
			]);
		}

		$subscribers = [];

		if ( $user_query->get_results() ) {
			foreach ( $user_query->get_results() as $user ) {
				/** In case a WP_User has more than one role, get a single Chimplet-valid role. */
				$role = array_intersect( $user->roles, $roles );

				if ( empty( $role ) ) {
					continue;
				}

				$role = end( $role );

				if ( empty( $role ) ) {
					continue;
				}

				$subscribers[] = $this->get_user_object( $user, $role );
			}
		}

		if ( count( $subscribers ) ) {
			$result = $this->mc->sync_list_users( $list_id, $subscribers );

			if ( $result ) {
				if ( $limit === count( $subscribers ) ) {
					$new_offset = ( $offset + $limit );

					wp_send_json_success([
						'message' => [
							'type' => 'info',
							// Please don’t turn off your console.
							'text' => sprintf(
								'<strong>%s</strong> %s',
								sprintf(
									__( 'Syncing %1$d/%2$d WordPress Users to MailChimp.', 'chimplet' ),
									$new_offset,
									$user_query->get_total()
								),
								__( 'Please wait.', 'chimplet' )
							)
						],
						'next' => $new_offset
					]);
				}
			}
			else {
				wp_send_json_error([
					'message' => [
						'type' => 'error',
						'text' => __( 'An error occurred while syncing WordPress Users to MailChimp.', 'chimplet' )
					]
				]);
			}
		}

		wp_send_json_success([
			'message' => [
				'type' => 'success',
				'text' => __( 'Successfully synced WordPress Users to MailChimp.', 'chimplet' )
			],
			'next' => false
		]);
	}

	/**
	 * Sync the current user with the corresponding list if he has one of the roles specified
	 *
	 * @param $user_id
	 */

	public function sync_subscriber( $user_id )
	{
		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return;
		}

		$list_id = $this->get_option( 'mailchimp.list' );

		if ( $roles = $this->get_option( 'mailchimp.user_roles' ) ) {
			$role = reset( $user->roles );

			$subscriber = $this->get_user_object( $user, $role );

			if ( in_array( $role, $roles ) ) {
				$this->mc->sync_list_users( $list_id, [ $subscriber ] );
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
		 * @param array $subscriber A MailChimp subscriber
		 * @param WP_User $user WordPress User object
		 * @param string $role Chimplet-associated WordPress Role.
		 * @return array|void MailChimp subscriber object
		 */

		return $this->wp->apply_filters( 'chimplet/user/subscribe', [
			'email' => [
				'email' => $user->user_email,
			],

			/**
			 * Filter the email type preference for the MailChimp Subscriber
			 *
			 * Either 'html' (rich-text emails) or 'text' (plain-text emails).
			 *
			 * @param string $email_type Either 'html' or 'text'. Defaults to 'html'.
			 * @param int $user_id WordPress User ID
			 * @return string
			 */

			'email_type' => $this->wp->apply_filters( 'chimplet/user/email_type', 'html', $user->ID ),

			/**
			 * Filter the merges to associate to a Subscriber
			 *
			 * @param array $merge_vars An array of mergs
			 * @param WP_User $user WordPress User object
			 * @param string $role Chimplet-associated WordPress Role.
			 * @return array|void MailChimp subscriber object
			 */

			'merge_vars' => $this->wp->apply_filters( 'chimplet/user/merge_vars', [
				'FNAME'   => $user->first_name,
				'LNAME'   => $user->last_name,
				'WP_ROLE' => $role,

				/**
				 * Filter MailChimp Interest Groupings to associate to a Subscriber
				 *
				 * @param array  $interests {
				 *     An array of Interest Groupings the user belongs to. Each Grouping is an associative array that contains:
				 *
				 *     @type int      $id      Grouping "id" from `lists/interest-groupings` (either this or $name must be present).
				 *                             This $id takes precedence and can't change (unlike the $name)
				 *     @type string   $name    Grouping "name" from lists/interest-groupings (either this or $id must be present).
				 *     @type array    $groups  An array of valid group names for this grouping.
				 * }
				 * @param array  $groupings {
				 *     An array of available Interest Groupings. Baisc properties documented in {@see $interests}.
				 *
				 *     @type string  $form_field     Gives the type of interest group: checkbox, radio, select.
				 *     @type string  $display_order  The display order of the grouping, if set.
				 *     @type array   $groups {
				 *         Each Group is an associative array that contains:
				 *
				 *         @type int     $id             Group "id" from `lists/interest-groupings`.
				 *         @type string  $bit            The bit value - not really anything to be done with this.
				 *         @type string  $name           The name of the group.
				 *         @type string  $display_order  The display order of the group, if set.
				 *         @type int     $subscribers    Total number of subscribers who have this group, if set.
				 *     }
				 * }
				 * @param int $user_id WordPress User ID
				 * @return mixed|void
				 */

				'groupings'   => $this->wp->apply_filters( 'chimplet/user/groupings', [], $groupings, $user->ID ),

				/**
				 * Filter the language preference for the MailChimp Subscriber
				 *
				 * @link http://kb.mailchimp.com/lists/managing-subscribers/view-and-edit-subscriber-languages#code for supported codes that are fully case-sensitive.
				 *
				 * @param string $language Two-letter language code based on current locale.
				 * @param int $user_id WordPress User ID
				 * @return string|null
				 */

				'mc_language' => $this->wp->apply_filters( 'chimplet/user/language', $language, $user->ID ),
			], $user, $role )
		], $user, $role );
	}

	/**
	 * Generate RSS options for a new Campaign
	 * from Chimplet's options.
	 *
	 * @see SettingsPage\sanitize_settings()
	 * @version 2015-04-02
	 * @since   0.0.0 (2015-04-02)
	 */

	public function generate_rss_options()
	{
		$options = $this->get_option( 'mailchimp.campaigns.schedule' );

		if ( empty( $options ) ) {
			return false;
		}

		$rss_opts = [];

		if ( isset( $options['frequency'] ) ) {
			switch ( $options['frequency'] ) {
				case 'daily':
					$rss_opts = [
						'schedule' => 'daily',
						'days'     => array_fill_keys( $options['days'], true ),
					];
					break;

				case 'weekly':
					$rss_opts = [
						'schedule'         => 'weekly',
						'schedule_weekday' => $options['weekday'],
					];
					break;

				case 'monthly':
					$rss_opts = [
						'schedule'          => 'monthly',
						'schedule_monthday' => $options['monthday'],
					];
					break;

				default:
					return false;
					break;
			}
		}

		if ( isset( $options['hour'] ) ) {
			$rss_opts['schedule_hour'] = absint( $options['hour'] );
		}
		else {
			return false;
		}

		return $rss_opts;
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
				'handle'    => 'chimplet-common',
				'src'       => $this->get_asset( 'scripts/dist/common' . $min . '.js' ),
				'deps'      => [ 'jquery' ],
				'foot'      => true,
				'localized' => [
					'name' => 'chimpletL10n',
					'data' => [
						'segmentCount' => __( 'Groups: %1$d / Segments: %2$d', 'chimplet' )
					]
				]
			]
		];

		foreach ( $scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], $script['deps'], $this->get_info( 'version' ), $script['foot'] );

			if ( isset( $script['localized'] ) ) {
				wp_localize_script( $script['handle'], $script['localized']['name'], $script['localized']['data'] );
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
