<?php

namespace Locomotive\Chimplet;

/**
 * File: Static Options Handling Trait
 *
 * @package Locomotive\Chimplet
 */

/**
 * Trait: Static Options Handling
 *
 * @version 2015-02-10
 * @since   0.0.0 (2015-02-06)
 */

trait BaseOption
{

	/**
	 * Retrieve options array from Options Table
	 *
	 * @uses    Application::$options
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   bool    $allow_filter  Optional. Pass value through a hook.
	 * @return  array   $options
	 */

	public function get_options( $allow_filter = true )
	{
		global $chimplet;

		if ( empty( $chimplet->options ) )
		{
			$chimplet->options = get_option( 'chimplet', [] );
		}

		if ( $allow_filter ) {
			$chimplet->options = apply_filters( "chimplet/options/load", $chimplet->options );
		}

		return $chimplet->options;
	}

	/**
	 * Update options array for Options Table
	 *
	 * @uses    Application::$options
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   array   $options       The array to save to the Options Table.
	 * @param   bool    $allow_filter  Optional. Pass value through a hook.
	 * @return  bool
	 */

	public function update_options( $options = null, $allow_filter = true )
	{
		global $chimplet;

		if ( ! is_null( $options ) ) {
			$chimplet->options = $options;
		}

		if ( $allow_filter ) {
			$chimplet->options = apply_filters( "chimplet/options/save", $chimplet->options );
		}

		return update_option( 'chimplet', $chimplet->options );
	}

	/**
	 * Retrieve a value from the $options array
	 *
	 * @uses    Application::$options
	 * @version 2015-02-10
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   string  $name          Name of information to retrieve.
	 * @param   mixed   $default       Optional. Default value to return if the option does not exist.
	 * @param   bool    $allow_filter  Optional. Pass value through a hook.
	 * @return  mixed   $value         Value set for the information.
	 */

	public function get_option( $name, $default = false, $allow_filter = true )
	{
		global $chimplet;

		$this->get_options();

		$value = null;

		$name = trim( $name );

		if ( empty( $name ) ) {
			return false;
		}

		$spaces = explode( '.', $name );
		$value  = & $chimplet->options;

		foreach ( $spaces as $space ) {

			if ( isset( $value[ $space ] ) ) {
				$value = & $value[ $space ];
			}
			else {
				return $default;
			}

		}

		if ( $allow_filter ) {
			$value = apply_filters( "chimplet/options/value/{$name}", $value );
		}

		return $value;
	}

	/**
	 * Remove a value from the options array
	 *
	 * @uses    Application::$options
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   string  $name
	 */

	public function remove_option( $name )
	{
		global $chimplet;

		if ( isset( $chimplet->options[ $name ] ) ) {
			unset( $chimplet->options[ $name ] );

			$this->update_options();
		}
	}

	/**
	 * Update a value to the options array
	 *
	 * @uses    Application::$options
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 */

	public function update_option( $name, $value )
	{
		global $chimplet;

		$chimplet->options[ $name ] = $value;

		$this->update_options();
	}

	/**
	 * Add a value to the options array
	 *
	 * @uses    Application::$options
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 */

	public function append_option( $name, $value )
	{
		global $chimplet;

		if ( ! isset( $chimplet->options[ $name ] ) )
		{
			$chimplet->options[ $name ] = [];
		}

		$chimplet->options[ $name ][] = $value;

		$this->update_options();
	}

}
