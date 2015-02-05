<?php

namespace Locomotive\Chimplet;

/**
 * File: Abstract Base Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Abstract Base
 *
 * @version 2015-02-05
 * @since   0.0.0 (2015-02-05)
 */

abstract class Base
{

	/**
	 * Constructor
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 */

	public function __construct()
	{
	}

	/**
	 * Retrieve a value from the settings array
	 *
	 * @uses    Application::$settings
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $name
	 * @param   bool    $allow_filter
	 */

	public function get_setting( $name, $allow_filter = true )
	{
		global $chimplet;

		$value = null;

		if ( isset( $chimplet->settings[ $name ] ) )
		{
			$value = $chimplet->settings[ $name ];
		}

		if ( $allow_filter )
		{
			$value = apply_filters( "chimplet/settings/{$name}", $value );
		}

		return $value;
	}

	/**
	 * Update a value to the settings array
	 *
	 * @uses    Application::$settings
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 */

	public function update_setting( $name, $value )
	{
		global $chimplet;

		$chimplet->settings[ $name ] = $value;
	}

	/**
	 * Add a value to the settings array
	 *
	 * @uses    Application::$settings
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 */

	public function append_setting( $name, $value )
	{
		global $chimplet;

		if ( ! isset( $chimplet->settings[ $name ] ) )
		{
			$chimplet->settings[ $name ] = [];
		}

		$chimplet->settings[ $name ][] = $value;
	}

	/**
	 * Retrieve path to Chimplet directory
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $path
	 */

	public function get_path( $path )
	{
		return $this->get_setting('path') . $path;
	}

	/**
	 * Retrieve path to Chimplet directory
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $path
	 */

	public function get_url( $path )
	{
		return $this->get_setting('url') . $path;
	}

	/**
	 * Include File
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $file
	 */
/*
	public function include( $file )
	{
		$path = $this->get_path( $file );

		if ( file_exists( $path ) ) {

			include $path;

		}
	}
*/
	/**
	 * Render View
	 *
	 * Load template from `views/` directory and allow
	 * variables to be passed through.
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $template
	 * @param   array   $args
	 */

	public function render_view( $template, $args = [] )
	{
		$path = $this->get_path("assets/views/{$template}.php");

		if ( file_exists( $path ) ) {

			include $path;

		}
	}

	/**
	 * Add a new notice
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 * @link    AdvancedCustomFields\acf_add_admin_notice() Based on ACF method
	 *
	 * @param   string  $text
	 * @param   mixed   $args  {
	 *     @type string $code
	 *     @type string $class
	 *     @type string $wrap
	 * }
	 * @return  array $notices
	 */

	function add_notice( $text, $args = [] )
	{
		$defaults = [
			'code'  => '',
			'class' => 'updated',
			'wrap'  => 'p'
		];

		$args = wp_parse_args( $args, $defaults );

		$notices = $this->get_notices();

		$notices[] = array_merge( [ 'text' => $text ], $args );

		$this->update_setting( 'admin_notices', $notices );

		return ( count( $notices ) - 1 );
	}

	/**
	 * Retrieve any notices
	 *
	 * @used-by static::render_notices()
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 * @link    AdvancedCustomFields\acf_get_admin_notices() Based on ACF method
	 *
	 * @return  array $notices
	 */

	function get_notices()
	{
		$notices = $this->get_setting('admin_notices');

		if ( ! $notices )
		{
			$notices = [];
		}

		return $notices;
	}

}
