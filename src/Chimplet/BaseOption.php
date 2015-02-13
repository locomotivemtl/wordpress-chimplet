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
 * @uses    \WordPress\WP as $wp
 * @version 2015-02-12
 * @since   0.0.0 (2015-02-06)
 */

trait BaseOption
{
	protected static $options;

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
		if ( empty( self::$options ) ) {
			self::$options = $this->wp->get_option( 'chimplet', [] );
		}

		if ( $allow_filter ) {
			self::$options = $this->wp->apply_filters( 'chimplet/options/load', self::$options );
		}

		return self::$options;
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
		if ( ! is_null( $options ) ) {
			self::$options = $options;
		}

		if ( $allow_filter ) {
			self::$options = $this->wp->apply_filters( 'chimplet/options/save', self::$options );
		}

		return $this->wp->update_option( 'chimplet', self::$options );
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
		$this->get_options();

		$value = null;

		$name = trim( $name );

		if ( empty( $name ) ) {
			return false;
		}

		$spaces = explode( '.', $name );
		$value  = &self::$options;

		foreach ( $spaces as $space ) {

			if ( isset( $value[ $space ] ) ) {
				$value = & $value[ $space ];
			}
			else {
				return $default;
			}
		}

		if ( $allow_filter ) {
			$value = $this->wp->apply_filters( "chimplet/options/value/{$name}", $value );
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
		if ( isset( self::$options[ $name ] ) ) {
			unset( self::$options[ $name ] );

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
		self::$options[ $name ] = $value;

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
		if ( ! isset( self::$options[ $name ] ) )  {
			self::$options[ $name ] = [];
		}

		self::$options[ $name ][] = $value;

		$this->update_options();
	}

}
