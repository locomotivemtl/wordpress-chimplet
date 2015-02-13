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
	public  $is_initialized = false;
	private $facade;

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
		if ( $this->is_initialized() ) {
			return $this->facade;
		}

		if ( func_num_args() ) {
			return $this->facade = new Mailchimp( func_get_args() );
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
		return ( $this->facade instanceof Mailchimp );
	}

	/**
	 * Is the api key entered by the user valid?
	 *
	 * @since  2015-02-12
	 * @access public
	 * @param  String $api_key
	 * @return bool
	 */

	public function is_api_key_valid( $api_key ) {
		$this->facade = new Mailchimp( $api_key );

		try {
			$ping = $this->facade->helper->ping();
			if ( "Everything's Chimpy!" === $ping['msg'] ) {
				return true;
			}
		} catch( \Mailchimp_Error $e ) {
			return false;
		}

		return false;
	}

	/**
	 * Magic __call method that creates a facade for
	 * the chosen MailChimp API client.
	 *
	 * @throws \Exception
	 * @access public
	 *
	 * @param string $method The MailChimp API function you want to call.
	 * @param mixed $arguments The arguments passed to the function
	 *
	 * @return mixed The return value depends on the MailChimp API function
	 */

	public function __call( $method, $arguments )
	{
		if ( method_exists( $this->facade, $method ) ) {
			return call_user_func_array( [ $this->facade, $method ], $arguments );
		}

		throw new \Exception( sprintf( 'The function, "%s::%s", does not exist.', get_class( $this->facade ), $method ) );
	}

	/**
	 * Magic __get method that creates a facade for
	 * the chosen MailChimp API client.
	 *
	 * @throws \Exception
	 * @access public
	 *
	 * @param string $property The MailChimp API property you want to get.
	 *
	 * @return mixed The return value depends on the MailChimp API property
	 */

	public function __get( $property )
	{
		if ( property_exists( $this->facade, $property ) ) {
			return $this->facade->{ $property };
		}

		throw new \Exception( sprintf( 'The property, "%s::\$%s", does not exist.', get_class( $this->facade ), $property ) );
	}

}
