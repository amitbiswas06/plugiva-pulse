<?php
namespace Plugiva\Pulse;

/**
 * Class Autoloader
 *
 * Registers a SPL autoloader for Plugiva Pulse namespaced classes.
 *
 * Design rules:
 * - Only classes inside the `Plugiva\Pulse` namespace are handled.
 * - Class names are mapped to files inside the `includes/` directory.
 * - File naming convention:
 *     Class_Name        → class-class-name.php
 *     Pulse_Renderer    → class-pulse-renderer.php
 *
 * Important:
 * - This autoloader is reactive, not proactive.
 * - It only runs when PHP encounters an undefined class.
 * - It does NOT preload files or control execution order.
 *
 * This autoloader intentionally avoids:
 * - directory scanning
 * - Composer-style class maps
 * - loading non-plugin classes
 *
 * Core / infrastructure classes that must exist deterministically
 * may still be explicitly loaded during plugin bootstrap.
 */
final class Autoloader {

	/**
	 * Register the autoloader with SPL.
	 *
	 * This must be called once, early in plugin execution,
	 * before any namespaced classes are referenced.
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	/**
	 * Autoload callback.
	 *
	 * Resolves fully-qualified class names to files using
	 * a simple, WordPress-style naming convention.
	 *
	 * @param string $class Fully-qualified class name.
	 * @return void
	 */
	private static function autoload( string $class ): void {

		// Only handle classes within this plugin's namespace.
		if ( strpos( $class, __NAMESPACE__ . '\\' ) !== 0 ) {
			return;
		}

		/*
		 * Convert class name to file path:
		 *
		 * Plugiva\Pulse\Pulse_Renderer
		 * → Pulse_Renderer
		 * → pulse-renderer
		 * → includes/class-pulse-renderer.php
		 */
		$path = strtolower(
			str_replace(
				[ __NAMESPACE__ . '\\', '\\', '_' ],
				[ '', '-', '-' ],
				$class
			)
		);

		$file = PPLS_PATH . 'includes/class-' . $path . '.php';

		// Load the class file if it exists.
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

// Register the autoloader immediately.
Autoloader::register();
