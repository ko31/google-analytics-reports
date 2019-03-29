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
	 * @throws \Exception
	 */
	public function search_words( $args, $assoc ) {
		$result = gar_get_search_words( [
			'from'     => $assoc['from'] ?? '7daysAgo',
			'to'       => $assoc['to'] ?? 'today',
			'pageSize' => $assoc['pagesize'] ?? 100,
		] );
		if ( ! $result ) {
			\WP_CLI::error( 'No keywords found. Please change period and try again.' );
		} elseif ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}
		$table = new \cli\Table();
		$table->setHeaders( [ 'Keyword', 'Sessions' ] );
		$table->setRows( $result );
		$table->display();
		\WP_CLI::success( sprintf('%d keywords found.', count( $result ) ) );
	}
	
	/**
	 * Get popular ranking.
	 *
	 * @synopsis [--from=<from>] [--to=<to>] [--exclude_type=<exclude_type>] [--limit=<limit>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function get_ranking( $args, $assoc ) {
		$from  = $assoc['from'] ?? '7daysAgo';
		$to    = $assoc['to'] ?? 'today';
		$limit = intval( $assoc['limit'] ?? 10 );
		$exclude_type = array_filter( explode( ',', ( $assoc['exclude_type'] ?? $assoc['exclude_type'] ) ), function( $post_type ) {
			return ! empty( $post_type ) && post_type_exists( $post_type );
		} );
		$extra = [];
		if ( $exclude_type ) {
			$extra['dimensionFilter'] = [
				[
					'dimensionName' => sprintf( 'ga:dimension%d', get_option( 'google-analytics-reports-type' ) ),
					'operator'      => 'IN_LIST',
					'expressions'   => $exclude_type,
					'not'           => true,
				]
			];
		}
		$result = gar_post_precise_ranking( $from, $to, $limit, $extra );
		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}
		if ( ! $result ) {
			\WP_CLI::error( 'No results found.' );
		}
		$table = new \cli\Table();
		$table->setHeaders( [ 'Rank', 'ID', 'Title', 'PV'] );
		$table->setRows( array_map( function( $post ) {
			return [
				$post->rank,
				sprintf( '#%d', $post->ID ),
				get_the_title( $post ),
				number_format_i18n( $post->pv ),
			];
		}, $result ) );
		$table->display();
	}
}
