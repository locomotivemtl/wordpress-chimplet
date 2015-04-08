<?php

namespace Locomotive\Chimplet;

use Locomotive\WordPress\Facade as WP;

/**
 * File: Chimplet Feed Campaign Management
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Chimplet Feed
 *
 * @version 2015-02-13
 * @since   0.0.0 (2015-02-07)
 */

class Feed extends Base {

	/**
	 * @var WP     $wp           WordPress Facade
	 * @var array  $frequencies  Available frequencies from MailChimp
	 */

	public $wp;
	public $frequencies;


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

		$this->frequencies = [ 'monthly', 'weekly', 'daily' ];

		$this->wp->add_action( 'init',             [ $this, 'init' ], 10 );
		$this->wp->add_action( 'pre_get_posts',    [ $this, 'pre_get_posts' ], 10 );

		$this->wp->add_filter( 'get_wp_title_rss', [ $this, 'wp_title_rss' ], 10, 2 );

		if ( $this->get_option( 'mailchimp.campaigns.schedule.frequency' ) ) {
			$this->wp->add_action( 'template_redirect', [ $this, 'render_feed' ], 1 );
		}
	}

	/**
	 * Register a rewrite endpoint for the RSS feed
	 */

	public function init()
	{
		$freq_regex = implode( '|', $this->frequencies );

		add_rewrite_tag( '%chimplet_schedule%', "({$freq_regex})" );
		add_rewrite_rule( "chimplet/({$freq_regex})/?$", 'index.php?chimplet_schedule=$matches[1]', 'top' );
	}

	/**
	 * Filter returned posts for the RSS campaign
	 *
	 * @param $query
	 */

	public function pre_get_posts( &$query )
	{
		$query->is_chimplet = false;

		$schedule = $query->get( 'chimplet_schedule' );

		if ( empty( $schedule ) ) {
			return;
		}

		$tax = ( is_array( $_GET['tax'] ) ? $_GET['tax'] : [] ); //input var ok

		if ( empty( $tax ) ) {
			return;
		}

		$allowed_tax = $this->get_option( 'mailchimp.terms' );
		$tax_query   = [];

		foreach ( $tax as $tax_name => $term_ids ) {
			$term_ids = explode( ',', $term_ids );

			foreach ( $term_ids as $id ) {
				if ( ! in_array( $id, $allowed_tax[ $tax_name ] ) ) {
					// Something is not right... bail
					return;
				}
			}

			$tax_query[] = [
				'taxonomy' => $tax_name,
				'field'    => 'term_id',
				'terms'    => $term_ids,
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
		$query->set( 'chimplet_feed', true );

		$query->is_tax = true;
		$query->is_feed = true;
		$query->is_chimplet = true;

		do_action_ref_array( 'chimplet/feed/pre_get_posts', array( &$query ) );
	}

	/**
	 * Filter the blog title for use as the feed title.
	 *
	 * @param string $title The current blog title.
	 * @param string $sep   Separator used by wp_title().
	 */

	public function wp_title_rss( $title, $sep )
	{
		global $wp_query;

		if ( $wp_query->is_chimplet ) {

			$schedule = $wp_query->get( 'chimplet_schedule' );

			switch ( $schedule ) {
				case 'monthly':
					$title = " $sep " . __( 'Monthly', 'chimplet' );
					break;

				case 'weekly':
					$title = " $sep " . __( 'Weekly', 'chimplet' );
					break;

				case 'daily':
					$title = " $sep " . __( 'Daily', 'chimplet' );
					break;
			}

			if ( is_tax() ) {
				$queried_terms = ( is_array( $_GET['tax'] ) ? $_GET['tax'] : [] );

				$allowed_tax = $this->get_option( 'mailchimp.terms' );

				$terms = [];

				foreach ( $queried_terms as $tax_name => $term_ids ) {
					$term_ids = explode( ',', $term_ids );

					$term_obj = get_terms( $tax_name, [
						'fields'  => 'id=>name',
						'include' => $term_ids
					] );

					if ( ! empty( $term_obj ) ) {
						$terms = array_merge( $terms, $term_obj );
					}
				}

				$terms = array_unique( $terms );

				if ( $terms ) {
					$title .= " $sep " . implode( ', ', $terms );
				}
			}

			$title = apply_filters( 'chimplet/feed/channel/title', $title );
		}

		return $title;
	}

	/**
	 * Handle data send back to the endpoint
	 */

	public function render_feed()
	{
		global $wp_query;

		if ( ! $wp_query->get( 'chimplet_feed' ) ) {
			return;
		}

		$path = $this->get_path( 'assets/views/feed-rss2.php' );

		include $this->wp->apply_filters( "chimplet/render_feed={$path}", $this->wp->apply_filters( 'chimplet/render_feed', $path, $args, $title ), $args, $title );

		exit;
	}

	/**
	 * Generate a valid url based on the segments
	 *
	 * @param array $tax
	 * @param string $schedule
	 * @return string|void $feed_url
	 */

	public function url_from_segmented_terms( $segmented_terms, $schedule )
	{
		if ( empty( $segmented_terms['taxonomy'] ) || empty( $segmented_terms['terms'] ) ) {
			return false;
		}

		$tax   = $segmented_terms['taxonomy'];
		$terms = $segmented_terms['terms'];

		$feed_url = trailingslashit( $this->wp->site_url( trailingslashit( 'chimplet/' . urlencode( $schedule ) ) ) );
		$feed_url = add_query_arg( [ "tax[{$tax}]" => implode( ',', $terms ) ], $feed_url );

		/**
		 * Filter segment feed URL
		 *
		 * The feed URL is generated by Chimplet based on the term IDs of
		 * the MailChimp Segment and the RSS-Driven Campaign schedule options.
		 *
		 * @see SettingsPage\generate_segments_from_terms()
		 * @link https://apidocs.mailchimp.com/api/2.0/campaigns/segment-test.php
		 * @param string $feed_url The feed URL
		 * @param array $segmented_terms Documented in {@see SettingsPage::generate_segments_from_terms()}.
		 * @param array $schedule RSS-driven campaign schedule.
		 * @return string
		 */

		return $this->wp->apply_filters( 'chimplet/feed/url', $feed_url, $segmented_terms, $schedule );
	}

}
