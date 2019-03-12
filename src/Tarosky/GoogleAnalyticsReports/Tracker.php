<?php

namespace Tarosky\GoogleAnalyticsReports;


use Tarosky\GoogleAnalyticsReports\Pattern\Singleton;

/**
 * Tracker class
 *
 * @package google-analytics-reports
 */
class Tracker extends Singleton {

	/**
	 * Tracker constructor.
	 */
	protected function __construct() {
		add_filter( 'woocommerce_ga_snippet_create', [ $this, 'woocommerce_ga_snippet' ] );
	}

	/**
	 * Get extra tags.
	 */
	public function get_extra_tags() {
		$codes = [];
		foreach ( [
			'post_id' => is_singular() ? get_queried_object_id() : null,
			'author'  => is_singular() ? get_queried_object()->post_author : null,
		] as $key => $value ) {
			$index = get_option( "google-analytics-reports-{$key}" );
			if ( ! $index || is_null( $value ) ) {
				continue;
			}
			$codes[] = sprintf( "ga('set', 'dimension%d', '%s');", $index, esc_js( $value ) );
		}
		$codes = apply_filters( 'google_analytics_reports_code_array', $codes );
		return implode( "\n", $codes );
	}

	/**
	 * Add tracking code to WGI.
	 *
	 * @param string $snippet
	 * @return string
	 */
	public function woocommerce_ga_snippet( $snippet ) {
		$codes = $this->get_extra_tags();
		if ( $codes ) {
			$snippet .= $codes;
		}
		return $snippet;
	}

}
