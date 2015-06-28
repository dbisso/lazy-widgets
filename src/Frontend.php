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
			$hash = substr( md5( $serialized ), 0, 16 );

			set_transient( self::$cache_key_prefix . $hash , $serialized, 300 );
			?><div class="lazy-widget--placeholder" data-widget="<?php esc_attr_e( $hash ) ?>"></div><?php
			return false;
		}
	}

	/**
	 * Add the JS to the footer
	 *
	 * @todo Put this in it's own file.
	 */
	public function action_wp_footer() {
		?><script type="text/javascript" async defer>
			jQuery(function($) {
				var hashes = [];

				var widgets = $('[data-widget]').each( function() {
					var widget = $(this);
					hashes.push( widget.data('widget') );
				});

				$.get( woocommerce_params.ajax_url, {
					action: 'get_widgets',
					hashes: hashes
				} ).done( function( data ) {
					if ( data === '0' ) {
						throw 'No data received';
					}
					$.each( data, function( hash ) {
						var widget = $('[data-widget="' + hash + '"]');

						widget
							.removeClass('lazy-widget--placeholder')
							.addClass('lazy-widget--loading')
							.html( data[hash] );

						// Allow DOM to settle
						setTimeout( function() {
							widgets.addClass('lazy-widget--loaded');
						}, 1 );
					} );
				});
			});
		</script><?php
	}

	/**
	 * Anonymous AJAX callback
	 */
	public function action_wp_ajax_nopriv_get_widgets() {
		$this->action_wp_ajax_get_widgets();
	}

	/**
	 * AJAX callback to return the widget contents.
	 */
	public function action_wp_ajax_get_widgets() {
		$return = [];

		// Sanitize the hashes and remove and empty ones
		foreach ( array_filter( array_map( [ $this, 'clean_hash' ], $_REQUEST['hashes'] ) ) as $hash ) {
			$params = unserialize( get_transient( self::$cache_key_prefix . $hash ) );
			$class_name = $params[0];
			$instance = $params[1];
			$args = $params[2];

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
		if ( preg_match( '/^[a-f0-9]{16}$/', $hash ) ) {
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