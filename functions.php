<?php
/**
 * Utility functions
 *
 * @package GoogleAnalyticsReports
 */

/**
 * Get plugin version
 *
 * @since 1.0.0
 * @return string
 */
function gar_version() {
	static $info = null;
	if ( is_null( $info ) ) {
		$info = get_file_data( __DIR__ . '/google-analytics-reports.php', [
			'version' => 'Version',
		] );
	}

	return $info['version'];
}

/**
 * Get reports
 *
 * @since 1.0.0
 * @return array
 */
function gar_reports( $args = [] ) {
	$reports = GoogleAnalyticsReports\Analytics::get_instance()->get_report( $args );

	return $reports;
}

/**
 * Get post from URL
 *
 * @since 1.0.0
 * @return null|WP_post
 */
function gar_url_to_post( $url ) {
	$post_id = url_to_postid( $url );
	if ( empty( $post_id ) ) {
		return null;
	}

	return get_post( $post_id );
}
