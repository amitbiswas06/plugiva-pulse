<?php
namespace Plugiva\Pulse\Admin;

use Plugiva\Pulse\Pulses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles pulse list and edit screens.
 */
final class Pulses_Page {

	public static function render_list() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
            self::render_edit();
            return;
        }
        
		self::handle_actions();
		$pulses = Pulses::all();
		require PPLS_PATH . 'admin/views/pulses-list.php';
	}

	public static function render_edit() {
		self::handle_actions();

		$pulse_id = isset( $_GET['pulse'] ) ? sanitize_text_field( wp_unslash( $_GET['pulse'] ) ) : '';
		$pulse    = $pulse_id ? Pulses::get( $pulse_id ) : null;

		require PPLS_PATH . 'admin/views/pulse-edit.php';
	}

	public static function render_responses_stub() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Responses', 'plugiva-pulse' ) . '</h1>';
		echo '<p>' . esc_html__( 'Responses screen will be available in the next step.', 'plugiva-pulse' ) . '</p></div>';
	}

	private static function handle_actions() {
		if ( empty( $_POST['ppls_action'] ) ) {
			return;
		}

		check_admin_referer( 'ppls_pulse_action' );

		switch ( $_POST['ppls_action'] ) {

            case 'save':
                if ( isset( $_POST['pulse'] ) && is_array( $_POST['pulse'] ) ) {
                    Pulses::save( wp_unslash( $_POST['pulse'] ) );
                }
                wp_safe_redirect( admin_url( 'admin.php?page=ppls-pulses&updated=1' ) );
                exit;

            case 'delete':
                if ( ! empty( $_POST['pulse_id'] ) ) {
                    Pulses::delete( sanitize_text_field( wp_unslash( $_POST['pulse_id'] ) ) );
                }
                wp_safe_redirect( admin_url( 'admin.php?page=ppls-pulses&updated=1' ) );
                exit;

            case 'toggle':
                if ( ! empty( $_POST['pulse_id'] ) ) {
                    $pulse = Pulses::get( sanitize_text_field( wp_unslash( $_POST['pulse_id'] ) ) );
                    if ( $pulse ) {
                        $pulse['enabled'] = ! $pulse['enabled'];
                        Pulses::save( $pulse );
                    }
                }
                wp_safe_redirect( admin_url( 'admin.php?page=ppls-pulses&updated=1' ) );
                exit;
        }

	}
}
