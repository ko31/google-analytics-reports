<?php

namespace Tarosky\GoogleAnalyticsReports;

/**
 * Command utility for Google Analytics Reports
 *
 * @package google-analytics-reports
 */
class Commands extends \WP_CLI_Command {

	const COMMAND_NAME = 'ga';

	/**
	 * Get search keywords.
	 *
	 * @syonpsis [--from=<from>] [--to=<to>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function search_words( $args, $assoc ) {
		\WP_CLI::success( 'Search keyword is above:' );
	}

}
