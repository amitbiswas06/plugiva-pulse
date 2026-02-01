<?php
/**
 * Uninstall Plugiva Pulse
 *
 * This file runs only when the plugin is uninstalled via WordPress.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only remove data if user explicitly opted in.
$cleanup = get_option( 'ppls_cleanup_on_uninstall', false );

if ( ! $cleanup ) {
	return;
}

global $wpdb;

// Delete responses table.
$table = $wpdb->prefix . 'ppls_responses';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

// Delete plugin options.
delete_option( 'ppls_pulses' );
delete_option( 'ppls_cleanup_on_uninstall' );
