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
function gar_reports( $from, $to, $metrics, $dimensions, $args = [] ) {
	$reports = GoogleAnalyticsReports\Analytics::get_instance()->get_report( $from, $to, $metrics, $dimensions, $args );

	return $reports;
}
