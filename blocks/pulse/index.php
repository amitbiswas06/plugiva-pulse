<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Pulse block.
 *
 * @return void
 */
function register_pulse_block(): void {

	register_block_type(
		__DIR__,
		[
			'render_callback' => __NAMESPACE__ . '\\render_pulse_block',
		]
	);
}
add_action( 'init', __NAMESPACE__ . '\\register_pulse_block' );

add_action( 'enqueue_block_editor_assets', function () {

	wp_enqueue_script(
		'ppls-pulse-block-editor',
		plugins_url( 'editor.js', __FILE__ ),
		[
			'wp-blocks',
			'wp-element',
			'wp-components',
			'wp-i18n',
			'wp-api-fetch',
			'wp-block-editor',
			'wp-compose',
		],
		microtime(),
		true
	);
} );


/**
 * Render callback for the Pulse block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function render_pulse_block( array $attributes ): string {

	$pulse_id = $attributes['pulseId'] ?? '';

	if ( ! is_string( $pulse_id ) || $pulse_id === '' ) {
		return '';
	}

	return Pulse_Renderer::render(
		$pulse_id,
		[
			'admin'		=> is_admin(),
			'source'	=> 'block',
		]
	);
}
