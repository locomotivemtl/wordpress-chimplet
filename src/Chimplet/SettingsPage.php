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

	const SETTINGS_KEY = 'chimplet';

	/**
	 * @var \Mailchimp    $mc       MailChimp API Object
	 * @var AdminNotices  $notices  AdminNotices Controller Object
	 */

	protected $mc;
	protected $notices;

	/**
	 * @var array  $excluded_post_types  Post types to exclude when fetching Taxonomy objects
	 * @var array  $excluded_taxonomies  Taxonomies to exclude when fetching Taxonomy objects
	 */

	public $excluded_post_types = [];
	public $excluded_taxonomies = [];

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
		$this->view['document_title'] = __( 'Chimplet Settings', 'chimplet' );

		$this->view['page_title'] = __( 'Settings' );
		$this->view['menu_title'] = $this->view['page_title'];
		$this->view['menu_slug']  = 'chimplet-settings';

		$this->excluded_post_types = [ 'page', 'revision', 'nav_menu_item' ];
		$this->excluded_taxonomies = [ 'post_format', 'nav_menu' ];

		$this->notices = AdminNotices::get_singleton();
		$this->notices->set_settings_errors_params( self::SETTINGS_KEY );

		parent::__construct( $facade );
	}

	/**
	 * Register settings, sections, and fields
	 *
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   string    $api_key
	 * @param   callable  $try_callback  Optional. Test MailChimp API and throw an error if it fails. Default is to use ping() method.
	 * @return  bool
	 */

	public function mc_init( $api_key = null, $try_callback = null )
	{
		if ( $this->mc instanceof \Mailchimp ) {
			return true;
		}

		if ( empty( $api_key ) ) {
			$api_key = $this->get_option( 'mailchimp.api_key' );
		}

		if ( empty( $api_key ) && ! isset( $_GET['settings-updated'] ) ) {
			return false;
		}

		try {

			$this->mc = new \Mailchimp( $api_key );

			if ( is_callable( $try_callback ) ) {

				call_user_func( $try_callback );

			}
			else {

				$ping = $this->mc->helper->ping();

				if ( $ping['msg'] !== "Everything's Chimpy!" ) {
					throw $this->castError( $ping );
				}
			}

			return true;

		} catch ( \Mailchimp_Error $e ) {

			$this->wp->add_settings_error(
				self::SETTINGS_KEY,
				'api-key-failed',
				$e->getMessage(),
				'error'
			);

			// @todo save that the key is invalid
		}

		return false;
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
		if ( false === get_option( 'chimplet' ) ) {
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
		if ( $this->mc_init() ) {

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
	 *
	 * @param array $settings
	 *
	 * @return array
	 */

	public function sanitize_settings( $settings )
	{
		// Validate key with MailChimp service
		if ( isset( $settings['mailchimp']['api_key'] ) ) {

			$this->mc_init( $settings['mailchimp']['api_key'] );

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
			$this->get_menu_slug( 'OverviewPage' ),
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

		if ( $this->mc_init() ) {
			$this->view['button_label'] = null;
		}
		else {
			$this->view['button_label'] = __( 'Save API Key', 'chimplet' );
		}

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
foreach ( $lists['data'] as $list ) {
	$select_label = sprintf( __( 'Select %s' ), '&ldquo;' . $list['name'] . '&rdquo;' );
	$id = 'rb-select-' . $list['id'];
?>
	<tr id="mailchimp-list-<?php echo $list['id']; ?>" class="mailchimp-list-<?php echo $list['id']; ?> mailchimp-list<?php echo ( $i % 2 === 0 ? ' alternate' : '' ); ?>">
		<th scope="row" class="check-column">
			<label class="screen-reader-text" for="<?php echo $id; ?>"><?php echo $select_label; ?></label>
			<input type="radio" id="<?php echo $id; ?>" name="chimplet[mailchimp][list]" value="<?php echo $list['id']; ?>"<?php echo checked( $value, $list['id'] ); ?> />
		</th>
		<td class="column-title">
			<strong><label for="<?php echo $id; ?>" title="<?php echo esc_attr( $select_label ); ?>"><?php echo $list['name']; ?></label></strong>
		</td>
		<td class="column-groupings num"><?php echo $list['stats']['grouping_count']; ?></td>
		<td class="column-members num"><?php echo $list['stats']['member_count']; ?></td>
		<td class="column-rating num"><?php echo $list['list_rating']; ?></td>
		<td class="column-date"><time datetime="<?php echo $list['date_created']; ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $list['date_created'] ) ); ?></time></td>
	</tr>
<?php
	$i++;
}
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
	 * @version 2015-02-11
	 * @since   0.0.0 (2015-02-11)
	 * @todo    Support all taxonomies
	 * @todo    Add badges to indicate already synced groups
	 *
	 * @param  array  $args
	 */

	public function render_mailchimp_field_terms( $args )
	{
		$list  = $this->get_option( 'mailchimp.list' );

		if ( empty( $list ) ) {

			echo '<p class="chimplet-alert alert-error">' . __( 'A List must be selected and saved.', 'chimplet' ) . '</p>';
			return;

		}

		try {

			$list = $this->mc->lists->getList( [ 'list_id' => $list ] );
			$list = reset( $list['data'] );

		} catch ( \Mailchimp_List_DoesNotExist $e ) {

			echo '<p class="chimplet-alert alert-error">' . __( 'The selected List does not exist in your account.', 'chimplet' ) . '</p>';
			return;

		} catch ( \Mailchimp_Error $e ) {

			if ( $e->getMessage() ) {
				echo '<p class="chimplet-alert alert-warning">' . $e->getMessage() . '</p>';
			} else {
				echo '<p class="chimplet-alert alert-error">' . __( 'An unknown error occurred while fetching the selected Mailing List from your account.', 'chimplet' ) . '</p>';
			}
			return;

		}

?>
			<p class="description"><?php _e( 'Select one or more terms, across available taxonomies, to be added as Interest Groupings for the selected Mailing List.', 'chimplet' ); ?></p>

<?php

		$value = $this->get_option( 'mailchimp.terms', [] );

try {

	$groups = $this->mc->lists->interestGroupings( $list['id'] );

} catch ( \Mailchimp_Error $e ) {

	if ( $e->getMessage() ) {
		echo '<p class="chimplet-alert alert-warning">' . $e->getMessage() . '</p>';
	} else {
		echo '<p class="chimplet-alert alert-error">' . __( 'An unknown error occurred while fetching the Interest Groupings from the selected Mailing List.', 'chimplet' ) . '</p>';
	}
}

if ( empty( $groups ) ) {
	$groups = [];
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

		$taxonomies = get_taxonomies( [ 'object_type' => $this->excluded_post_types ], 'objects', 'NOT' );

if ( count( $taxonomies ) ) {
	foreach ( $taxonomies as $taxonomy ) {

		if ( in_array( $taxonomy->name, $this->excluded_taxonomies ) ) {
			continue;
		}

		$terms = get_terms( $taxonomy->name );

		if ( count( $terms ) ) {

?>
	<fieldset>
		<legend><span class="h4"><?php echo $taxonomy->label; ?></span></legend>
		<div class="chimplet-item-list chimplet-mc">
<?php

			$id   = 'cb-select-' . $taxonomy->name . '-all';
			$name = 'chimplet[mailchimp][terms][' . $taxonomy->name . '][]';
?>

			<label for="<?php echo $id; ?>">
				<input type="checkbox" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="all">
				<span><?php _e( 'Select All/None', 'chimplet' ); ?></span>
			</label>

<?php

foreach ( $terms as $term ) {
	$id    = 'cb-select-' . $taxonomy->name . '-' . $term->term_id;
	$match = ( empty( $value[ $taxonomy->name ] ) || ! is_array( empty( $value[ $taxonomy->name ] ) ) ? false : in_array( $term->term_id, $value[ $taxonomy->name ] ) );

?>

<label for="<?php echo $id; ?>">
	<input type="checkbox" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $term->term_id; ?>"<?php echo checked( $match ); ?>>
	<span><?php echo $term->name; ?></span>
</label>

<?php

}

?>
		</div>
	</fieldset>

<?php

		}
	}
}

	}

}
