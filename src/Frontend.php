<?php
namespace DBisso\Plugin\LazyWidgets;

use DBisso\Hooker\HookableInterface;

/**
 * Frontend functionality for the plugin.
 *
 * @todo Add options to widget editor to choose which to lazy load
 * @todo Add option to specify transient timeout
 * @todo Add option to choose whether to batch the AJAX requests
 */
class Frontend implements HookableInterface {
	/**
	 * Action string we look for in the request.
	 */
	const REQUEST_ACTION = 'get_widgets';

	/**
	 * Prefix for the cache key. Prepended to the widget setting hash.
	 *
	 * @var string
	 */
	static $cache_key_prefix = 'lazy_widget_';

	/**
	 * No-op the widget display callback and cache the settings for the widget in a transient.
	 *
	 * @param  array      $instance Widget instance.
	 * @param  \WP_Widget $widget   Widget.
	 * @param  array      $args     Widget arguments.
	 * @return false|null           False to no-op the display else null to use default.
	 */
	public function filter_widget_display_callback( array $instance, \WP_Widget $widget, array $args ) {
		if ( ! $this->is_ajax() ) {
			$serialized = serialize( [ get_class( $widget ), $instance, $args ] );
			$hash = wp_hash( $serialized );

			if ( false === get_transient( self::$cache_key_prefix . $hash ) ) {
				set_transient( self::$cache_key_prefix . $hash , $serialized, DAY_IN_SECONDS );
			}

			?><div class="lazy-widget--placeholder" data-widget="<?php esc_attr_e( $hash ) ?>"></div><?php
			return false;
		}
	}

	/**
	 * Look for our action in the request and build the widgets if we find it.
	 */
	public function action_template_redirect() {
		$action = get_query_var( 'action' );

		if ( ! empty( $action ) && 'get_widgets' === $action ) {
			nocache_headers();
			$this->get_widgets();
		}
	}

	/**
	 * Register our query vars.
	 */
	public function filter_query_vars( $vars ) {
		$vars[] = 'hashes';
		$vars[] = 'action';
		return $vars;
	}

	/**
	 * Enqueue our script
	 */
	public function action_wp_enqueue_scripts() {
		wp_enqueue_script( 'dbisso-lazy-widgets', plugins_url( 'dbisso-lazy-widgets/js/lazy-widgets.js' ), ['jquery'], '0.1.0_1', true );
	}

	public function filter_script_loader_tag( $tag, $handle ) {
		if ( 'dbisso-lazy-widgets' === $handle ) {
			return str_replace(' src',' defer src', $tag);
		}

		return $tag;
	}

	/**
	 * AJAX callback to return the widget contents.
	 */
	public function get_widgets() {
		$return = [];

		// Sanitize the hashes and remove and empty ones
		foreach ( array_filter( array_map( [ $this, 'clean_hash' ], $_REQUEST['hashes'] ) ) as $hash ) {

			$params     = unserialize( get_transient( self::$cache_key_prefix . $hash ) );
			$class_name = $params[0];
			$instance   = $params[1];
			$args       = $params[2];
			$query      = $params[3];
			ob_start();
			the_widget( $class_name, $instance, $args );
			$content = ob_get_clean();

			$return[ $hash ] = $content;
		}

		wp_send_json( $return );
	}

	/**
	 * Ensure our hash is a well formed?
	 *
	 * @param  string $hash The hash.
	 * @return string|false The orginal hash if it passes, otherwise false.
	 */
	private function clean_hash( $hash ) {
		if ( preg_match( '/^[a-f0-9]{32}$/', $hash ) ) {
			return $hash;
		}

		return false;
	}

	/**
	 * Is this an ajax request?
	 *
	 * @return boolean Is this an AJAX request
	 */
	private function is_ajax() {
		if ( defined( 'DOING_AJAX' ) and DOING_AJAX ) {
			return true;
		}

		return false;
	}
}