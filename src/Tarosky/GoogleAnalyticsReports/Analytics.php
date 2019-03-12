<?php

namespace Tarosky\GoogleAnalyticsReports;

use Tarosky\GoogleAnalyticsReports\Pattern\Singleton;

/**
 * Customize the list table on the admin screen.
 *
 * @package GoogleAnalyticsReports
 * @property-read string $view_id
 * @property-read string $secret_key
 */
final class Analytics extends Singleton {

	private $transient_expiration;

	private $analytics;

	/**
	 * CConstructor
	 */
	protected function __construct() {
		$this->transient_expiration = apply_filters( 'google_analytics_reporters_interval', DAY_IN_SECONDS );
		$this->analytics            = $this->initialize_analytics();
	}

	/**
	 * Initializes an Analytics Reporting API V4 service object.
	 *
	 * @return \WP_Error|\Google_Service_AnalyticsReporting An authorized Analytics Reporting API V4 service object.
	 */
	public function initialize_analytics() {

		if ( empty( $this->secret_key ) || empty( $this->view_id ) ) {
			// TODO: Writer todo.
			return new \WP_Error( 500, __( 'Option is not set.', 'google-analytics-reporters' ) );
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
			return new \WP_Error( 500, $e->getMessage() );
		}

		return $analytics;
	}

	/**
	 * Check settings
	 *
	 * @return bool|\WP_Error
	 */
	function check_settings() {
		if ( empty( $this->secret_key ) || empty( $this->view_id ) ) {
			return new \WP_Error( 500, __( 'Option is not set.', 'google-analytics-reports' ) );
		}

		json_decode( $this->secret_key );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error( 500, __( 'Secret Key is invalid', 'google-analytics-reports' ) );
		}

		if ( ! preg_match( "/^[0-9]+$/", $this->view_id ) ) {
			return new \WP_Error( 500, __( 'View ID is invalid', 'google-analytics-reports' ) );
		}

		try {
			$request = new \Google_Service_AnalyticsReporting_ReportRequest();
			$request->setViewId( $this->view_id );

			$body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
			$body->setReportRequests( [ $request ] );

			$result = $this->analytics->reports->batchGet( $body );

			return true;

		} catch ( \Exception $e ) {

			$result = json_decode( $e->getMessage() );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return new \WP_Error( 500, sprintf( __( 'API settings is Invalid (%s %s)', 'google-analytics-reports' ), $result->error->code, $result->error->message ) );
			} else {
				return new \WP_Error( 500, sprintf( __( 'API settings is invalid: %s', 'google-analytics-reports' ), $e->getMessage() ) );
			}
		}
	}

	/**
	 * Queries the Analytics Reporting API V4.
	 *
	 * @link https://developers.google.com/analytics/devguides/reporting/core/v4/quickstart/service-php
	 * @link https://developers.google.com/analytics/devguides/reporting/core/v4/rest/v4/reports/batchGet
	 *
	 * @param array $args
	 *
	 * @return \WP_Error|\Google_Service_AnalyticsReporting_GetReportsResponse The Analytics Reporting API V4 response.
	 */
	function get_report( $args = [] ) {

		if ( empty( $this->analytics ) ) {
			return new \WP_Error( 500, __( 'Google Analytics setting is invalid.', 'google-analytics-reports' ) );
		}

		$defaults = [
			'from'            => '7daysAgo',
			'to'              => 'today',
			'metrics'         => 'ga:sessions',
			'dimensions'      => 'ga:pagePath',
			'dimensionFilter' => [],
			'pageSize'        => 100,
			'sortFieldName'   => 'ga:sessions',
			'sortOrderType'   => 'VALUE',
			'sortOrder'       => 'DESCENDING',
		];
		$args     = wp_parse_args( $args, $defaults );

		$transient_key = md5( serialize( $args ) );
		$result        = get_transient( $transient_key );
		if ( false !== $result ) {
			return $result;
		}

		try {

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

			// Create the Dimensions object.
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
			$request->setDateRanges( [ $dateRange ] );
			$request->setDimensions( [ $dimensions ] );
			$request->setMetrics( [ $metrics ] );
			$request->setOrderBys( [ $orderby ] );
			$request->setPageSize( $args['pageSize'] );

			// Create the DimensionFilter
			if ( ! empty( $args['dimensionFilter'] ) ) {

				$dimensionFilters = [];

				$args['dimensionFilter'] = (array) $args['dimensionFilter'];

				foreach ( $args['dimensionFilter'] as $filter ) {

					// Create the DimensionFilter object.
					$dimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
					$dimensionFilter->setDimensionName( $filter['dimensionName'] );
					$dimensionFilter->setOperator( $filter['operator'] );
					$dimensionFilter->setExpressions( $filter['expressions'] );
					if ( isset( $filter['not'] ) ) {
						$dimensionFilter->setNot( $filter['not'] );
					}

					$dimensionFilters[] = $dimensionFilter;
				}

				$dimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
				$dimensionFilterClause->setFilters( $dimensionFilters );
				$dimensionFilterClause->setOperator( 'and' );

				$request->setDimensionFilterClauses( $dimensionFilterClause );
			}

			// Call the batchGet method.
			$body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
			$body->setReportRequests( [ $request ] );

			$result = $this->analytics->reports->batchGet( $body );

			delete_transient( $transient_key );
			/**
			 * gar_set_transient_expiration
			 *
			 * @param int $expiration
			 *
			 * @return int
			 */
			$expiration = apply_filters( 'gar_set_transient_expiration', $this->transient_expiration );
			set_transient( $transient_key, $result, $expiration );

			return $result;

		} catch ( \Exception $e ) {

			$result = json_decode( $e->getMessage() );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return new \WP_Error( 500, sprintf( __( 'API request failed (%s %s)', 'google-analytics-reports' ), $result->error->code, $result->error->message ) );
			}

			return new \WP_Error( 500, __( 'API request failed', 'google-analytics-reports' ) );
		}


	}

	/**
	 * This class uses empty.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset( $name ) {
		switch ( $name ) {
			case 'view_id':
			case 'secret_key':
				return true;
			default:
				return parent::__isset( $name );
		}
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'secret_key':
			case 'view_id':
				return isset( $this->options[ $name ] ) ? $this->options[ $name ] : '';
			default:
				return parent::__get( $name );
		}
	}

}
