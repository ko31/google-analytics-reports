<?php

namespace Tarosky\GoogleAnalyticsReports;

use Tarosky\GoogleAnalyticsReports\Pattern\Singleton;

/**
 * Setting admin screen.
 *
 * @package GoogleAnalyticsReports
 */
final class Admin extends Singleton {


	/**
	 * Register hooks.
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Register admin menu.
	 */
	public function admin_menu() {
		add_options_page(
			__( 'Google Analytics Reports', 'google-analytics-reports' ),
			__( 'Google Analytics Reports', 'google-analytics-reports' ),
			'manage_options',
			$this->prefix,
			array( $this, 'display' )
		);
	}

	/**
	 * Register settings.
	 */
	public function admin_init() {
		register_setting( $this->prefix, $this->prefix );

		add_settings_section( 'api_settings', __( 'API settings', 'google-analytics-reports' ), function() {
			printf(
				'<p class="description">%s</p>',
				esc_html__( 'To interact with Gogole Analytics API to get stuff with report data, please set up options.', 'google-analytics-reports' )
			);
		}, $this->prefix );

		add_settings_field(
			'secret_key',
			__( 'Secret Key', 'google-analytics-reports' ),
			array( $this, 'secret_key_callback' ),
			$this->prefix,
			'api_settings'
		);

		add_settings_field(
			'view_id',
			__( 'View ID', 'google-analytics-reports' ),
			array( $this, 'view_id_callback' ),
			$this->prefix,
			'api_settings'
		);

		add_settings_field(
			'checker',
			'',
			array( $this, 'checker_callback' ),
			$this->prefix,
			'api_settings'
		);

		// Register tracking setting.
		$section_name = 'google-analytics-reports-tracking';
		add_settings_section( $section_name, __( 'Tracking Setting', 'google-analytics-reports' ), function() {
			printf(
				'<p class="description">%s</p>',
				esc_html__( 'These section will be used for ', 'google-analytics-reports' )
			);
		}, $this->prefix );
		foreach ( [
					  'author'  => _x( 'Custom Dimension / Author', 'dimension-name', 'google-analytics-reports' ),
					  'post_id' => _x( 'Custom Dimension / Post ID', 'dimension-name', 'google-analytics-reports' ),
				  ] as $key => $label ) {
			$option_key = "google-analytics-reports-{$key}";
			add_settings_field( $option_key, $label, function() use ( $option_key, $section_name, $label ) {
				printf(
					'<input name="%1$s" value="%2$s" class="regular-text" type="%3$s" /><p class="description">%4$s</p>',
					esc_attr( $option_key ),
					esc_attr( get_option( $option_key ) ),
					'number',
					esc_html( sprintf( __( 'To combine %s to page views, enter custom dimension index.', 'google-analytics-reports' ), $label ) ) // translators: %s is dimension name.
				);
			}, $this->prefix,  $section_name );
			// Register fields.
			register_setting( $this->prefix, $option_key );
		}
	}

	/**
	 * Render callback for secret key.
	 */
	public function secret_key_callback() {
		$secret_key = isset( $this->options['secret_key'] ) ? $this->options['secret_key'] : '';
		?>
		<textarea name="<?php echo $this->prefix; ?>[secret_key]" id="secret_key" rows="10" cols="50"
		          class="large-text"><?php echo $secret_key; ?></textarea>
		<?php
	}

	/**
	 * Render callback for view id.
	 */
	public function view_id_callback() {
		$view_id = isset( $this->options['view_id'] ) ? $this->options['view_id'] : '';
		?>
		<input name="<?php echo $this->prefix; ?>[view_id]" type="text" id="view_id" value="<?php echo $view_id; ?>"
		       class="regular-text">
		<?php
	}

	/**
	 * Render callback for check id.
	 */
	public function checker_callback() {
		$result = Analytics::get_instance()->check_settings();
		if ( is_wp_error( $result ) ) {
			printf( '<p class="descrpition" style="color: red;">%s</p>', esc_html( $result->get_error_message() ) );
		} else {
			printf( '<p class="description">%s</p>', esc_html__( 'API settings is valid', 'google-analytics-reports' ) );
		}
	}

	/**
	 * Admin menu render callback.
	 */
	public function display() {
		$action = untrailingslashit( admin_url() ) . '/options.php';
		?>
		<div class="wrap GoogleAnalyticsReports-settings">
			<h1 class="wp-heading-inline"><?php _e( 'Google Analytics Settings', 'GoogleAnalyticsReports' ); ?></h1>
			<form action="<?php echo esc_url( $action ); ?>" method="post">
				<?php
				settings_fields( $this->prefix );
				do_settings_sections( $this->prefix );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
