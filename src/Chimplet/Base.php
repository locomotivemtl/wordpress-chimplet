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
	 * @param   string  $name     Name of setting to retrieve.
	 * @param   mixed   $default  Optional. Default value to return if the option does not exist.
	 * @return  mixed   $value    Value set for the setting.
	 */

	public function get_setting( $name, $default = false, $allow_filter = true )
	{
		global $chimplet;

		$value = null;

		$name = trim( $name );

		if ( empty( $name ) ) {
			return false;
		}

		if ( isset( $chimplet->settings[ $name ] ) ) {
			$value = $chimplet->settings[ $name ];

			if ( $allow_filter ) {
				$value = apply_filters( "chimplet/settings/{$name}", $value );
			}
		}
		else {
			$value = $default;
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

}
