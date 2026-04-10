<?php
/**
 * Plugin Name: Plugiva Pulse
 * Description: Create lightweight feedback forms and quick polls with yes/no, emoji, and text responses inside WordPress.
 * Version:     1.2.0
 * Author:      Plugiva
 * Author URI:  https://plugiva.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: plugiva-pulse
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PPLS_VERSION', '1.2.0' );
define( 'PPLS_DB_VERSION', '1.2.0' );
define( 'PPLS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PPLS_URL', plugin_dir_url( __FILE__ ) );
define( 'PPLS_BASENAME', plugin_basename( __FILE__ ) );

require_once PPLS_PATH . 'includes/class-autoloader.php';

add_action( 'plugins_loaded', function () {
	\Plugiva\Pulse\Plugin::instance();
} );

// Database schema update on admin init.
// @since 1.2.0
add_action( 'admin_init', function () {

	$installed = get_option( 'ppls_db_version' );

	if ( $installed === PPLS_DB_VERSION ) {
		return;
	}

	// Run schema update (safe via dbDelta)
	\Plugiva\Pulse\Schema::install();

	update_option( 'ppls_db_version', PPLS_DB_VERSION );
});

// Initial installation hook to set up database schema.
register_activation_hook(
	__FILE__,
	function () {
		\Plugiva\Pulse\Schema::install();
	}
);
