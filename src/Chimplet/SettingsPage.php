<?php

namespace Locomotive\Chimplet;

/**
 * File: Chimplet Settings Page Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Settings Page
 *
 * @version 2015-02-13
 * @since   0.0.0 (2015-02-07)
 */

class SettingsPage extends BasePage
{

	const SETTINGS_KEY = 'chimplet';
	const USER_ROLE_MERGE_VAR = 'WP_ROLE';

	/**
	 * @var array  $excluded_post_types  Post types to exclude when fetching Taxonomy objects
	 * @var array  $excluded_taxonomies  Taxonomies to exclude when fetching Taxonomy objects
	 */

	public $excluded_post_types = [];
	public $excluded_taxonomies = [];

	/**
	 * Before WordPress, mid-initialization
	 *
	 * @version 2015-02-12
	 * @since   0.0.0 (2015-02-07)
	 * @access  public
	 */

	public function before_wp_hooks()
	{
		$this->view['document_title'] = __( 'Chimplet Settings', 'chimplet' );

		$this->view['page_title'] = __( 'Settings' );
		$this->view['menu_title'] = $this->view['page_title'];
		$this->view['menu_slug']  = 'chimplet-settings';

		$this->excluded_post_types = [ 'page', 'revision', 'nav_menu_item' ];
		$this->excluded_taxonomies = [ 'post_format', 'nav_menu' ];

		$this->notices->set_settings_errors_params( self::SETTINGS_KEY );
	}

	/**
	 * Register settings and sections
	 *
	 * @used-by Action: "admin_init"
	 * @version 2015-03-06
	 * @since   0.0.0 (2015-03-03)
	 */

	public function register_sections()
	{
		$this->wp->register_setting(
			self::SETTINGS_KEY,
			self::SETTINGS_KEY,
			[ $this, 'sanitize_settings' ]
		);

		$this->wp->add_settings_section(
			'chimplet-section-mailchimp-api',
			__( 'API Management', 'chimplet' ),
			[
				'before' => [ $this, 'render_mailchimp_section' ],
				'after'  => ( $this->get_option( 'mailchimp.api_key' ) ? null : [ $this, 'render_submit_button' ] )
			],
			$this->view['menu_slug']
		);

		// Add these fields when the API Key is integrated
		if ( $this->get_option( 'mailchimp.valid' ) ) {

			$this->wp->add_settings_section(
				'chimplet-section-mailchimp-lists',
				__( 'List Management', 'chimplet' ),
				null,
				$this->view['menu_slug']
			);

			// Add these fields when the List is selected
			if ( $list = $this->get_option( 'mailchimp.list' ) ) {

				$list = $this->mc->get_list_by_id( $list );

				if ( ! $list instanceof \Mailchimp_Error ) {

					$this->wp->add_settings_section(
						'chimplet-section-mailchimp-campaigns',
						__( 'Campaign Management', 'chimplet' ),
						null,
						$this->view['menu_slug']
					);

				}
			}

		}
	}

	/**
	 * Register settings and fields
	 *
	 * @used-by Action: "admin_init"
	 * @version 2015-03-03
	 * @since   0.0.0 (2015-02-09)
	 */

	public function register_settings()
	{
		if ( false === $this->wp->get_option( 'chimplet' ) ) {
			$this->wp->update_option( 'chimplet', [] );
		}

		$this->wp->add_settings_field(
			'chimplet-field-mailchimp-api_key',
			__( 'API Key', 'chimplet' ),
			[ $this, 'render_mailchimp_field_api_key_section' ],
			$this->view['menu_slug'],
			'chimplet-section-mailchimp-api',
			[
				'label_for' => 'chimplet-field-mailchimp-api_key'
			]
		);

		// Add these fields when the API Key is integrated
		if ( $this->get_option( 'mailchimp.valid' ) ) {

			$this->wp->add_settings_field(
				'chimplet-field-mailchimp-lists',
				__( 'Select Mailing List', 'chimplet' ),
				[ $this, 'render_mailchimp_field_list_section' ],
				$this->view['menu_slug'],
				'chimplet-section-mailchimp-lists',
				[
					'layout'   => 'custom',
					'control'  => 'radio-table' // Choices: select, radio-table
				]
			);

			// Add these fields when the List is selected
			if ( $list = $this->get_option( 'mailchimp.list' ) ) {

				$list = $this->mc->get_list_by_id( $list );

				if ( ! $list instanceof \Mailchimp_Error ) {

					$this->wp->add_settings_field(
						'chimplet-field-mailchimp-categories',
						__( 'Select Taxonomy Terms', 'chimplet' ),
						[ $this, 'render_mailchimp_field_terms_section' ],
						$this->view['menu_slug'],
						'chimplet-section-mailchimp-lists',
						[ 'list' => $list ]
					);

					$this->wp->add_settings_field(
						'chimplet-field-mailchimp-user-roles',
						__( 'Select User Roles', 'chimplet' ),
						[ $this, 'render_mailchimp_field_user_roles_section' ],
						$this->view['menu_slug'],
						'chimplet-section-mailchimp-lists',
						[ 'list' => $list ]
					);

					$this->wp->add_settings_field(
						'chimplet-field-mailchimp-campaign-automation',
						__( 'Automation', 'chimplet' ),
						[ $this, 'render_mailchimp_field_campaign_automation' ],
						$this->view['menu_slug'],
						'chimplet-section-mailchimp-campaigns',
						[
							'label_for' => 'chimplet-field-mailchimp-campaign-automation'
						]
					);

					// Add these fields when Campaign Automation is enabled
					if ( $this->get_option( 'mailchimp.campaigns.automate' ) ) {

						$this->wp->add_settings_field(
							'chimplet-field-mailchimp-campaign-schedule',
							__( 'Schedule', 'chimplet' ),
							[ $this, 'render_mailchimp_field_campaign_schedule' ],
							$this->view['menu_slug'],
							'chimplet-section-mailchimp-campaigns'
						);

						$this->wp->add_settings_field(
							'chimplet-field-mailchimp-campaign-template',
							__( 'RSS Template', 'chimplet' ),
							[ $this, 'render_mailchimp_field_campaign_template' ],
							$this->view['menu_slug'],
							'chimplet-section-mailchimp-campaigns'
						);

					}
				}
			}
		}
	}

	/**
	 * Sanitize all settings values and handles group sync and API key validation
	 *
	 * @uses    Filter: "sanitize_option_{$option_name}"
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 * @param array $settings
	 *
	 * @return array
	 */

	public function sanitize_settings( $settings )
	{
		// Validate key with MailChimp service
		if ( isset( $settings['mailchimp']['api_key'] ) && ! empty( $settings['mailchimp']['api_key'] ) ) {
			$is_valid_key = $this->mc->is_api_key_valid( $settings['mailchimp']['api_key'] );

			if ( $is_valid_key ) {
				$settings['mailchimp']['valid'] = true;
			}
			else {
				// Save that this the key is invalid
				$this->wp->add_settings_error(
					self::SETTINGS_KEY,
					'mailchimp-api-key-failed',
					sprintf( __( 'Invalid MailChimp API Key: %s' ), $settings['mailchimp']['api_key'] ),
					'error'
				);

				$settings['mailchimp']['valid'] = false;
			}
		}

		if ( isset( $settings['mailchimp']['list'] ) ) {
			$list = $this->mc->get_list_by_id( $settings['mailchimp']['list'] );

			if ( $list instanceof \Mailchimp_Error ) {

				$this->wp->add_settings_error(
					self::SETTINGS_KEY,
					'mailchimp-invalid-list',
					$list->getMessage(),
					'error'
				);

				$list = null;

			}
		}

		// No need to continue further if we don't have list...
		if ( empty( $list ) ) {
			return $settings;
		}

		// Do we have any taxonomies?
		if ( isset( $settings['mailchimp']['terms'] ) ) {

			$segments = $this->save_taxonomy_terms( $settings['mailchimp']['terms'] );

		}

		if ( isset( $settings['mailchimp']['user_roles'] ) ) {

			$this->save_user_roles( $settings['mailchimp']['user_roles'] );

		}

		// We have new segments we add a corresponding campaigns
		if ( isset( $settings['mailchimp']['campaigns']['automate'] ) ) {

			// Here we compare old campaign settings to the new one
			if ( ! $segments ) {
				$old_options = $this->get_option( 'mailchimp.campaigns' );

				if ( $old_options !== $settings['mailchimp']['campaigns'] ) {
					// Delete all campaigns saved and recreate segments
					$this->delete_active_campaigns();

					$segments = $this->handle_segment_and_grouping( $this->get_option( 'mailchimp.terms' ) );
				}
			}

			if ( $segments ) {
				// Create RSS driven campaign using template and frequency specified
				$segments  = apply_filters( 'chimplet/campaigns/segments', $segments );
				$folder_id = $this->mc->get_campaign_folder_id( apply_filters( 'chimplet/campaigns/folder', 'Chimplet' ) );

				// Only one taxonomy for now
				// @todo what do we do with all other segments from other tax?
				$segments = array_shift( $segments );

				foreach ( $segments as $segment ) {
					// From core
					$sitename = strtolower( $_SERVER['SERVER_NAME'] ); //input var okay
					if ( 'www.' == substr( $sitename, 0, 4 ) ) {
						$sitename = substr( $sitename, 4 );
					}

					$campaign = [
						'type'    => apply_filters( 'chimplet/campaigns/type', 'rss' ),
						'options' => apply_filters( 'chimplet/campaigns/options', [
							'list_id'     => $list['id'],
							'subject'     => sprintf( __( 'Digest - %s', 'chimplet' ), $segment['conditions'][0]['value'] ),
							'from_email'  => apply_filters( 'wp_mail_from', 'chimplet@' . $sitename ), // xss ok
							'from_name'   => apply_filters( 'wp_mail_from_name', 'Chimplet' ),
							'template_id' => absint( $settings['mailchimp']['campaigns']['template'] ),
						] ),
						'content' => apply_filters( 'chimplet/campaigns/content', [
							'url' => apply_filters( 'chimplet/campaigns/rss/url', bloginfo( 'rss2_url' ) ),
						] ),
						'segment_opts' => $segment,
						'type_opts' => apply_filters( 'chimplet/campaigns/type/opts', [
							'rss' => [
								'url'      => apply_filters( 'chimplet/campaigns/rss/url', bloginfo( 'rss2_url' ) ),
								'schedule' => $settings['mailchimp']['campaigns']['frequency']
							]
						] ),
					];

					if ( is_int( $folder_id ) ) {
						$campaign['options']['folder_id'] = $folder_id;
					}

					$campaign = $this->mc->create_campaign( $campaign );

					if ( $campaign ) {
						$settings['mailchimp']['campaigns']['active'][] = $campaign['id'];
					}
				}
			}
		}

		return $settings;
	}

	/**
	 * Handle saving and sanitization related to taxonomy
	 * Since it's impossible to delete grouping that are used by campaign
	 * we need to handle the campaign deletion associated
	 *
	 * @param array $tax_to_save
	 * @return array|bool
	 */
	private function save_taxonomy_terms( &$tax_to_save ) {
		// For comparison purposes
		if ( ! $old_option = $this->get_option( 'mailchimp.terms' ) ) {
			$old_option = [];
		}

		// Sync taxonomy with MailChimp groups only if it didn't change
		if ( $tax_to_save !== $old_option ) {

			// Here we have some new terms so we need to delete previously set campaigns
			// otherwise we won't be able to delete any groups because of active campaigns
			$this->delete_active_campaigns();

			if ( ! empty( $tax_to_save )	) {

				// Computing the difference between old options grouping and what is being save
				foreach ( $old_option as $key => &$value ) { $value = []; }

				return $this->handle_segment_and_grouping( array_merge( $old_option, $tax_to_save ) );
			}
			else {

				foreach ( $old_option as $tax => $terms ) {

					$tax_label = get_taxonomy( $tax )->label;
					$this->mc->delete_grouping( $tax_label );

				}
			}
		}

		return false;
	}

	/**
	 * Create grouping and related segments
	 *
	 * @param $options
	 * @return array
	 */
	private function handle_segment_and_grouping( $options ) {

		$segments = [];

		foreach ( $options as $tax => $terms ) {

			// Use the tax label in mailchimp as it is cleaner
			$terms        = array_map( 'sanitize_text_field', $terms );
			$tax_label    = get_taxonomy( $tax )->label;
			$grouping     = $this->mc->get_grouping( $tax_label );
			$local_groups = [];

			foreach ( $terms as $term_id ) {

				if ( 'all' === $term_id ) {
					continue;
				}

				$term = $this->wp->get_term_by( 'id', $term_id, $tax );

				if ( $term ) {
					$local_groups[] = $term->name;
				}
			}

			// Add or update grouping
			$this->add_or_update_grouping(
				$local_groups,
				$grouping,
				$tax_label,
				$tax_to_save
			);

			// Add or update segments related to grouping
			// We cannot cross reference grouping. This might limit power set.
			$segments[] = $this->generate_segments( $local_groups, $grouping );

		}

		return $segments;
	}

	/**
	 * We established that we needed to clear the campaigns we created.
	 */
	private function delete_active_campaigns() {

		if ( $active_campaigns = $this->get_option( 'mailchimp.campaigns.active' ) ) {
			foreach ( $active_campaigns as $cid ) {
				$this->mc->delete_campaign( $cid );
			}
		}

	}

	/**
	 * Generate segments using groups and grouping
	 *
	 * @param $groups
	 * @param $grouping
	 * @return array
	 */
	private function generate_segments( $groups, $grouping ) {

		$segments = $this->generate_group_power_set( $groups );

		foreach ( $segments as &$segment ) {
			$diff = array_diff( $groups, $segment );

			// Here we setup some option for the segment
			$segment = [
				'match' => 'all',
				'conditions' => [
					[
						'field' => 'interests-' . $grouping['id'],
						'op'    => 'all',
						'value' => implode( ',', $segment ),
					]
				],
			];

			if ( count( $diff ) > 0 ) {

				$segment['conditions'][] = [
					'field' => 'interests-' . $grouping['id'],
					'op'    => 'none',
					'value' => implode( ',', $diff ),
				];

			}
		}

		return $segments;

	}

	/**
	 * Save user roles
	 *
	 * @param array $roles
	 */
	private function save_user_roles( &$roles ) {
		// For comparison purposes
		$old_option = $this->get_option( 'mailchimp.user_roles' );

		if ( ! $old_option ) {
			$old_option = [];
		}

		// User roles list fields
		if ( is_array( $roles ) && $roles !== $old_option ) {
			// Make sure we got a valid role from the role list
			$roles_to_save = array_diff( $roles, ['all'] );
			$roles_key     = array_keys( $this->wp->get_editable_roles() );
			$role_diff     = array_diff( $roles_to_save, $roles_key );

			if ( count( $role_diff ) > 0 ) {

				unset( $roles );

				$this->wp->add_settings_error(
					self::SETTINGS_KEY,
					'chimplet-invalid-user-roles',
					__( 'Impossible to save specified user roles', 'chimplet' ),
					'error'
				);

			}
			else {
				// Let's add the role as a list field if not already present
				$merge_var_options = [
					'field_type' => 'dropdown',
					'public'     => false,
					'show'       => false,
					'req'        => true,
					'choices'    => $roles_to_save
				];

				$success = $this->mc->handle_merge_var_integrity(
					self::USER_ROLE_MERGE_VAR,
					'WordPress role',
					$merge_var_options
				);

				if ( ! $success ) {

					unset( $roles );

					$this->wp->add_settings_error(
						self::SETTINGS_KEY,
						'chimplet-user-roles-sync-problem',
						__( 'Impossible to save user roles with MailChimp merge fields', 'chimplet' ),
						'error'
					);

				}
			}
		}
	}

	/**
	 * Helping function that handles the logic to update or add a grouping
	 *
	 * @param array $local_groups
	 * @param array $grouping
	 * @param string $grouping_name
	 * @param mixed $to_unset
	 * @param string $group_type
	 */
	private function add_or_update_grouping( $local_groups, $grouping, $grouping_name, &$to_unset, $group_type = 'checkboxes' ) {
		if ( empty( $local_groups ) ) {

			$this->mc->delete_grouping( $grouping_name );

			return;

		}

		if ( $grouping ) {

			$this->mc->handle_grouping_integrity( $local_groups, $grouping['groups'], $grouping['id'] );

		}
		else {
			// Create new grouping with default groups
			$grouping_id = $this->mc->add_grouping( $grouping_name, $group_type, $local_groups );

			if ( ! $grouping_id ) {

				unset( $to_unset );

			}
		}
	}

	/**
	 * Generate all combination for segments using power set algorithm
	 *
	 * @param array $array
	 * @return array
	 */
	private function generate_group_power_set( $array ) {
		$results = [ [] ];

		foreach ( $array as $element ) {

			foreach ( $results as $combination ) {

				array_push( $results, array_merge( [ $element ], $combination ) );

			}
		}

		// Removing the empty array of the beginning
		array_shift( $results );

		return $results;
	}

	/**
	 * Add pages to the WordPress administration menu
	 *
	 * @used-by Action: admin_menu
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-07)
	 */

	public function append_to_menu()
	{
		$this->hook = $this->wp->add_submenu_page(
			$this->get_menu_slug( 'overview' ),
			$this->view['document_title'],
			$this->view['menu_title'],
			apply_filters( 'chimplet/manage/capability', 'manage_options' ),
			$this->view['menu_slug'],
			[ $this, 'render_page' ]
		);

		$this->wp->add_action( "load-{$this->hook}", [ $this, 'load_page' ] );
	}

	/**
	 * Display the Settings Page
	 *
	 * @used-by Function: add_menu_page
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-07)
	 */

	public function render_page()
	{
		$this->view['settings_group'] = self::SETTINGS_KEY;

		$this->render_view( 'options-settings', $this->view );
	}

	/**
	 * Display the "Save Changes" button
	 *
	 * @version 2015-03-03
	 * @since   0.0.0 (2015-03-03)
	 *
	 * @param array $args
	 */

	public function render_submit_button( $args = [] )
	{
		$defaults = [
			'text' => ( $this->get_option( 'mailchimp.valid' ) ? null : __( 'Save API Key', 'chimplet' ) )
		];

		$args = wp_parse_args( $args, $defaults );

		call_user_func_array( [ $this->wp, 'submit_button' ], $args );
	}

	/**
	 * Display the MailChimp API Section
	 *
	 * @used-by Function: add_settings_section
	 * @version 2015-03-03
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_section( $args )
	{
		$this->render_section( 'settings-mailchimp', $this->get_options() );
	}

	/**
	 * Display the API Key Settings Field
	 *
	 * @used-by Function: add_settings_field
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_field_api_key_section( $args )
	{
		$value = $this->get_option( 'mailchimp.api_key' );

		printf(
			'<input type="text" class="regular-text" id="%s" name="chimplet[mailchimp][api_key]" value="%s"/>',
			esc_attr( $args['label_for'] ),
			esc_attr( $value )
		);
	}

	/**
	 * Display the Subscriber List Settings Field
	 *
	 * @used-by Function: add_settings_field
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_field_list_section( $args )
	{
		$this->render_section( 'settings-list', $args );
	}

	/**
	 * Display a terms from all taxonomies
	 *
	 * @used-by Function: add_settings_field
	 * @version 2015-02-13
	 * @since   0.0.0 (2015-02-11)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_field_terms_section( $args )
	{
		$this->render_section( 'settings-terms', $args );
	}

	/**
	 * Render user groups that needs to be synchonize with MailChimp
	 *
	 * @access public
	 * @param $args
	 *
	 * @return void
	 */

	public function render_mailchimp_field_user_roles_section( $args )
	{
		$this->render_section( 'settings-user-roles', $args );
	}

	/**
	 * Render campaigns automation setting
	 *
	 * @todo Link to more explanation
	 * @param array $args
	 */

	public function render_mailchimp_field_campaign_automation( $args )
	{
		$options = $this->get_option( 'mailchimp.campaigns', [] );

		$match = ( empty( $options['automate'] ) || ! is_array( $options ) ) ? false : array_key_exists( 'automate', $options );

		echo '<fieldset>';

		$field  = '<label for="%1$s">';
		$field .= '<input type="checkbox" id="%1$s" name="%2$s" value="%3$s"' . checked( $match, true, false ) . '/>' . ' ';
		$field .= '<span>%4$s</span>';
		$field .= '</label>';

		printf(
			$field,
			esc_attr( $args['label_for'] ),
			esc_attr( 'chimplet[mailchimp][campaigns][automate]' ),
			esc_attr( 'on' ),
			esc_html__( 'Automate creation of Campaigns', 'chimplet' )
		);

		echo '<p class="description">' . esc_html__( 'Chimplet can automate the creation of RSS Campaigns using power sets of interest groupings.', 'chimplet' ) . '</p>';
		echo '</fieldset>';
	}

	/**
	 * Render campaign scheduling settings
	 *
	 * @access public
	 * @param $args
	 *
	 * @return void
	 */

	public function render_mailchimp_field_campaign_schedule( $args )
	{
		$this->render_field( 'settings-schedule', $args );
	}

	/**
	 * Render campaign templating settings
	 *
	 * @access public
	 * @param $args
	 *
	 * @return void
	 */

	public function render_mailchimp_field_campaign_template( $args )
	{
		$this->render_field( 'settings-template', $args );
	}

	/**
	 * Display error message or a fallback if there isn't one
	 *
	 * @version 2015-03-03
	 * @access private
	 * @param $message
	 * @param $fallback_message
	 */
	private function display_inline_error( $message, $fallback_message )
	{
		if ( $message ) {
			printf( '<p class="chimplet-alert alert-warning">%s</p>', esc_html( $message ) );
		} else {
			printf( '<p class="chimplet-alert alert-error">%s</p>', esc_html( $fallback_message ) );
		}
	}

}
