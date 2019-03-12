<?php

namespace Tarosky\GoogleAnalyticsReports\Pattern;


use Tarosky\GoogleAnalyticsReports;

/**
 * Singleton pattern.
 *
 * @package google-analytics-reports
 * @property-read GoogleAnalyticsReports $google_analytics_reports
 * @property-read array                  $options
 * @property-read string                 $prefix
 */
abstract class Singleton {

	private static $instances = [];

	/**
	 * Get instance
	 *
	 * @return static
	 */
	public static function get_instance() {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name();
		}
		return self::$instances[ $class_name ];
	}


	/**
	 * This class uses empty.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset( $name ) {
		switch ( $name ) {
			case 'prefix';
			case 'options':
				return true;
			default:
				return false;
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
			case 'google_analytics_reports':
				return GoogleAnalyticsReports::get_instance();
			case 'prefix':
				return $this->google_analytics_reports->get_prefix();
			case 'options':
				return get_option( $this->prefix, [] );
			default:
				return null;
		}
	}
}
