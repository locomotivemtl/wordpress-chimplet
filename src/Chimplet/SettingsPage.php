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

		$this->excluded_post_types = apply_filters( 'chimplet/excluded_post_types', [ 'page', 'revision', 'nav_menu_item' ] );
		$this->excluded_taxonomies = apply_filters( 'chimplet/excluded_taxonomies', [ 'post_format', 'nav_menu' ] );

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
						[
							'before' => ( $this->get_option( 'mailchimp.campaigns.automate' ) ? [ $this, 'render_mailchimp_campaigns_section' ] : null )
						],
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
	 * @version 2015-03-06
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
			[ $this, 'render_mailchimp_field_api_key' ],
			$this->view['menu_slug'],
			'chimplet-section-mailchimp-api',
			[
				'label_for' => 'chimplet-field-mailchimp-api_key'
			]
		);

		// Add these fields when the API Key is integrated
		if ( ! $this->get_option( 'mailchimp.valid' ) ) {
			return;
		}

		$this->wp->add_settings_field(
			'chimplet-field-mailchimp-lists',
			__( 'Select Mailing List', 'chimplet' ),
			[ $this, 'render_mailchimp_field_list' ],
			$this->view['menu_slug'],
			'chimplet-section-mailchimp-lists',
			[
				'layout'   => 'custom',
				'control'  => 'radio-table' // Choices: select, radio-table
			]
		);

		// Add these fields when the List is selected
		if ( ! $list = $this->get_option( 'mailchimp.list' ) ) {
			return;
		}

		$list = $this->mc->get_list_by_id( $list );

		if ( $list instanceof \Mailchimp_Error ) {
			return;
		}

		$this->wp->add_settings_field(
			'chimplet-field-mailchimp-categories',
			__( 'Select Taxonomy Terms', 'chimplet' ),
			[ $this, 'render_mailchimp_field_terms' ],
			$this->view['menu_slug'],
			'chimplet-section-mailchimp-lists',
			[ 'list' => $list ]
		);

		$this->wp->add_settings_field(
			'chimplet-field-mailchimp-user-roles',
			__( 'Select User Roles', 'chimplet' ),
			[ $this, 'render_mailchimp_field_user_roles' ],
			$this->view['menu_slug'],
			'chimplet-section-mailchimp-lists',
			[ 'list' => $list ]
		);

		if ( $this->get_option( 'mailchimp.user_roles' ) ) {
			$user_query = $this->app->get_wp_users();

			$this->wp->add_settings_field(
				'chimplet-field-mailchimp-subscribers-automate',
				__( 'Subcribers', 'chimplet' ),
				[ $this, 'render_mailchimp_field_automation' ],
				$this->view['menu_slug'],
				'chimplet-section-mailchimp-lists',
				[
					'chimplet_option' => 'mailchimp.subscribers',
					'xhr_action'      => 'chimplet/subscribers/sync',
					'xhr_nonce'       => wp_create_nonce( 'chimplet-subscribers-sync' ),
					'input_name'      => 'chimplet[mailchimp][subscribers][automate]',
					'input_attr'      => ' data-condition-key="chimplet-subscribers-sync"',
					'label_for'       => 'chimplet-field-mailchimp-subscribers-automate',
					'label_text'      => __( 'Automate subscribers synchronization', 'chimplet' ),
					'button_id'       => 'chimplet-field-mailchimp-subscribers-sync',
					'button_text'     => __( 'Synchronize Subscribers', 'chimplet' ),
					'button_attr'     => ' data-condition-chimplet-subscribers-sync="on"',
					'counter_label'   => __( 'Eligible WordPress Users: %d', 'chimplet' ),
					'counter_value'   => $user_query->get_total(),
					'description'     => __( 'Chimplet can automatically sync subscribers of the above user roles with the MailChimp list selected.', 'chimplet' )
				]
			);
		}

		$campaign_opts = $this->get_option( 'mailchimp.campaigns' );

		$this->wp->add_settings_field(
			'chimplet-field-mailchimp-campaign-automate',
			__( 'Automation', 'chimplet' ),
			[ $this, 'render_mailchimp_field_automation' ],
			$this->view['menu_slug'],
			'chimplet-section-mailchimp-campaigns',
			[
				'chimplet_option'  => 'mailchimp.campaigns',
				'xhr_action'       => 'chimplet/campaigns/sync',
				'xhr_nonce'        => wp_create_nonce( 'chimplet-campaigns-sync' ),
				'input_name'       => 'chimplet[mailchimp][campaigns][automate]',
				'input_attr'      => ' data-condition-key="chimplet-campaigns-sync"',
				'label_for'        => 'chimplet-field-mailchimp-campaigns-automate',
				'label_text'       => __( 'Automate creation of Campaigns', 'chimplet' ),
				'button_id'        => 'chimplet-field-mailchimp-campaigns-sync',
				'button_text'      => __( 'Synchronize Campaigns', 'chimplet' ),
				'button_condition' => $this->get_option( 'mailchimp.campaigns.schedule' ),
				'button_attr'      => ' data-condition-chimplet-campaigns-sync="on"' .
									  ' data-condition-frequency="' . esc_attr( $this->get_option( 'mailchimp.campaigns.schedule.frequency', '' ) ) . '"' .
									  ( ! $this->get_option( 'mailchimp.campaigns.schedule.weekday' )  ? '' : ' data-condition-weekday="'   . esc_attr( $this->get_option( 'mailchimp.campaigns.schedule.weekday', '' ) ) . '"' ) .
									  ( ! $this->get_option( 'mailchimp.campaigns.schedule.monthday' ) ? '' : ' data-condition-monthday="'  . esc_attr( $this->get_option( 'mailchimp.campaigns.schedule.monthday', '' ) ) . '"' ) .
									  ( ! $this->get_option( 'mailchimp.campaigns.schedule.days' )     ? '' : ' data-condition-days="'      . esc_attr( implode( ',', $this->get_option( 'mailchimp.campaigns.schedule.days', '' ) ) ) . '"' ) .
									  ' data-condition-hour="'      . esc_attr( $this->get_option( 'mailchimp.campaigns.schedule.hour', '' ) ) . '"' .
									  ' data-condition-template="'  . esc_attr( $this->get_option( 'mailchimp.campaigns.template', '' ) ) . '"',
				'counter_label'    => __( 'Generated Campaigns: %d', 'chimplet' ),
				'counter_value'    => count( $this->get_option( 'mailchimp.campaigns.active', [] ) ),
				'description'      => __( 'Chimplet can automate the creation of RSS Campaigns using power sets of interest groupings (Maximum of 32,000 campaigns per account).', 'chimplet' )
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

		$segmented_taxonomies = [];

		// Do we have any terms?
		if ( isset( $settings['mailchimp']['terms'] ) ) {
			$settings['mailchimp']['terms'] = $this->clean_terms( $settings['mailchimp']['terms'] );

			if ( $this->get_option( 'mailchimp.terms' ) !== $settings['mailchimp']['terms'] ) {
				$this->update_terms( $settings['mailchimp']['terms'], $segmented_taxonomies );
			}
		}

		if ( isset( $settings['mailchimp']['user_roles'] ) ) {
			if ( $this->get_option( 'mailchimp.user_roles' ) !== $settings['mailchimp']['user_roles'] ) {
				$this->save_user_roles( $settings['mailchimp']['user_roles'] );
			}
		}

		// We have new segments we add a corresponding campaigns
		if ( isset( $settings['mailchimp']['campaigns']['automate'] ) ) {
			// Merge old settings
			if ( $options = $this->get_option( 'mailchimp.campaigns.active' ) ) {
				$settings['mailchimp']['campaigns']['active'] = $options;
			}

			if ( $options = $this->get_option( 'mailchimp.campaigns.trashed' ) ) {
				$settings['mailchimp']['campaigns']['trashed'] = $options;
			}

			// Create RSS driven campaign using template and frequency specified
			$folder_name = $this->wp->apply_filters( 'chimplet/campaign/folder_name', 'Chimplet' );
			$folder_id = $this->mc->get_campaign_folder_id( $folder_name );

			if ( isset( $settings['mailchimp']['campaigns']['schedule'] ) ) {
				$schedule = $settings['mailchimp']['campaigns']['schedule'];

				if ( isset( $schedule['frequency'] ) ) {
					switch ( $schedule['frequency'] ) {
						case 'daily':
							unset( $schedule['monthday'], $schedule['weekday'] );
							$schedule['days'] = array_map( 'intval', $schedule['days'] );
							break;

						case 'weekly':
							unset( $schedule['monthday'], $schedule['days'] );
							$schedule['weekday'] = absint( $schedule['weekday'] );
							break;

						case 'monthly':
							unset( $schedule['weekday'], $schedule['days'] );
							$schedule['monthday'] = intval( $schedule['monthday'] );
							break;

						default:
							$this->wp->add_settings_error(
								self::SETTINGS_KEY,
								'mailchimp-shedule-frequency-failed',
								sprintf( __( 'Invalid schedule frequency.' ) ),
								'error'
							);
							break;
					}
				}

				if ( isset( $schedule['hour'] ) ) {
					$schedule['hour'] = absint( $schedule['hour'] );
				}
				else {
					$this->wp->add_settings_error(
						self::SETTINGS_KEY,
						'mailchimp-shedule-hour-failed',
						sprintf( __( 'Invalid schedule hour.' ) ),
						'error'
					);
				}

				$settings['mailchimp']['campaigns']['schedule'] = $schedule;

				$this->wp->flush_rewrite_rules();
			}
		}
		else {
			unset( $settings['mailchimp']['campaigns']['schedule'] );
		}

		return $settings;
	}

	/**
	 * Remove terms that don't exist from an array
	 *
	 * Remove terms that might have ceased to exist between the time
	 * the Settings page was loaded and the moment it's submitted
	 * values are being processed.
	 *
	 * @param  array   $arr       The terms to check
	 * @param  string  $taxonomy  If provided, {@see $terms} is assumed to be a single-dimension array of term IDs.
	 *                            If omitted, {@see $terms} is assumed to be a multi-dimensional array of taxonomy slugs and term IDs.
	 * @return array
	 */

	public function clean_terms( array $arr, $taxonomy = '' )
	{
		if ( empty( $taxonomy ) ) {
			foreach ( $arr as $tax_name => &$terms ) {
				$this->_clean_terms( $terms, $tax_name );
			}
		}
		else {
			$this->_clean_terms( $arr, $taxonomy );
		}

		return $arr;
	}

	/**
	 * Private function executing the core removal process.
	 *
	 * @see SettingsPage\clean_terms()
	 * @param array $terms The terms to check
	 * @param string $taxonomy The taxonomy name to use
	 */

	private function _clean_terms( array &$arr, $taxonomy = '' )
	{
		if ( taxonomy_exists( $taxonomy ) ) {
			foreach ( $arr as $i => $term ) {
				$term_id = term_exists( (int) $term, $taxonomy );

				if ( 0 === $term_id || null === $term_id ) {
					unset( $arr[ $i ] );
				}
			}

			$arr = array_values( $arr );
		}
		else {
			$arr = [];
		}
	}

	/**
	 * We established that we needed to clear the campaigns we created.
	 */

	public function delete_active_campaigns()
	{
		$options = $this->get_options();

		if ( empty( $options['mailchimp']['campaigns']['active'] ) ) {
			return;
		}

		$active_campaigns = $options['mailchimp']['campaigns']['active'];

		if ( ! isset( $options['mailchimp']['campaigns']['trashed'] ) ) {
			$options['mailchimp']['campaigns']['trashed'] = [];
		}

		if ( ! isset( $options['mailchimp']['campaigns']['trashed']['paused'] ) ) {
			$options['mailchimp']['campaigns']['trashed']['paused'] = [];
		}

		if ( ! isset( $options['mailchimp']['campaigns']['trashed']['deleted'] ) ) {
			$options['mailchimp']['campaigns']['trashed']['deleted'] = [];
		}

		if ( ! isset( $options['mailchimp']['campaigns']['trashed']['failed'] ) ) {
			$options['mailchimp']['campaigns']['trashed']['failed'] = [];
		}

		foreach ( $active_campaigns as $i => $cid ) {
			try {
				if ( $this->mc->campaigns->pause( $cid ) ) {
					if ( $this->mc->campaigns->delete( $cid ) ) {
						$options['mailchimp']['campaigns']['trashed']['deleted'][] = $cid;
					}
					else {
						$options['mailchimp']['campaigns']['trashed']['paused'][] = $cid;
					}
				}
				else {
					$options['mailchimp']['campaigns']['trashed']['failed'][] = $cid;
				}
			}
			catch ( \Mailchimp_Campaign_DoesNotExist $e ) {
				$options['mailchimp']['campaigns']['trashed']['deleted'][] = $cid;
			}
			catch ( \Mailchimp_Error $e ) {
				$options['mailchimp']['campaigns']['trashed']['failed'][] = $cid;
			}

			unset( $options['mailchimp']['campaigns']['active'][ $i ] );
		}

		if ( empty( $options['mailchimp']['campaigns']['trashed']['paused'] ) ) {
			unset( $options['mailchimp']['campaigns']['trashed']['paused'] );
		}

		if ( empty( $options['mailchimp']['campaigns']['trashed']['deleted'] ) ) {
			unset( $options['mailchimp']['campaigns']['trashed']['deleted'] );
		}

		if ( empty( $options['mailchimp']['campaigns']['trashed']['failed'] ) ) {
			unset( $options['mailchimp']['campaigns']['trashed']['failed'] );
		}

		if ( empty( $options['mailchimp']['campaigns']['trashed'] ) ) {
			unset( $options['mailchimp']['campaigns']['trashed'] );
		}

		if ( empty( $options['mailchimp']['campaigns']['active'] ) ) {
			unset( $options['mailchimp']['campaigns']['active'] );
		}

		$this->update_options( $options );
	}

	/**
	 * Handle saving and sanitization related to taxonomy
	 * Since it's impossible to delete grouping that are used by campaign
	 * we need to handle the campaign deletion associated
	 *
	 * @param array  $taxonomies_and_terms  A list of taxonomies and terms.
	 * @param array  $matches               If provided, it is filled with the groups and groupings from {@see $taxonomies_and_terms}
	 *                                      and the sets of combinations of interests as MailChimp segments.
	 */

	private function update_terms( &$taxonomies_and_terms, &$matches = [] )
	{
		// For comparison purposes
		$old_option = $this->get_option( 'mailchimp.terms', [] );

		// Sync taxonomy with MailChimp groups only if it didn't change
		if ( $taxonomies_and_terms !== $old_option ) {
			// Here we have some new terms so we need to delete previously set campaigns
			// otherwise we won't be able to delete any groups because of active campaigns
			$this->delete_active_campaigns();

			if ( ! empty( $taxonomies_and_terms ) ) {
				// Computing the difference between old options grouping and what is being save
				foreach ( $old_option as $key => &$value ) {
					$value = [];
				}

				$matches = $this->handle_segments_and_groupings( array_merge( $old_option, $taxonomies_and_terms ) );
			}
			else {
				foreach ( $old_option as $tax => $terms ) {
					$tax_label = get_taxonomy( $tax )->label;
					$this->mc->delete_grouping( $tax_label );
				}
			}
		}
	}

	/**
	 * Retrieve grouping and related segments
	 *
	 * @param array $segmented_taxonomies
	 * @return int
	 */

	public function get_segment_total( $segmented_taxonomies )
	{
		$c = 0;

		array_walk( $segmented_taxonomies, function ( $v, $i ) use ( &$c ) {
			if ( isset( $v['segments'] ) && is_array( $v['segments'] ) ) {
				$c = $c + count( $v['segments'] );
			}
		} );

		return $c;
	}

	/**
	 * Retrieve grouping and related segments
	 *
	 * @param array $taxonomies_and_terms
	 * @return array
	 */

	public function get_segments_and_groupings( $taxonomies_and_terms = null )
	{
		if ( null === $taxonomies_and_terms ) {
			$taxonomies_and_terms = $this->get_option( 'mailchimp.terms' );
		}

		return $this->handle_segments_and_groupings( $taxonomies_and_terms, false );
	}

	/**
	 * Create grouping and related segments
	 *
	 * @param array $taxonomies_and_terms
	 * @return array
	 */

	private function handle_segments_and_groupings( $taxonomies_and_terms, $push_updates = true )
	{
		$taxonomy_sets = [];

		foreach ( $taxonomies_and_terms as $taxonomy => $terms ) {
			// Only one taxonomy for now
			// @todo what do we do with all other segments from other tax?
			if ( 'category' !== $taxonomy ) {
				continue;
			}

			// Use the taxonomy label in mailchimp as it is cleaner
			$terms     = array_map( 'sanitize_text_field', $terms );
			$tax_obj   = get_taxonomy( $taxonomy );
			$tax_label = $tax_obj->label;
			$grouping  = $this->mc->get_grouping( $tax_label );
			$groups    = [];
			$couples   = [];

			foreach ( $terms as $term_id ) {
				if ( 'all' === $term_id || empty( $term_id ) ) {
					continue;
				}

				$term = $this->wp->get_term_by( 'id', $term_id, $taxonomy );

				if ( $term ) {
					$couples[] = [
						'term'  => $term_id,    # "wp"
						'group' => $term->name  # "mc"
					];

					$groups[] = $term->name;
				}
			}

			natsort( $groups );
			$groups = array_values( $groups );

			// Add, remove, or update grouping
			if ( $push_updates ) {
				$this->update_grouping(
					$groups,
					$grouping,
					$tax_label
				);
			}

			// Add or update segments related to grouping
			// We cannot cross reference grouping. This might limit power set.
			$segments = $this->generate_segments_from_terms( $grouping, $taxonomy, $terms );

			if ( is_array( $segments ) && ! empty( $segments ) ) {
				$taxonomy_sets[ $taxonomy ] = [
					'grouping' => $grouping,
					'couples'  => $couples,
					'segments' => $segments
				];
			}
		}

		return $this->wp->apply_filters( 'chimplet/taxonomies/segments', $taxonomy_sets, $taxonomies_and_terms );
	}

	/**
	 * Generate segments using groups and grouping
	 *
	 * @param   array $grouping
	 * @param   array $taxonomy
	 * @param   array terms
	 * @return  array $segments {
	 *     An array of segments. Each segment contains:
	 *
	 *     @type  string      $taxonomy  The related taxonomy name.
	 *     @type  int|string  $grouping  The related grouping ID.
	 *     @type  array       $terms     The combination of term IDs related to this segment.
	 *     @type  array       $rules     {
	 *         The segmentation rules.
	 *
	 *         @type  string  $match       Controls whether to use AND or OR when applying your options - expects "any" (for OR) or "all" (for AND)
	 *         @type  string  $conditions  {
	 *             Up to 5 structs for different criteria to apply while segmenting.
	 *             Each criteria row must contain 3 keys (possibly 4):
	 *
	 *             @type  string  $field  Required.
	 *             @type  string  $op     Required. Operator.
	 *             @type  mixed   $value  Required.
	 *             @type  mixed   $extra  Optional.
	 *         }
	 *     }
	 * }
	 */

	private function generate_segments_from_terms( $grouping, $taxonomy, $terms )
	{
		$group_combos = array_power_set( $terms );

		$segments = [];

		if ( is_array( $grouping ) ) {
			$grouping_id = $grouping['id'];
		}
		else if ( is_numeric( $grouping ) ) {
			$grouping_id = $grouping;
		}
		else {
			return $segments;
		}

		foreach ( $group_combos as $combination ) {
			$diff = array_diff( $terms, $combination );

			$combo_terms = get_terms( $taxonomy, [
				'hide_empty' => false,
				'fields'     => 'names',
				'include'    => $combination
			] );

			$diff_terms = get_terms( $taxonomy, [
				'hide_empty' => false,
				'fields'     => 'names',
				'include'    => $diff
			] );

			if ( is_wp_error( $combo_terms ) || empty( $combo_terms ) || is_wp_error( $diff_terms ) || empty( $diff_terms ) ) {
				continue;
			}

			// Here we setup some option for the segment
			$rules = [
				'match' => 'all',
				'conditions' => []
			];

			$rules['conditions'][] = [
				'field' => 'interests-' . $grouping_id,
				'op'    => 'all',
				'value' => implode( ',', $combo_terms )
			];

			if ( count( $diff ) > 0 ) {
				$rules['conditions'][] = [
					'field' => 'interests-' . $grouping_id,
					'op'    => 'none',
					'value' => implode( ',', $diff_terms )
				];
			}

			$segments[] = [
				'taxonomy' => $taxonomy,
				'grouping' => $grouping_id,
				'terms'    => $combination,
				'rules'    => $rules
			];
		}

		return $segments;
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

	private function update_grouping( $local_groups, $grouping, $grouping_name, &$to_unset = null, $group_type = 'checkboxes' )
	{
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
	 * Save user roles
	 *
	 * @param array $roles
	 */

	private function save_user_roles( &$roles )
	{
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
					__( 'WordPress Role', 'chimplet' ),
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
	 * Add pages to the WordPress administration menu
	 *
	 * @used-by Action: admin_menu
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-07)
	 */

	public function append_to_menu()
	{
		parent::{ __FUNCTION__ }();

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
		if ( $this->get_option( 'mailchimp.api_key' ) ) {
			?>
			<p><?php esc_html_e( 'With an integrated API Key, additional options are provided below.', 'chimplet' ); ?></p>
			<p><?php esc_html_e( 'Removing the API Key will disable Chimplet’s data synchronization features and no longer provide it access to your account to manage your subscribers and campaigns. Disabling Chimplet does not delete any data from MailChimp nor does it disable Post Category feeds and the active RSS-Driven Campaigns.', 'chimplet' ); ?></p>
			<?php
		}
		else {
			?>
			<p><?php _e( 'To integrate your blog with your MailChimp account, you need to generate an API key.', 'chimplet' ); ?></p>
			<aside class="panel-assistance inset">
				<p><?php
					printf(
						esc_html__( 'Users with Admin or Manager permissions can generate and view API keys. You can %s from your Account Panel.', 'chimplet' ),
						'<a target="_blank" href="' . '//kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key' . '">' . esc_html__( 'find or generate an API key', 'chimplet' ) . '</a>'
					);
					?></p>
				<ol>
					<li><?php printf( esc_html__( 'Click your profile name to expand the Account Panel, and choose %1$s.', 'chimplet' ), '<em>' . __( 'Account', 'chimplet' ) . '</em>' ); ?></li>
					<li><?php printf( esc_html__( 'Click the %1$s drop-down menu and choose %2$s.', 'chimplet' ), '<em>' . esc_html__( 'Extras', 'chimplet' ) . '</em>', '<em>' . __( 'API keys', 'chimplet' ) . '</em>' ); ?></li>
					<li><?php printf( esc_html__( 'Copy an existing API key or click the %1$s button.', 'chimplet' ), '<em>' . esc_html__( 'Create A Key', 'chimplet' ) . '</em>' ); ?></li>
					<li><?php esc_html_e( 'Name your key descriptively, so you know what application uses that key.', 'chimplet' ); ?></li>
				</ol>
			</aside>
			<p><?php esc_html_e( 'Once the API Key is integrated with Chimplet, you will be provided with additional options.', 'chimplet' ); ?></p>
			<?php
		}
	}

	/**
	 * Display the API Key Settings Field
	 *
	 * @used-by Function: add_settings_field
	 * @version 2015-03-06
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_field_api_key( $args )
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
	 * @version 2015-03-06
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_field_list( $args )
	{
		$this->render_field( 'settings-list', $args );
	}

	/**
	 * Display a terms from all taxonomies
	 *
	 * @used-by Function: add_settings_field
	 * @version 2015-03-06
	 * @since   0.0.0 (2015-02-11)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_field_terms( $args )
	{
		$this->render_field( 'settings-terms', $args );
	}

	/**
	 * Render user groups that needs to be synchonize with MailChimp
	 *
	 * @access public
	 * @param $args
	 *
	 * @return void
	 */

	public function render_mailchimp_field_user_roles( $args )
	{
		$this->render_field( 'settings-user-roles', $args );
	}

	/**
	 * Display the MailChimp Campaigns Section
	 *
	 * @used-by Function: add_settings_section
	 * @since   0.0.0 (2015-04-14)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_campaigns_section( $args )
	{
		?>
		<p><?php esc_html_e( 'Any changes related to Groupings (taxonomies and terms) that are saved will delete all Campaigns created by Chimplet. They must be re-generated manually, below. The syncing process is separate from the principal saving of settings to reduce page load times.', 'chimplet' ); ?></p>
		<?php
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
	 * Render sync setting
	 *
	 * @access public
	 * @param $args
	 *
	 * @return void
	 */

	public function render_mailchimp_field_automation( $args )
	{
		$this->render_field( 'settings-automate', $args );
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
		printf( '<p class="chimplet-alert alert-warning">%s</p>', esc_html( $message ?: $fallback_message ) );
	}

}
