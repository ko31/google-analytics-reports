<?php

namespace Tarosky\GoogleAnalyticsReports;


use Tarosky\GoogleAnalyticsReports\Pattern\Singleton;

/**
 * Handle UUID for Google Analytics.
 *
 * @package google-analytics-reports
 */
class Uuid extends Singleton {
	
	/**
	 * Uuid constructor
	 */
	protected function __construct() {
		// Check if cookie tasting exist.
		if ( ! function_exists( 'cookie_tasting_version' ) ) {
			return;
		}
		// Register option.
		add_filter( 'google_analytics_reports_options', [ $this, 'option_filter' ] );
		// Change description.
		add_filter( 'google_analytics_reports_option_desc', [ $this, 'option_desc' ], 10, 2 );
		// Add extra tags.
		add_filter( 'google_analytics_reports_code_array', [ $this, 'add_tracking_code' ] );
	}
	
	/**
	 * Add option.
	 *
	 * @param array $options
	 * @return array
	 */
	public function option_filter( $options ) {
		$options[ 'user_id' ] = __( 'User ID Tracking', 'google-analytics-reports' );
		return $options;
	}
	
	/**
	 *
	 *
	 * @param string $desc
	 * @param string $key
	 *
	 * @return string
	 */
	public function option_desc( $desc, $key ) {
		if ( 'user_id' === $key ) {
			$desc = __( 'Set custom dimension index of user scope. An unique id will be set to every user and also set to User ID View.', 'google-analytics-reports' );
		}
		return $desc;
	}
	
	/**
	 * Add tracking code.
	 *
	 * @param array $codes
	 * @return array
	 */
	public function add_tracking_code( $codes ) {
		if ( $index = get_option( 'google-analytics-reports-user_id' ) ) {
			$js = <<<'JS'
				!function(){
  					if ( ! window.CookieTasting ) {
  					  return;
  					}
  					var uuid = CookieTasting.get( 'uuid' );
  					if ( ! uuid ) {
  					  return;
  					}
  					ga( 'set', 'dimension%d', uuid );
  					ga( 'set', 'userId', uuid );
				}();
JS;
			$js = sprintf( $js, $index );
			$codes = array_merge( [ implode( ' ', array_map( 'trim', explode( "\n", $js ) ) ) ], $codes );
		}
		return $codes;
	}
}
