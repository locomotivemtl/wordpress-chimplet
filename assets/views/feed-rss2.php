<?php

/**
 * File: RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package Locomotive\Chimplet\Views
 */

global $wp_query;

$schedule = $wp_query->get( 'chimplet_schedule' );

if ( in_array( $schedule, [ 'hourly', 'daily', 'weekly', 'monthly', 'yearly' ] ) ) {
	$duration = $schedule;
}
else {
	$duration = 'hourly';
}

header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>'; //xss ok

/**
 * Fires between the xml and rss tags in a feed.
 *
 * @since 4.0.0
 *
 * @param string $context Type of feed. Possible values include 'rss2', 'rss2-comments',
 *                        'rdf', 'atom', and 'atom-comments'.
 */
do_action( 'rss_tag_pre', 'rss2' );

?>
<rss version="2.0"<?php
	?> xmlns:media="http://search.yahoo.com/mrss/"<?php
	?> xmlns:content="http://purl.org/rss/1.0/modules/content/"<?php
	?> xmlns:dc="http://purl.org/dc/elements/1.1/"<?php
	?> xmlns:atom="http://www.w3.org/2005/Atom"<?php
	?> xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"<?php
	?> xmlns:slash="http://purl.org/rss/1.0/modules/slash/"<?php

	/**
	 * Fires at the end of the RSS root to add namespaces.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss2_ns' );

	?>>
<channel>
	<title><?php bloginfo_rss( 'name' ); wp_title_rss(); ?></title>
	<?php

	$_request_uri   = $_SERVER['REQUEST_URI'];
	$_request_parts = explode( '?', $_SERVER['REQUEST_URI'] );

	wp_parse_str( end( $_request_parts ), $_query_uri );

	$_SERVER['REQUEST_URI'] = reset( $_request_parts );

	$host = parse_url( home_url() );

	?><atom:link href="<?php echo add_query_arg( $_query_uri, esc_url( apply_filters( 'self_link', set_url_scheme( 'http://' . $host['host'] . wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) ); ?>" rel="self" type="application/rss+xml" />
	<?php

	$_SERVER['REQUEST_URI'] = $_request_uri;

	?><link><?php bloginfo_rss( 'url' ) ?></link>
	<description><?php bloginfo_rss( 'description' ) ?></description>
	<lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ); //xss ok ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<?php

	/**
	 * Filter how often to update the RSS feed.
	 *
	 * @param string $duration The update period.
	 *                         Default 'hourly'. Accepts 'hourly', 'daily', 'weekly', 'monthly', 'yearly'.
	 */

	?><sy:updatePeriod><?php echo apply_filters( 'rss_update_period', $duration ); //xss ok ?></sy:updatePeriod>
	<?php

	$frequency = '1';

	/**
	 * Filter the RSS update frequency.
	 *
	 * @param string $frequency An integer passed as a string representing the frequency
	 *                          of RSS updates within the update period. Default '1'.
	 */

	?><sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', $frequency ); //xss ok ?></sy:updateFrequency>
	<?php

	/**
	 * Fires at the end of the RSS2 Feed Header.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss2_head' );

	while ( have_posts() ) : the_post();
	?>
	<item>
		<title><?php the_title_rss() ?></title>
		<link><?php the_permalink_rss() ?></link>
		<comments><?php comments_link_feed(); ?></comments>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); //xss ok ?></pubDate>
		<dc:creator><![CDATA[<?php the_author() ?>]]></dc:creator>
		<?php the_category_rss( 'rss2' ) ?>

		<guid isPermaLink="false"><?php the_guid(); ?></guid>
<?php if ( get_option( 'rss_use_excerpt' ) ) : ?>
		<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
<?php else : ?>
		<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
		<?php if ( get_the_post_thumbnail() ) : ?>
            <media:content url="<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), apply_filters( 'chimplet/feed/template/image_size', 'thumbnail' ) ); echo $image[0]; //xss ok ?>" medium="image" />
        <?php endif; ?>
	<?php $content = get_the_content_feed( 'rss2' ); ?>
	<?php if ( strlen( $content ) > 0 ) : ?>
		<content:encoded><![CDATA[<?php echo $content; //xss ok ?>]]></content:encoded>
	<?php else : ?>
		<content:encoded><![CDATA[<?php the_excerpt_rss(); ?>]]></content:encoded>
	<?php endif; ?>
<?php endif; ?>
<?php rss_enclosure(); ?>
	<?php
	/**
	 * Fires at the end of each RSS2 feed item.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss2_item' );
	?>
	</item>
	<?php endwhile; ?>
</channel>
</rss>
