<?php

namespace Locomotive\WordPress;

/**
 * File: WordPress Facade Pattern Class
 *
 * @package Locomotive\WordPress
 * @version 2015-02-06
 * @since   0.0.0 (2015-02-06)
 * @author  Chauncey McAskill <chauncey@locomotive.ca>
 */

/**
 * Trait: WordPress Facade Pattern
 *
 * @version 2015-02-06
 * @since   0.0.0 (2015-02-06)
 */

trait Facade
{

	/**
	 * @var WP  $wp  A master WordPress interface
	 */

	protected $wp;

	/**
	 * Set Facade
	 *
	 * @param  WP $facade  Allows inserting a different facade object for testing.
	 */

	public function set_facade( WP $facade = null )
	{
		$this->wp = ( empty( $facade ) ? new WP : $facade );
	}

}
