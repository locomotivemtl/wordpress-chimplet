<?php

namespace Locomotive\MailChimp;

use ReflectionClass;
use Mailchimp;

/**
 * File: MailChimp Facade Class
 *
 * Acts as a master facade class for MailChimp to allow me to isolate and test
 * functionality without bootstrapping the vendor API.
 *
 * @package Locomotive\MailChimp
 * @link    https://bitbucket.org/mailchimp/mailchimp-api-php/ Mailchimp API Client
 * @see     Locomotive\WordPress\Facade {
 *    Inspired by a modified version of Kevin Sperrine's WordPress facade.
 * }
 */

/**
 * Class: MailChimp Facade
 *
 * @version 2015-02-12
 * @since   0.0.0 (2015-02-12)
 */

class Facade
{
	public  static $is_initialized = false;
	private static $__facade;

	/**
	 * MailChimp Initialization
	 *
	 * @version 2015-02-12
	 * @since   2015-02-12
	 * @access  public
	 * @param   mixed  The arguments passed to the function
	 * @return  void|object
	 */

	public function initialize()
	{
		if ( func_num_args() ) {

			$reflect = new ReflectionClass( 'Mailchimp' );

			static::$__facade = $reflect->newInstanceArgs( func_get_args() );

		}
		else {

			static::$__facade = new Mailchimp;

		}

		if ( static::$__facade instanceof Mailchimp ) {
			return static::$__facade;
		}
	}

	/**
	 * Is MailChimp API Client Initialized?
	 *
	 * @version 2015-02-12
	 * @since   2015-02-12
	 * @access  public
	 * @return  bool
	 */

	public function is_initialized()
	{
		return ( static::$__facade instanceof Mailchimp );
	}

	/**
	 * Magic __call method that creates a facade for
	 * the chosen MailChimp API client.
	 *
	 * @throws Exception
	 * @access public
	 *
	 * @param string $method The MailChimp API function you want to call.
	 * @param mixed $arguments The arguments passed to the function
	 *
	 * @return mixed The return value depends on the MailChimp API function
	 */

	public function __call( $method, $arguments )
	{
		if ( method_exists( static::$__facade, $method ) ) {
			return call_user_func_array( [ static::$__facade, $method ], $arguments );
		}

		throw new \Exception( sprintf( 'The function, "%s::%s", does not exist.', get_class( static::$__facade ), $method ) );
	}

	/**
	 * Magic __get method that creates a facade for
	 * the chosen MailChimp API client.
	 *
	 * @throws Exception
	 * @access public
	 *
	 * @param string $property The MailChimp API property you want to get.
	 *
	 * @return mixed The return value depends on the MailChimp API property
	 */

	public function __get( $property )
	{
		if ( property_exists( static::$__facade, $property ) ) {
			return static::$__facade->{ $property };
		}

		throw new \Exception( sprintf( 'The property, "%s::\$%s", does not exist.', get_class( static::$__facade ), $property ) );
	}

}
