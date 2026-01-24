<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Autoloader {

	public static function register() {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	private static function autoload( $class ) {
		if ( strpos( $class, __NAMESPACE__ . '\\' ) !== 0 ) {
			return;
		}

		$path = strtolower(
			str_replace(
				[ __NAMESPACE__ . '\\', '\\' ],
				[ '', '-' ],
				$class
			)
		);

		$file = PPLS_PATH . 'includes/class-' . $path . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

Autoloader::register();
