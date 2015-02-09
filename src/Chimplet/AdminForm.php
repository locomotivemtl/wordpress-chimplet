<?php

namespace Locomotive\Chimplet;

/**
 * File: Form Handling Trait
 *
 * @package Locomotive\Chimplet
 */

/**
 * Trait: Form Handling
 *
 * @version 2015-02-09
 * @since   0.0.0 (2015-02-06)
 */

trait AdminForm
{
	public $nonce;
	public $submitted_data;
	public $submission_parsed = false;

	/**
	 * Verify $_POST['_chimpletnonce']
	 *
	 * @uses    $_POST
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   string  $nonce
	 * @return  bool
	 */

	public function verify_nonce( $nonce )
	{
		$key  = '_chimpletnonce';
		$test = false;

		if ( isset( $_POST[ $key ] ) ) {

			if ( is_string( $_POST[ $key ] ) && wp_verify_nonce( $_POST[ $key ], $nonce ) ) {
				$test = true;

				$_POST[ $key ] = false;
			}

		}

		return $test;
	}

	/**
	 * Retrieve submitted data from $_POST request
	 *
	 * @uses    $_POST
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   bool    $allow_filter  Optional. Pass value through a hook.
	 * @return  array   $data
	 */

	public function get_submitted_values( $allow_filter = true )
	{
		global $chimplet;

		$data = [];

		if ( ! $this->submission_parsed ) {
			$this->parse_submitted_values();
		}

		if ( ! empty( $this->submitted_data ) ) {
			$data = $this->submitted_data;
		}

		return $data;
	}

	/**
	 * Parse and validate submitted data from $_POST request
	 *
	 * @uses    $_POST
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   bool    $allow_filter  Optional. Pass value through a hook.
	 * @return  array   $data
	 */

	public function validate_submitted_values()
	{
		global $chimplet;

		if ( $this->verify_nonce( $this->nonce ) ) {

			if ( ! empty( $_POST['chimplet'] ) && is_array( $_POST['chimplet'] ) ) {

				$groups = array_keys( $_POST['chimplet'] );

				foreach ( $groups as $group ) {

					if ( '_' === substr( $group, 0, 1 ) ) {
						continue;
					}

					if ( ! empty( $_POST['chimplet'][ $group ] ) && is_array( $_POST['chimplet'][ $group ] ) ) {

						$keys = array_keys( $_POST['chimplet'][ $group ] );

						foreach ( $keys as $key ) {

							// @todo Get Setting from Settings API
							// $setting = $this->wp->get_setting(); ???
							$setting = null;

							$this->validate_value( $_POST['chimplet'][ $group ][ $key ], $setting, "chimplet[{$group}][{$key}]" );

						}

					}

				}

			}

		}

		$this->submission_parsed = true;

		return true;
	}

	/**
	 * Validate submitted value for a setting
	 *
	 * @uses    $_POST
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-09)
	 *
	 * @param   mixed   $value    The submitted value.
	 * @param   array   $setting  The registered setting.
	 * @param   string  $input    Value of name attribute of a DOM element.
	 * @return  bool
	 */

	public function validate_value( $value, $setting, $input )
	{
		global $chimplet;

		$valid = true;

		// @todo Validation & Sanitization

		return $valid;
	}

}
