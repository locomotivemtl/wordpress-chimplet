<?php

namespace Locomotive\Chimplet;

use Locomotive\Singleton;
use Locomotive\WordPress\AdminNotices;

/**
 * File: Chimplet Settings Page Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Settings Page
 *
 * @version 2015-02-10
 * @since   0.0.0 (2015-02-07)
 */

class SettingsPage extends AdminPage
{
	use Singleton;

	protected $mc;
	protected $notices;

	/**
	 * Constructor
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-07)
	 * @access  public
	 * @param   WP  $facade  {@see WordPress\Facade::__construct}
	 */

	public function __construct( WP $facade = null )
	{
		$this->view['document_title'] = __('Chimplet Settings', 'chimplet');

		$this->view['page_title'] = __('Settings', 'chimplet');
		$this->view['menu_title'] = $this->view['page_title'];
		$this->view['menu_slug']  = 'chimplet-settings';

		$this->notices = AdminNotices::get_singleton();

		$mailchimp_key = $this->get_option('mailchimp.api_key');

		if ( empty( $mailchimp_key ) ) {

			$this->nonce = 'activate_mailchimp_api_key';

		}
		else {

			$this->nonce = 'deactivate_mailchimp_api_key';

			try {

				$this->mc = new \Mailchimp( $mailchimp_key );

			} catch ( \Mailchimp_Error $e ) {

				if ( $e->getMessage() ) {
					$message = $e->getMessage();
				} else {
					$message  = '<p>' . __('Chimplet was unable to integrate with MailChimp.', 'chimplet') . ' ' . __('Possible reasons:', 'chimplet') . '</p>';
					$message .= '<ul>';
					$message .= 	'<li>' . __('You have not set an API key.', 'chimplet') . '</p>';
					$message .= 	'<li>' . __('Your API key is invalid.', 'chimplet') . '</p>';
					$message .= '</ul>';
				}

				$this->notices->add(
					'chimplet/mailchimp/api-key-failed',
					$message,
					[ 'type' => 'error' ]
				);

			}

		}

		parent::__construct( $facade );
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
		if ( false === get_option('chimplet') ) {
			update_option( 'chimplet', [] );
		}

		register_setting( 'chimplet-mailchimp', 'chimplet', [ $this, 'sanitize_settings' ] );

		add_settings_section(
			'chimplet-section-mailchimp-api',
			null,
			[ $this, 'render_mailchimp_section' ],
			$this->view['menu_slug']
		);

		add_settings_field(
			'chimplet-field-mailchimp-api_key',
			__('API Key', 'chimplet'),
			[ $this, 'render_mailchimp_field_api_key' ],
			$this->view['menu_slug'],
			'chimplet-section-mailchimp-api',
			[
				'label_for' => 'chimplet-field-mailchimp-api_key'
			]
		);

		// Settings fields when API Key integrated
		if ( $this->mc ) {

			add_settings_field(
				'chimplet-field-mailchimp-list',
				__('Select Mailing List', 'chimplet'),
				[ $this, 'render_mailchimp_field_list' ],
				$this->view['menu_slug'],
				'chimplet-section-mailchimp-api',
				[
					'control' => 'radio-table' // Choices: select, radio-table
				]
			);

		}
	}

	/**
	 * Sanitize setting values
	 *
	 * @uses    Filter: "sanitize_option_{$option_name}"
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 */

	public function sanitize_settings( $values )
	{
		return $values;
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
		$this->hook = $this->wp->add_submenu_page( $this->get_menu_slug('OverviewPage'), $this->view['document_title'], $this->view['menu_title'], 'manage_options', $this->view['menu_slug'], [ $this, 'render_page' ] );

		add_action( "load-{$this->hook}", [ $this, 'load_page' ] );
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
		$mailchimp_key = $this->get_option('mailchimp.api_key');

		if ( empty( $mailchimp_key ) ) {
			$this->view['button_label'] = __('Save API Key', 'chimplet');
		}
		else {
			$this->view['button_label'] = null;
		}

		// $this->view['mailchimp_key'] = $this->get_option('mailchimp.api_key');

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
			<p><?php _e('To integrate your blog with your MailChimp account, you need to generate an API key.', 'chimplet'); ?></p>
			<aside class="panel-assistance inset">
				<p><?php
					printf(
						__('Users with Admin or Manager permissions can generate and view API keys. You can %s from your Account Panel.', 'chimplet'),
						'<a target="_blank" href="' . '//kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key' . '">' . __('find or generate an API key', 'chimplet') . '</a>'
					);
				?></p>
				<ol>
					<li><?php printf( __('Click your profile name to expand the Account Panel, and choose %1$s.', 'chimplet'), '<em>' . __('Account') . '</em>' ); ?></li>
					<li><?php printf( __('Click the %1$s drop-down menu and choose %2$s.', 'chimplet'), '<em>' . __('Extras') . '</em>', '<em>' . __('API keys') . '</em>' ); ?></li>
					<li><?php printf( __('Copy an existing API key or click the %1$s button.', 'chimplet'), '<em>' . __('Create A Key') . '</em>' ); ?></li>
					<li><?php _e('Name your key descriptively, so you know what application uses that key.', 'chimplet'); ?></li>
				</ol>
			</aside>
			<p><?php _e('Once the API Key is integrated with Chimplet, you will be provided with additional options.', 'chimplet'); ?></p>
<?php

		}
		else {

?>
			<p><?php _e('With an integrated API Key, additional options are provided below.', 'chimplet'); ?></p>
			<p><?php _e('Removing the API Key will disable Chimplet’s data synchronization features and no longer provides access to your account to manage your subscribers and campaigns. This does not delete any data from your MailChimp nor does it disable Post Category feeds and the active RSS-Driven Campaigns.', 'chimplet'); ?></p>
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
		$value = $this->get_option('mailchimp.api_key');

		if ( empty( $value ) ) {
			$value = $readonly = '';
		}
		else {
			$value    = esc_attr( $value );
			$readonly = ' readonly';
		}

		$readonly = '';

		echo '<input type="text" class="regular-text" id="' . $args['label_for'] . '" name="chimplet[mailchimp][api_key]"' . $readonly . ' value="' . $value . '" />';
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
		$value = $this->get_option('mailchimp.list');

		try {

			$lists = $this->mc->lists->getList();

			// var_dump( $lists );

		} catch ( \Mailchimp_Error $e ) {

			if ( $e->getMessage() ) {
				echo '<p>' . $e->getMessage() . '</p>';
			} else {
				echo '<p>' . __('An unknown error occurred', 'chimplet') . '</p>';
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

			if ( $args['control'] === 'select' ) {

				echo '<select name="list" id="' . $args['label_for'] . '" name="chimplet[mailchimp][list]"' . $readonly . '>';

				foreach ( $lists['data'] as $list ) {
					echo '<option value="' . $list['id'] . '"' . selected( $value, $list['id'] ) . '>' . $list['name'] . '</option>';
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
						<th scope="col" id="chimplet-rb" class="manage-column column-rb check-column"><label class="screen-reader-text"><?php _e('Select One', 'chimplet'); ?></label></th>
						<th scope="col" id="mailchimp-list-title" class="manage-column column-name"><?php _e('Title'); ?></th>
						<th scope="col" id="mailchimp-list-members" class="manage-column column-members num"><?php _e('Members', 'chimplet'); ?></th>
						<th scope="col" id="mailchimp-list-date" class="manage-column column-date"><?php _e('Date Created', 'chimplet'); ?></th>
						<th scope="col" id="mailchimp-list-rating" class="manage-column column-rating num"><?php _e('Rating'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
				$i = 0;
				foreach ( $lists['data'] as $list ) {
					$select_label = sprintf( __('Select %s'), '&ldquo;' . $list['name'] . '&rdquo;' );
?>
					<tr id="mailchimp-list-<?php echo $list['id']; ?>" class="mailchimp-list-<?php echo $list['id']; ?> mailchimp-list<?php echo ( $i % 2 === 0 ? ' alternate' : '' ); ?>">
						<th scope="row" class="check-column">
							<label class="screen-reader-text" for="rb-select-<?php echo $list['id']; ?>"><?php echo $select_label; ?></label>
							<input type="radio" id="rb-select-<?php echo $list['id']; ?>" name="chimplet[mailchimp][list]" value="<?php echo $list['id']; ?>"<?php echo checked( $value, $list['id'] ); ?> />
						</th>
						<td class="column-title">
							<strong><label for="rb-select-<?php echo $list['id']; ?>" title="<?php echo esc_attr( $select_label ); ?>"><?php echo $list['name']; ?></label></strong>
						</td>
						<td class="column-members num"><?php echo $list['stats']['member_count']; ?></td>
						<td class="column-date"><time datetime="<?php echo $list['date_created']; ?>"><?php echo date_i18n( get_option('date_format'), strtotime( $list['date_created'] ) ); ?></time></td>
						<td class="column-rating num"><?php echo $list['list_rating']; ?></td>
					</tr>
<?php
					$i++;
				}
?>
			</table>
			<div class="tablenav bottom cf">
<?php /*
				<div class="alignright tablenav-actions">
					<input type="submit" name="chimplet-new-list" id="mailing-list-add-new" class="button action" value="<?php _e('Add New List', 'chimplet'); ?>">
				</div>
*/ ?>
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

}
