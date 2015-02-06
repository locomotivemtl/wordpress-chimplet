<?php

namespace Locomotive\WordPress;

use Locomotive\Singleton;
use Locomotive\WordPress\WP;
use Locomotive\WordPress\Facade;

/**
 * File: WordPress Administration Notifications Class
 *
 * @package Locomotive\WordPress
 */

/**
 * Class: WordPress Administration Notifications
 *
 * Container for handling messages and errors to be displayed
 * to users within the WordPress Administration interface.
 *
 * Based on \WordPress\WP_Error.
 *
 * @package  Locomotive\WordPress
 * @author   Chauncey McAskill <https://mcaskill.ca>
 */

class AdminNotices
{
	use Singleton, Facade;

	/**
	 * @var array  $types        Types of notifications
	 * @var array  $notices      Stores the list of notifications
	 * @var array  $notice_data  Stores the list of data for notification codes.
	 * @var bool   $updated      Whether the current queue of notifications has been saved to the database
	 */

	protected $types       = [];
	protected $notices     = [];
	protected $notice_data = [];
	protected $updated     = false;

	/**
	 * Constructor
	 *
	 * Prepares actions and filters for commodity methods.
	 *
	 * @access  public
	 * @param   WP  $facade  Allows inserting a different facade object for testing.
	 */

	public function __construct( WP $facade = null )
	{
		$this->set_facade( $facade );

		if ( ! $this->wp->is_admin() ) {
			return;
		}

		$this->wp->add_action( 'init',          [ $this, 'init'     ], 5 );
		$this->wp->add_action( 'admin_notices', [ $this, 'render'   ], 5 );
		$this->wp->add_action( 'shutdown',      [ $this, 'shutdown' ], 5 );
	}

	/**
	 * WordPress Initialization
	 *
	 * Set up notifications as early as possible.
	 *
	 * @used-by  Action: init
	 * @access   public
	 * @version  2015-02-06
	 * @since    0.0.0 (2015-02-06)
	 */

	public function init()
	{
		$this->types = [ 'info', 'update', 'error' ];

		$saved = $this->wp->get_transient('admin_notices');

		if ( empty( $saved ) ) {
			$saved = [
				'notices'     => [],
				'notice_data' => []
			];
		}
		else {

			if ( empty( $saved['notices'] ) ) {
				$saved['notices'] = [];
			}

			if ( empty( $saved['notice_data'] ) ) {
				$saved['notice_data'] = [];
			}

		}

		$this->notices     = $saved['notices'];
		$this->notice_data = $saved['notice_data'];
		$this->updated     = false;
	}

	/**
	 * Retrieve the CSS class name for the notification type.
	 *
	 * @access  public
	 * @param   string  $type   Notification type.
	 * @return  string  $class  CSS class name.
	 */

	public function get_type_class( $type )
	{
		switch ( $type ) {
			case 'info':
				$class = 'information';
				break;

			case 'update':
				$class = 'updated';
				break;

			default:
				$class = $type;
				break;
		}

		return $class;
	}

	/**
	 * Retrieve all notification codes.
	 *
	 * @access  public
	 * @return  array  List of notification codes, if available.
	 */

	public function get_codes()
	{
		if ( empty( $this->notices ) ) {
			return [];
		}

		return array_keys( $this->notices );
	}

	/**
	 * Retrieve first notification code available.
	 *
	 * @access  public
	 * @return  string|int  $codes  Empty string, if no notification codes.
	 */

	public function get_code()
	{
		$codes = $this->get_codes();

		if ( empty( $codes ) ) {
			return '';
		}

		return $codes[0];
	}

	/**
	 * Retrieve all notification messages or notification messages matching code.
	 *
	 * @access  public
	 * @param   string|int  $code  Optional. Retrieve messages matching code, if exists.
	 * @return  array              Notification strings on success, or empty array on failure (if using code parameter).
	 */

	public function get_messages( $code = '' )
	{
		// Return all messages if no code specified.
		if ( empty( $code ) ) {
			$all_messages = [];

			foreach ( (array) $this->notices as $code => $messages ) {
				$all_messages = array_merge( $all_messages, $messages );
			}

			return $all_messages;
		}

		if ( isset( $this->notices[ $code ] ) ) {
			return $this->notices[ $code ];
		}

		return [];
	}

	/**
	 * Get single notification message.
	 *
	 * This will get the first message available for the code. If no code is
	 * given then the first code available will be used.
	 *
	 * @access  public
	 * @param   string|int    $code      Optional. Notification code to retrieve message.
	 * @return  string|array  $messages
	 */

	public function get_message( $code = '' )
	{
		if ( empty( $code ) ) {
			$code = $this->get_code();
		}

		$messages = $this->get_messages( $code );

		if ( empty( $messages ) ) {
			return '';
		}

		return $messages[0];
	}

	/**
	 * Retrieve notification data for notification code.
	 *
	 * @access  public
	 * @param   string|int  $code  Optional. Notification code.
	 * @return  mixed              Null, if no notifications.
	 */

	public function get_data( $code = '' )
	{
		if ( empty( $code ) ) {
			$code = $this->get_code();
		}

		if ( isset( $this->notice_data[ $code ] ) ) {
			return $this->notice_data[ $code ];
		}

		return null;
	}

	/**
	 * Add an notification or append additional message to an existing notification.
	 *
	 * @access  public
	 * @param   string|int  $code     Notification code.
	 * @param   string      $message  Notification message.
	 * @param   mixed       $data     Optional. Notification data.
	 */
	public function add( $code, $message, $data = [] )
	{
		$this->notices[ $code ][] = $message;

		if ( ! empty( $data ) ) {
			$this->notice_data[ $code ] = $data;
		}
	}

	/**
	 * Add data for notification code.
	 *
	 * The notification code can only contain one notification data.
	 *
	 * @access  public
	 * @param   mixed       $data  Notification data.
	 * @param   string|int  $code  Optional. Notification code.
	 */
	public function add_data( $data, $code = '' )
	{
		if ( empty( $code ) ) {
			$code = $this->get_code();
		}

		$this->notice_data[ $code ] = $data;
	}

	/**
	 * Removes the specified notification.
	 *
	 * This function removes all notification messages associated with the specified
	 * notification code, along with any notification data for that code.
	 *
	 * @access  public
	 * @param   string|int  $code  Notification code.
	 */
	public function remove( $code )
	{
		unset( $this->notices[ $code ] );
		unset( $this->notice_data[ $code ] );
	}

	/**
	 * Display any notices for the administration screen
	 *
	 * @used-by  Action: "admin_notices"
	 * @version  2015-02-05
	 * @since    0.0.0 (2015-02-05)
	 * @link     AdvancedCustomFields\acf_admin::admin_notices() Based on ACF method
	 */

	public function render()
	{
		$codes  = $this->get_codes();
		$groups = [];

		foreach ( $codes as $code ) {
			$messages = $this->get_messages( $code );
			$data     = $this->get_data( $code );

			if ( empty( $data['type'] ) ) {
				$data['type'] = 'update';
			}

			if ( in_array( $data['type'], $this->types ) ) {
				$class_list = [ $this->get_type_class( $data['type'] ) ];

				if ( empty( $data['class'] ) ) {
					$data['class'] = [];
				}
				elseif ( ! is_array( $data['class'] ) ) {
					$data['class'] = [ $data['class'] ];
				}

				$classes = array_merge( $class_list, $data['class'] );
				$classes = implode( ' ', $classes );

				if ( empty( $data['wrap'] ) ) {
					$data['wrap'] = 'p';
				}

				$open  = '<'  . $data['wrap'] . '>';
				$close = '</' . $data['wrap'] . '>';

?>
				<div role="alert" class="<?php echo $classes; ?>">
<?php
				foreach ( $messages as $message ) {

					echo $open . wp_kses( $message, wp_kses_allowed_html('post') ) . $close;

				}
?>
				</div>
<?php

			}

			$this->remove( $code );
			$this->updated = true;
		}
	}

	/**
	 * Writes notices to the database
	 *
	 * @used-by  Action: "shutdown"
	 * @uses     Transients API
	 */

	public function shutdown()
	{
		if ( ( empty( $this->notices ) && empty( $this->notice_data ) ) || ! $this->updated ) {
			return;
		}

		$saved = [
			'notices'     => $this->notices,
			'notice_data' => $this->notice_data
		];

		$this->wp->set_transient( 'admin_notices', $saved, WEEK_IN_SECONDS );
	}


}

AdminNotices::get_singleton();
