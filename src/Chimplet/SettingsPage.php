<?php

namespace Locomotive\Chimplet;

use Locomotive\Singleton;

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
		$mailchimp_key = $this->get_option('mailchimp.api_key');

		$this->nonce = ( empty( $mailchimp_key ) ? 'activate_mailchimp_api_key' : 'deactivate_mailchimp_api_key' );

		$this->view['document_title'] = __('Chimplet Settings', 'chimplet');

		$this->view['page_title'] = __('Settings', 'chimplet');
		$this->view['menu_title'] = $this->view['page_title'];
		$this->view['menu_slug']  = 'chimplet-settings';

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
		$mailchimp_key = $this->get_option('mailchimp.api_key');

		if ( empty( $mailchimp_key ) ) {
			$value = $readonly = '';
		}
		else {
			$value = esc_attr( $mailchimp_key );
			$readonly = ' readonly';
		}

		$readonly = '';

		echo '<input type="text" class="regular-text" id="' . $args['label_for'] . '" name="chimplet[mailchimp][api_key]"' . $readonly . ' value="' . $value . '" />';
	}

}
