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

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'handle_actions' ] );
    }

	public static function render_list() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
            self::render_edit();
            exit; // REQUIRED
        }

        $pulses = Pulses::all();
        require PPLS_PATH . 'admin/views/pulses-list.php';
    }

	public static function render_edit() {

		$pulse_id = isset( $_GET['pulse'] ) ? sanitize_text_field( wp_unslash( $_GET['pulse'] ) ) : '';
		$pulse    = $pulse_id ? Pulses::get( $pulse_id ) : null;

        // view always receives an array
        if ( ! is_array( $pulse ) ) {
            $pulse = [
                'id'        => '',
                'title'     => '',
                'visibility'=> 'public',
                'enabled'   => true,
                'questions' => [],
            ];
        }

		require PPLS_PATH . 'admin/views/pulse-edit.php';
	}

	public static function handle_actions() {

        if ( empty( $_POST['ppls_action'] ) ) {
            return;
        }

        check_admin_referer( 'ppls_pulse_action' );

        switch ( $_POST['ppls_action'] ) {

            case 'save':

                if ( isset( $_POST['pulse'] ) && is_array( $_POST['pulse'] ) ) {
                    $pulse_data = wp_unslash( $_POST['pulse'] );

                    /** 
                     * @var string|\WP_Error $result 
                     */
                    $result = Pulses::save( $pulse_data );

                    if ( is_wp_error( $result ) ) {
                        $pulse_id = isset( $pulse_data['id'] ) ? $pulse_data['id'] : '';

                        wp_safe_redirect(
                            add_query_arg(
                                [
                                    'ppls_error' => $result->get_error_code(),
                                    'action'     => 'edit',
                                    'pulse'      => $pulse_id,
                                ],
                                admin_url( 'admin.php?page=ppls-pulses' )
                            )
                        );
                        exit;
                    }
                }

                wp_safe_redirect( admin_url( 'admin.php?page=ppls-pulses&updated=1' ) );
                exit;

            case 'delete':

                if ( ! empty( $_POST['pulse_id'] ) ) {
                    Pulses::delete(
                        sanitize_text_field( wp_unslash( $_POST['pulse_id'] ) )
                    );
                }

                wp_safe_redirect( admin_url( 'admin.php?page=ppls-pulses&updated=1' ) );
                exit;

            case 'toggle':

                if ( ! empty( $_POST['pulse_id'] ) ) {
                    $pulse_id = sanitize_text_field( wp_unslash( $_POST['pulse_id'] ) );
                    $pulse    = Pulses::get( $pulse_id );

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
