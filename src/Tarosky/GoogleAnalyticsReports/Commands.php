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
	 * @syonpsis [--from=<from>] [--to=<to>] [--pagesize=<pagesize>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function search_words( $args, $assoc ) {
		$result = gar_get_search_words( [
			'from'     => $assoc['from'] ?? '7daysAgo',
			'to'       => $assoc['to'] ?? 'today',
			'pageSize' => $assoc['pagesize'] ?? 100,
		] );
		if ( ! $result ) {
			\WP_CLI::error( 'No keywords found. Please change period and try again.' );
		}
		$table = new \cli\Table();
		$table->setHeaders( [ 'Keyword', 'Sessions' ] );
		$table->setRows( $result );
		$table->display();
		\WP_CLI::success( sprintf('%d keywords found.', count( $result ) ) );
	}
}
