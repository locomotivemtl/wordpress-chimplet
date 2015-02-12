<?php

namespace Locomotive\Chimplet;

/**
 * File: Static Information Handling Trait
 *
 * @package Locomotive\Chimplet
 */

/**
 * Trait: Static Information Handling
 *
 * @version 2015-02-10
 * @since   0.0.0 (2015-02-06)
 */

trait BaseInfo
{
	protected static $information;

	/**
	 * Retrieve a value from the $information array
	 *
	 * @uses    Application::$information
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $name          Name of information to retrieve.
	 * @param   mixed   $default       Optional. Default value to return if the option does not exist.
	 * @param   bool    $allow_filter  Optional. Pass value through a hook.
	 * @return  mixed   $value         Value set for the information.
	 */

	public function get_info( $name, $default = false, $allow_filter = true )
	{
		$value = null;

		$name = trim( $name );

		if ( empty( $name ) ) {
			return false;
		}

		$spaces = explode( '.', $name );
		$value  = &self::$information;

		foreach ( $spaces as $space ) {

			if ( isset( $value[ $space ] ) ) {
				$value = & $value[ $space ];
			}
			else {
				return $default;
			}
		}

		if ( $allow_filter ) {
			$value = apply_filters( "chimplet/info/value/{$name}", $value );
		}

		return $value;
	}

	/**
	 * Update a value to the information array
	 *
	 * @uses    Application::$information
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 */

	public function update_info( $name, $value )
	{
		self::$information[ $name ] = $value;
	}

	/**
	 * Add a value to the information array
	 *
	 * @uses    Application::$information
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 */

	public function append_info( $name, $value )
	{
		if ( ! isset( self::$information[ $name ] ) ) {
			self::$information[ $name ] = [];
		}

		self::$information[ $name ][] = $value;
	}

}
