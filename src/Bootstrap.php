<?php
namespace DBisso\Plugin\LazyWidgets;

use DBisso\Hooker\Hooker;
use DBisso\Hooker\HookableInterface;

/**
* Bootstrap the plugin
*/
class Bootstrap {
	static $instance;

	private $container = array();

	public function __construct () {
		if ( ! self::$instance instanceof Bootstrap ) {
			self::$instance = $this;
		}

		add_action( 'plugins_loaded', array( $this, 'boot' ) );
	}

	public function boot() {
		if ( ! is_admin() || $this->is_ajax() ) {
			$this->container['frontend'] = new Frontend();
		}

		$this->hook();
	}

	private function hook() {
		$hooker = new Hooker();

		foreach ( $this->container as $key => $contained ) {
			if ( $contained instanceof HookableInterface ) {
				$hooker->hook( $contained );
			}
		}
	}

	private function is_ajax() {
		if ( defined( 'DOING_AJAX' ) and DOING_AJAX ) {
			return true;
		}

		return false;
	}
}

new Bootstrap;
