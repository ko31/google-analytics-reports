<?php

namespace GoogleAnalyticsReports;

/**
 * Customize the list table on the admin screen.
 *
 * @package GoogleAnalyticsReports
 */
final class Analytics {
	private $prefix;
	private $options;

	private $secret_key;
	private $view_id;
	private $analytics;

	public function __construct() {
		$this->prefix    = \GoogleAnalyticsReports::get_instance()->get_prefix();
		$this->options   = get_option( $this->prefix );
		$this->analytics = $this->initialize_analytics();
	}

	public static function get_instance() {
		static $instance;
		if ( ! $instance ) {
			$instance = new Analytics();
		}

		return $instance;
	}

	/**
	 * Initializes an Analytics Reporting API V4 service object.
	 *
	 * @return An authorized Analytics Reporting API V4 service object.
	 */
	public function initialize_analytics() {
		$this->secret_key = $this->options['secret_key'];
		$this->view_id    = $this->options['view_id'];

		if ( empty( $this->secret_key ) || empty( $this->view_id ) ) {
			// TODO:
		}

		$handle = tmpfile();
		$meta   = stream_get_meta_data( $handle );
		file_put_contents( $meta['uri'], $this->secret_key );
		$key_file = $meta['uri'];

		try {
			// Create and configure a new client object.
			$client = new \Google_Client();
			$client->setApplicationName( "Analytics Reporting" );
			$client->setAuthConfig( $key_file );
			$client->setScopes( [ 'https://www.googleapis.com/auth/analytics.readonly' ] );
			$analytics = new \Google_Service_AnalyticsReporting( $client );
		} catch ( \Exception $e ) {
			// TODO:
			return new \WP_Error( 500, __( $e->getMessage(), $this->prefix ) );
		}

		return $analytics;
	}

	/**
	 * Queries the Analytics Reporting API V4.
	 *
	 * @param string $from
	 * @param string $to
	 * @param string $metrics
	 * @param array $dimensions
	 * @param array $args
	 *
	 * @return The Analytics Reporting API V4 response.
	 */
	function get_report( $args = [] ) {

		if ( ! $this->analytics ) {
			return new \WP_Error( 500, __( 'API connection is invalid', $this->prefix ) );
		}

		$defaults = [
			'from'          => '7daysAgo',
			'to'            => 'today',
			'metrics'       => 'ga:sessions',
			'dimensions'    => 'ga:pagePath',
			'pageSize'      => 100,
			'sortFieldName' => 'ga:sessions',
			'sortOrderType' => 'VALUE',
			'sortOrder'     => 'DESCENDING',
		];
		$args     = wp_parse_args( $args, $defaults );

		// Create the DateRange object.
		$dateRange = new \Google_Service_AnalyticsReporting_DateRange();
		$dateRange->setStartDate( $args['from'] );
		$dateRange->setEndDate( $args['to'] );

		// Create the Metrics object.
		$metrics = [];
		if ( ! is_array( $args['metrics'] ) ) {
			$args['metrics'] = (array) $args['metrics'];
		}
		array_map( function ( $metric ) use ( &$metrics ) {
			$_m = new \Google_Service_AnalyticsReporting_Metric();
			$_m->setExpression( $metric );
			$_m->setAlias( str_replace( 'ga:', '', $metric ) );
			$metrics[] = $_m;
		}, $args['metrics'] );

		//Create the Dimensions object.
		$dimensions = [];
		if ( ! is_array( $args['dimensions'] ) ) {
			$args['dimensions'] = (array) $args['dimensions'];
		}
		array_map( function ( $dimension ) use ( &$dimensions ) {
			$_d = new \Google_Service_AnalyticsReporting_Dimension();
			$_d->setName( $dimension );
			$dimensions[] = $_d;
		}, $args['dimensions'] );

		// Create the Orderby object.
		$orderby = new \Google_Service_AnalyticsReporting_OrderBy();
		$orderby->setFieldName( $args['sortFieldName'] );
		$orderby->setOrderType( $args['sortOrderType'] );
		$orderby->setSortOrder( $args['sortOrder'] );

		// Create the ReportRequest object.
		$request = new \Google_Service_AnalyticsReporting_ReportRequest();
		$request->setViewId( $this->view_id );
		$request->setDateRanges( $dateRange );
		$request->setDimensions( $dimensions );
		$request->setMetrics( $metrics );
		$request->setOrderBys( $orderby );
		$request->setPageSize( $args['pageSize'] );

		$body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
		$body->setReportRequests( array( $request ) );

		return $this->analytics->reports->batchGet( $body );
	}

}
