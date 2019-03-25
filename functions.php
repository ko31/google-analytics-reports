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
 * @param array $args
 * @return \WP_Error|\Google_Service_AnalyticsReporting_GetReportsResponse
 */
function gar_reports( $args = [] ) {
	$reports = Tarosky\GoogleAnalyticsReports\Analytics::get_instance()->get_report( $args );

	return $reports;
}

/**
 * Get reports in post
 *
 * @since 1.0.0
 * @param array $args
 * @return array
 */
function gar_report_posts( $args = [] ) {
	$posts = [];

	$reports  = gar_reports( $args );

	if ( is_wp_error( $reports ) ) {
		return $posts;
	}

	$page_path_index = ( ! empty( $args['page_path_index'] ) ? $args['page_path_index'] : 0 );

	$report = $reports[0];
	$rows   = $report->getData()->getRows();
	for ( $i = 0; $i < count( $rows ); $i ++ ) {
		$row        = $rows[ $i ];
		$dimensions = $row->getDimensions();
		$metrics    = $row->getMetrics();
		$values     = $metrics[0]->getValues();

		$_post = gar_url_to_post( $dimensions[ $page_path_index ] );
		if ( empty( $_post ) ) {
			$_post = new \stdClass();
		}
		$_post->dimensions = $dimensions;
		$_post->metrics    = $values;

		$posts[] = $_post;
	}

	return $posts;
}

/**
 * Get author ranking.
 *
 * ## Example
 *
 * foreach ( gar_author_pv_ranking() as list( $user_id, $pv ) {
 *     // Do stuff.
 * }
 *
 * @param $args
 * @return array|WP_Error array of [ $user_id, $pv ]
 */
function gar_author_pv_ranking( $args = [] ) {
	$dimension_index = get_option( 'google-analytics-reports-author' );
	if ( ! $dimension_index ) {
		return new WP_Error( 'not_set', __( 'This site does not collect author score.', 'google-analytics-reports' ), [
			'status' => 500,
		] );
	}
	$args = wp_parse_args( $args, [
		'dimensions'    => sprintf( 'ga:dimension%d', $dimension_index ),
		'metrics'       => 'ga:pageviews',
		'sortFieldName' => 'ga:pageviews',
	] );
	$args = apply_filters( 'google_analytics_reporters_author_pv_ranking', $args );
	$response = gar_reports( $args );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$results = [];
	foreach ( $response[0]->getData()->getRows() as $row ) {
		$dimensions = $row->dimensions;
		foreach ( $row->metrics as $metric ) {
			foreach ( $metric->values as $value ) {
				$dimensions[] = (int) $value;
			}
		}
		$results[] = $dimensions;
	}
	return $results;
}

/**
 * Get precise post data.
 *
 * @since 1.0.2
 * @param string $from  From date. Default '7daysAgo'
 * @param string $to    Default 'today'.
 * @param int    $limit Default 10.
 * @return array|WP_Error
 */
function gar_post_precise_ranking( $from = '7daysAgo', $to = 'today', $limit = 10 ) {
	$dimension_index = get_option( 'google-analytics-reports-post_id' );
	if ( ! $dimension_index ) {
		return new WP_Error( 'not_set', __( 'This site does not collect posts\' page views.', 'google-analytics-reports' ), [
			'status' => 500,
		] );
	}
	$args = [
		'from'          => $from,
		'to'            => $to,
		'pageSize'      => $limit * 2, // In case post's deletion.
		'dimensions'    => sprintf( 'ga:dimension%d', $dimension_index ),
		'metrics'       => 'ga:pageviews',
		'sortFieldName' => 'ga:pageviews',
	];
	$args     = apply_filters( 'google_analytics_reporters_ranking_per_post', $args );
	$response = gar_reports( $args );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$results = [];
	foreach ( $response[0]->getData()->getRows() as $row ) {
		$dimensions = $row->dimensions;
		foreach ( $row->metrics as $metric ) {
			foreach ( $metric->values as $value ) {
				$dimensions[] = (int) $value;
			}
		}
		$results[] = $dimensions;
	}
	$filtered = [];
	$found = 0;
	foreach ( $results as list( $post_id, $pv ) ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			continue;
		}
		$post->pv = $pv;
		$filtered[] = $post;
		if ( count( $filtered ) >= $limit ) {
			break;
		}
	}
	foreach ( $filtered as $index => $post ) {
		// Count other posts which have more pv than that,
		$more = 0;
		foreach ( $filtered as $p ) {
			if ( $p->pv > $post->pv ) {
				$more++;
			}
		}
		$post->rank = $more + 1;
	}
	return $filtered;
}

/**
 * Get post from URL
 *
 * @since 1.0.0
 * @param string $url
 * @return null|WP_post
 */
function gar_url_to_post( $url ) {
	$post_id = url_to_postid( $url );
	if ( empty( $post_id ) ) {
		return null;
	}
	$dimension_index = get_option( 'google-analytics-reports-post_id' );
	if ( ! $dimension_index ) {
	
	}
	return get_post( $post_id );
}

/**
 * Get search keywords.
 *
 * @param array $args
 *     *from     From date. Default '7daysAgo'.
 *     *to       To date. Default 'today'.
 *     *pazeSize Page size. Default 100.
 * @return \WP_Error|array
 */
function gar_get_search_words( $args = [] ) {
	$args = wp_parse_args( $args, [
		'dimensions'    => 'ga:searchKeyword',
		'metrics'       => 'ga:searchUniques',
		'sortFieldName' => 'ga:searchUniques',
	] );
	$result = gar_reports( $args );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$rows = $result[0]->getData()->getRows();
	$filtered = [];
	for ( $i = 0, $l = count( $rows ); $i < $l; $i++ ) {
		$row = $rows[ $i ];
		$dimensions = $row->dimensions;
		foreach ( $row->metrics as $metric ) {
			foreach ( $metric->values as $value ) {
				$dimensions[] = (int) $value;
			}
		}
		$filtered[] = $dimensions;
	}
	return $filtered;
}
