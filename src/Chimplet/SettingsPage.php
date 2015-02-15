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
	 * Register settings, sections, and fields
	 *
	 * @used-by Action: "admin_init"
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-09)
	 */

	public function register_settings()
	{
		if ( false === $this->wp->get_option( 'chimplet' ) ) {
			$this->wp->update_option( 'chimplet', [] );
		}

		$this->wp->register_setting(
			self::SETTINGS_KEY,
			self::SETTINGS_KEY,
			[ $this, 'sanitize_settings' ]
		);

		$this->wp->add_settings_section(
			'chimplet-section-mailchimp-api',
			null,
			[ $this, 'render_mailchimp_section' ],
			$this->view['menu_slug']
		);

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
		if ( $this->get_option( 'mailchimp.valid' ) ) {

			$this->wp->add_settings_field(
				'chimplet-field-mailchimp-lists',
				__( 'Select Mailing List', 'chimplet' ),
				[ $this, 'render_mailchimp_field_list' ],
				$this->view['menu_slug'],
				'chimplet-section-mailchimp-api',
				[
					'control' => 'radio-table' // Choices: select, radio-table
				]
			);

			// Add these fields when the List is selected
			if ( $this->get_option( 'mailchimp.list' ) ) {

				$this->wp->add_settings_field(
					'chimplet-field-mailchimp-categories',
					__( 'Select Taxonomy Terms', 'chimplet' ),
					[ $this, 'render_mailchimp_field_terms' ],
					$this->view['menu_slug'],
					'chimplet-section-mailchimp-api'
				);

			}
		}
	}

	/**
	 * Sanitize setting values and validate MailChimp key
	 *
	 * @uses    Filter: "sanitize_option_{$option_name}"
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 * @todo    Save the validity of the API key or API status
	 *          as its own setting for easier testing.
	 *
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
					'api-key-failed',
					sprintf( __( 'Invalid MailChimp API Key: %s' ), $settings['mailchimp']['api_key'] ),
					'error'
				);

				$settings['mailchimp']['valid'] = false;
			}
		}

		return $settings;
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
		$this->view['button_label']   = ( $this->get_option( 'mailchimp.valid' ) ? null : __( 'Save API Key', 'chimplet' ) );
		$this->render_view( 'options-settings', $this->view );
	}

	/**
	 * Display the MailChimp API Settings Section
	 *
	 * @used-by Function: add_settings_section
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_section( $args )
	{
		$options = $this->get_options();

		if ( empty( $options['mailchimp']['api_key'] ) ) {
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
		else {
			?>
			<p><?php esc_html_e( 'With an integrated API Key, additional options are provided below.', 'chimplet' ); ?></p>
			<p><?php esc_html_e( 'Removing the API Key will disable Chimplet’s data synchronization features and no longer provides access to your account to manage your subscribers and campaigns. This does not delete any data from your MailChimp nor does it disable Post Category feeds and the active RSS-Driven Campaigns.', 'chimplet' ); ?></p>
		<?php
		}

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
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_field_list( $args )
	{
		$value = $this->get_option( 'mailchimp.list' );

		try {

			$lists = $this->mc->lists->getList();

		} catch ( \Mailchimp_Error $e ) {

			if ( $e->getMessage() ) {
				echo '<p class="chimplet-alert alert-error">' . esc_html( $e->getMessage() ) . '</p>';
			} else {
				echo '<p class="chimplet-alert alert-error">' . esc_html__( 'An unknown error occurred while fetching the Mailing Lists from your account.', 'chimplet' ) . '</p>';
			}
		}

		if ( empty( $value ) ) {
			$value = $readonly = '';
		}
		else {
			$value = esc_attr( $value );
			$readonly = ' readonly';
		}

		$readonly = '';
		$selected = '';

		if ( ! empty( $lists['data'] ) ) {

			if ( ! isset( $args['control'] ) ) {
				$args['control'] = 'radio-table';
			}

			if ( 'select' === $args['control'] ) {

				printf(
					'<select name="list" id="%s" name="chimplet[mailchimp][list]" %s>',
					esc_attr( $args['label_for'] ),
					esc_attr( $readonly )
				);

				foreach ( $lists['data'] as $list ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $list['id'] ),
						selected( $value, $list['id'] ),
						esc_html( $list['name'] )
					);
				}

				echo '</select>';

			}
			else {
?>
					</td>
				</tr>
			</table>
			<table class="wp-list-table widefat mailchimp-lists">
				<thead>
					<tr>
						<th scope="col" id="chimplet-rb" class="manage-column column-rb check-column"><label class="screen-reader-text"><?php esc_html_e( 'Select One', 'chimplet' ); ?></label></th>
						<th scope="col" id="mailchimp-list-title" class="manage-column column-name"><?php esc_html_e( 'Title' ); ?></th>
						<th scope="col" id="mailchimp-list-groups" class="manage-column column-groups num"><?php esc_html_e( 'Groupings', 'chimplet' ); ?></th>
						<th scope="col" id="mailchimp-list-members" class="manage-column column-members num"><?php esc_html_e( 'Members', 'chimplet' ); ?></th>
						<th scope="col" id="mailchimp-list-rating" class="manage-column column-rating num"><?php esc_html_e( 'Rating' ); ?></th>
						<th scope="col" id="mailchimp-list-date" class="manage-column column-date"><?php esc_html_e( 'Date Created', 'chimplet' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$i = 0;
				foreach ( $lists['data'] as $list ) :
					$select_label = sprintf( __( 'Select %s' ), '&ldquo;' . $list['name'] . '&rdquo;' ); //xss ok
					$id = 'rb-select-' . $list['id'];
				?>
					<tr id="mailchimp-list-<?php echo esc_attr( $list['id'] ); ?>" class="mailchimp-list-<?php echo esc_attr( $list['id'] ); ?> mailchimp-list<?php echo ( $i % 2 === 0 ? ' alternate' : '' ); //xss ok ?>">
						<th scope="row" class="check-column">
							<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $select_label ); ?></label>
							<input type="radio" id="<?php echo esc_attr( $id ); ?>" name="chimplet[mailchimp][list]" value="<?php echo esc_attr( $list['id'] ); ?>"<?php echo checked( $value, $list['id'] ); ?> />
						</th>
						<td class="column-title">
							<strong><label for="<?php echo esc_attr( $id ); ?>" title="<?php echo esc_attr( $select_label ); ?>"><?php echo esc_html( $list['name'] ); ?></label></strong>
						</td>
						<td class="column-groupings num"><?php echo esc_html( $list['stats']['grouping_count'] ); ?></td>
						<td class="column-members num"><?php echo esc_html( $list['stats']['member_count'] ); ?></td>
						<td class="column-rating num"><?php echo esc_html( $list['list_rating'] ); ?></td>
						<td class="column-date"><time datetime="<?php echo esc_attr( $list['date_created'] ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $list['date_created'] ) ) ); ?></time></td>
					</tr>
				<?php
					$i++;
				endforeach;
				?>
			</table>
			<div class="tablenav bottom cf">
				<div class="alignleft tablenav-information">
					<span class="displaying-num"><?php printf( _n( '1 list', '%s lists', $lists['total'], 'chimplet' ), $lists['total'] ); ?></span>
				</div>
			</div>
			<table class="form-table">
				<tr>
					<td>
			<?php
			}
		}
	}

	/**
	 * Display a terms from all taxonomies
	 *
	 * @used-by Function: add_settings_field
	 * @version 2015-02-13
	 * @since   0.0.0 (2015-02-11)
	 * @todo    Support all taxonomies
	 * @todo    Add badges to indicate already synced groups
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_field_terms( $args )
	{
		$list = $this->get_option( 'mailchimp.list' );

		if ( empty( $list ) ) {

			printf( '<p class="chimplet-alert alert-error">%s</p>', esc_html__( 'A List must be selected and saved.', 'chimplet' ) );
			return;

		}

		try {

			$list = $this->mc->lists->getList( [ 'list_id' => $list ] );
			$list = reset( $list['data'] );

		} catch ( \Mailchimp_List_DoesNotExist $e ) {

			printf( '<p class="chimplet-alert alert-error">%s</p>', esc_html__( 'The selected List does not exist in your account.', 'chimplet' ) );
			return;

		} catch ( \Mailchimp_Error $e ) {

			$this->display_inline_error( $e->getMessage(), __( 'An unknown error occurred while fetching the selected Mailing List from your account.', 'chimplet' ) );
			return;

		}
		?>
		<p class="description"><?php esc_html_e( 'Select one or more terms, across available taxonomies, to be added as Interest Groupings for the selected Mailing List.', 'chimplet' ); ?></p>
		<?php

		$value = $this->get_option( 'mailchimp.terms', [] );

		$groupings = [];

		try {

			$groupings = $this->mc->lists->interestGroupings( $list['id'] );

		} catch ( \Mailchimp_Error $e ) {

			$this->display_inline_error( $e->getMessage(), __( 'An unknown error occurred while fetching the Interest Groupings from the selected Mailing List.', 'chimplet' ) );

		}

		$taxonomies = get_taxonomies( [ 'object_type' => $this->excluded_post_types ], 'objects', 'NOT' );

		if ( count( $taxonomies ) ) :
			foreach ( $taxonomies as $taxonomy ) :

				if ( in_array( $taxonomy->name, $this->excluded_taxonomies ) ) {
					continue;
				}

				$taxonomy_in_grouping = null;
				$grouping_status      = '<span class="chimplet-sync dashicons dashicons-no" title="' . esc_attr( __( 'Grouping isn’t synced with MailChimp List.', 'chimplet' ) ) . '"></span>';

				if ( count( $groupings ) ) {
					foreach ( $groupings as $grouping_key => $grouping ) {
						if ( $taxonomy->label === $grouping['name'] ) {
							$taxonomy_in_grouping = &$groupings[ $grouping_key ];
							$grouping_status      = '<span class="chimplet-sync dashicons dashicons-yes" title="' . esc_attr( __( 'Grouping is synced with MailChimp List.', 'chimplet' ) ) . '"></span>';
							break;
						}
					}
				}

				$terms = get_terms( $taxonomy->name );

				if ( count( $terms ) ) : ?>
				<fieldset>
					<legend><span class="h4"><?php echo $taxonomy->label . $grouping_status; //xss ok ?></span></legend>
					<div class="chimplet-item-list chimplet-mc">
						<?php
						$id   = 'cb-select-' . $taxonomy->name . '-all';
						$name = 'chimplet[mailchimp][terms][' . $taxonomy->name . '][]';
						$match = ( empty( $value[ $taxonomy->name ] ) || ! is_array( $value[ $taxonomy->name ] ) ? false : in_array( 'all', $value[ $taxonomy->name ] ) );
						?>
						<label for="<?php echo esc_attr( $id ); ?>">
							<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_html( $id ); ?>" value="all"<?php checked( $match ); ?>>
							<span><?php _e( 'Select All/None', 'chimplet' ); ?></span>
						</label>
						<?php
						foreach ( $terms as $term ) :
							$id    = 'cb-select-' . $taxonomy->name . '-' . $term->term_id;
							$match = ( empty( $value[ $taxonomy->name ] ) || ! is_array( $value[ $taxonomy->name ] ) ? false : in_array( $term->term_id, $value[ $taxonomy->name ] ) );
							$term_in_group  = null;
							$group_status   = '<span class="chimplet-sync dashicons dashicons-no" title="' . esc_attr( __( 'Group isn’t synced with MailChimp Grouping.', 'chimplet' ) ) . '"></span>';

							if ( isset( $taxonomy_in_grouping['groups'] ) && count( $taxonomy_in_grouping['groups'] ) ) {
								foreach ( $taxonomy_in_grouping['groups'] as $group_key => $group ) {
									if ( $term->name === $group['name'] ) {
										$term_in_group  = &$taxonomy_in_grouping['groups'][ $group_key ];
										$group_status   = '<span class="chimplet-sync dashicons dashicons-yes" title="' . esc_attr( __( 'Group is synced with MailChimp Grouping.', 'chimplet' ) ) . '"></span>';
										break;
									}
								}
							}
						?>
						<label for="<?php echo esc_attr( $id ); ?>">
							<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $term->term_id ); ?>"<?php checked( $match ); ?>>
							<span><?php echo esc_html( $term->name ); ?></span>
							<?php echo $group_status; //xss ok ?>
						</label>
						<?php endforeach; ?>
					</div>
				</fieldset>
				<?php
				endif;
			endforeach;
		endif;
	}

	/**
	 * Display error message or a fallback if there isn't one
	 *
	 * @version 2015-02-15
	 * @access private
	 * @param $message
	 * @param $fallback_message
	 */
	private function display_inline_error( $message, $fallback_message ) {
		if ( $message ) {
			printf( '<p class="chimplet-alert alert-warning">%s</p>', esc_html( $message ) );
		} else {
			printf( '<p class="chimplet-alert alert-error">%s</p>', esc_html( $fallback_message ) );
		}
		return;
	}

}
