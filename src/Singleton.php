<?php

namespace Locomotive;

/**
 * File: Singleton Pattern Class
 *
 * @package Locomotive
 * @version 2015-02-05
 * @since   0.0.0 (2015-02-05)
 * @author  Chauncey McAskill <chauncey@locomotive.ca>
 */

/**
 * Trait: Singleton Pattern
 *
 * @version 2015-02-05
 * @since   0.0.0 (2015-02-05)
 */

trait Singleton
{
	protected static $__instance;

	/**
	 * Retrieve a single reference to the current class.
	 */

	public static function get_singleton()
	{
		if ( empty( static::$__instance ) ) {
			static::$__instance = new static;
		}

		return static::$__instance;
	}

}
