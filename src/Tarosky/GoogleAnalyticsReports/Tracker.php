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
	 * Detect WCGA is activated.
	 *
	 * @return bool
	 */
	public function woocommerce_google_analytics_exists() {
		return class_exists( 'WC_Google_Analytics_Integration' );
	}
	
	/**
	 * Tracker constructor.
	 */
	protected function __construct() {
		add_filter( 'woocommerce_ga_snippet_create', [ $this, 'woocommerce_ga_snippet' ] );
		add_action( 'wp_head', [ $this, 'render_tracking_code' ], 1 );
	}

	/**
	 * Get extra tags.
	 */
	public function get_extra_tags() {
		$codes = [];
		foreach ( [
			'post_id' => is_singular() ? get_queried_object_id() : null,
			'author'  => is_singular() ? get_queried_object()->post_author : null,
			'type'    => is_singular() ? get_queried_object()->post_type : null,
		] as $key => $value ) {
			$index = get_option( "google-analytics-reports-{$key}" );
			$value = apply_filters( 'google_analytics_reporters_dimension_value', $value, $key );
			if ( ! $index || is_null( $value ) ) {
				continue;
			}
			$codes[] = sprintf( "ga('set', 'dimension%d', '%s');", $index, esc_js( $value ) );
		}
		$codes = apply_filters( 'google_analytics_reports_code_array', $codes );
		return implode( "\n", $codes );
	}
	
	/**
	 * Page view path.
	 *
	 * @return string
	 */
	private function page_view_path() {
		return apply_filters( 'google_analytics_pave_view_path', '' );
	}
	
	/**
	 * Render tracking code if woocommece_ga is not enabled.
	 */
	public function render_tracking_code() {
		$tracking_id = get_option( 'google-analytics-reports-tracking_id' );
		if ( ! $tracking_id || $this->woocommerce_google_analytics_exists() ) {
			// Do nothing.
			return;
		}
		?>
		<!-- Google Analytics -->
		<script>
			window.google_analytics_uacct = "<?= esc_js( $tracking_id ) ?>";
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
			
			ga( 'create', '<?php echo esc_attr( $tracking_id ) ?>', 'auto' );
			ga( 'require', 'displayfeatures' );
			ga( 'require', 'linkid', 'linkid.js' );
			<?php echo $this->get_extra_tags(); ?>
			<?php if ( $path = $this->page_view_path() ) : ?>
				ga( 'send', 'pageview', '<?php echo esc_attr( $path ) ?>' );
			<?php else : ?>
				ga( 'send', 'pageview' );
			<?php endif; ?>
		</script>
		<!-- End Google Analytics -->
		<?php
		$codes = $this->get_extra_tags();
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
