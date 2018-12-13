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
	function get_report( $from, $to, $metrics, $dimensions, $args = [] ) {

		if ( ! $this->analytics ) {
			return new \WP_Error( 500, __( 'API connection is invalid', $this->prefix ) );
		}

		$defaults = [
			'pageSize' => 100,
		];
		$args     = wp_parse_args( $args, $defaults );

		// Create the DateRange object.
		$_dateRange = new \Google_Service_AnalyticsReporting_DateRange();
		$_dateRange->setStartDate( $from );
		$_dateRange->setEndDate( $to );

		// Create the Metrics object.
		$_metrics = new \Google_Service_AnalyticsReporting_Metric();
		$_metrics->setExpression( $metrics );
		$_metrics->setAlias( str_replace( 'ga:', '', $metrics ) );

		//Create the Dimensions object.
		$_dimensions = [];
		if ( ! is_array( $dimensions ) ) {
			$dimensions = (array) $dimensions;
		}
		foreach ( $dimensions as $dimension ) {
			$_d = new \Google_Service_AnalyticsReporting_Dimension();
			$_d->setName( $dimension );
			$_dimensions[] = $_d;
		}

		// Create the Orderby object.
		$_orderby = new \Google_Service_AnalyticsReporting_OrderBy();
		$_orderby->setFieldName( $metrics );
		$_orderby->setOrderType( 'VALUE' );
		$_orderby->setSortOrder( 'DESCENDING' );

		// Create the ReportRequest object.
		$request = new \Google_Service_AnalyticsReporting_ReportRequest();
		$request->setViewId( $this->view_id );
		$request->setDateRanges( $_dateRange );
		$request->setDimensions( $_dimensions );
		$request->setMetrics( $_metrics );
		$request->setOrderBys( $_orderby );
		$request->setPageSize( $args['pageSize'] );

		$body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
		$body->setReportRequests( array( $request ) );

		return $this->analytics->reports->batchGet( $body );
	}

}
