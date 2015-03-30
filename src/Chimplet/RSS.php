<?php

namespace Locomotive\Chimplet;

use Locomotive\WordPress\Facade as WP;

/**
 * File: Chimplet RSS campaign management
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Settings Page
 *
 * @version 2015-02-13
 * @since   0.0.0 (2015-02-07)
 */

class RSS extends Base {

	/**
	 * @var WP  $wp  WordPress Facade
	 */

	public $wp;


	/**
	 * Constructor
	 *
	 * Prepares actions and filters for commodity methods.
	 *
	 * @access public
	 * @param  WP  $wp  The WordPress Facade
	 */

	public function __construct( WP $wp = null )
	{
		$this->wp = ( $wp instanceof WP ? $wp : new WP );

		$this->wp->add_action( 'init',              [ $this, 'init' ], 10 );
		$this->wp->add_action( 'pre_get_posts',     [ $this, 'pre_get_posts' ], 10 );

		if ( $this->get_option( 'mailchimp.campaigns.schedule.frequency' ) ) {
			$this->wp->add_action( 'template_redirect', [ $this, 'generate_rss_feed' ], 1 );
		}
	}

	/**
	 * Register a rewrite endpoint for the RSS feed
	 */

	public function init()
	{
		add_rewrite_tag( '%chimplet_schedule%', '(monthly|weekly|daily)' );
		add_rewrite_rule( 'chimplet/(monthly|weekly|daily)/?$', 'index.php?chimplet_schedule=$matches[1]', 'top' );
	}

	/**
	 * Filter returned posts for the RSS campaign
	 *
	 * @param $query
	 */

	public function pre_get_posts( $query )
	{
		$schedule = $query->get( 'chimplet_schedule' );

		if ( empty( $schedule ) ) {
			return;
		}

		$tax = is_array( $_GET['tax'] ) ? $_GET['tax'] : ''; //input var ok

		if ( empty( $tax ) ) {
			return;
		}

		$allowed_tax = $this->get_option( 'mailchimp.terms' );
		$tax_query   = [];

		foreach ( $tax as $tax_name => $tax_ids ) {

			$tax_ids = explode( ',', $tax_ids );

			foreach ( $tax_ids as $id ) {

				if ( ! in_array( $id, $allowed_tax[ $tax_name ] ) ) {
					// Something is not right... bail
					return;
				}
			}

			$tax_query[] = [
				'taxonomy' => $tax_name,
				'field'    => 'term_id',
				'terms'    => $tax_ids,
			];
		}

		// Build date_query
		switch ( $schedule ) {
			case 'monthly':
				$date_query = [
					[
						'column' => 'post_date_gmt',
						'after' => '1 month ago',
					]
				];
				break;

			case 'weekly':
				$date_query = [
					[
						'year' => date( 'Y' ),
						'week' => date( 'W' ),
					]
				];
				break;

			case 'daily':
				$today = getdate();
				$date_query = [
					[
						'year'  => $today['year'],
						'month' => $today['mon'],
						'day'   => $today['mday'],
					]
				];
				break;

			default:
				// That shouldn't happen
				return;
				break;
		}

		// Modify wp_query to our needs
		$query->set( 'post_type', 'post' );
		$query->set( 'tax_query', $tax_query );
		$query->set( 'posts_per_page', -1 );
		$query->set( 'date_query', $date_query );
		$query->set( 'chimplet_rss', true );
	}

	/**
	 * Handle data send back to the endpoint
	 */

	public function generate_rss_feed()
	{
		global $wp_query;

		if ( ! $wp_query->get( 'chimplet_rss' ) ) {
			return;
		}

		$path = $this->get_path( 'assets/rss/mailchimp-feed-rss2.php' );

		include apply_filters( 'chimplet/rss/template/path', $path );

		exit;
	}

	/**
	 * Generate a valid url based on the segments
	 *
	 * @param array $tax
	 * @param string $schedule
	 * @return string|void
	 */

	public function create_rss_url( $tax, $schedule )
	{
		$url = trailingslashit( get_bloginfo( 'url' ) ) . '?';

		foreach ( $tax as $tax_name => $tax_ids ) {
			$url .= sprintf( '%s[%s]&', $tax_name, implode( ',', $tax_ids ) );
		}

		$url = trim( $url, '&' );

		return $url;
	}

}
