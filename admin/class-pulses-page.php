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
        add_action( 'admin_notices', [ __CLASS__, 'maybe_show_notice' ] );
    }

    /**
     * Render pulses page.
     *
     * @return void
     */
	public static function render_list() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
            self::render_edit();
            exit; // REQUIRED
        }

        $pulses = Pulses::all();
        require PPLS_PATH . 'admin/views/pulses-list.php';
    }

    /**
     * Render pulse edit screen.
     *
     * @return void
     */
	public static function render_edit() {

		$pulse_id = isset( $_GET['pulse'] ) ? sanitize_key( wp_unslash( $_GET['pulse'] ) ) : '';
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

    /**
     * Handle pulse actions: save, delete, toggle.
     *
     * @return void
     */
	public static function handle_actions() {

        if ( empty( $_POST['ppls_action'] ) ) {
            return;
        }

        // PATCH: explicit nonce presence + verification
		if ( empty( $_POST['ppls_nonce'] ) ) {
			return;
		}

        check_admin_referer( 'ppls_pulse_action', 'ppls_nonce' );

        switch ( $_POST['ppls_action'] ) {

            case 'save':

                if ( isset( $_POST['pulse'] ) && is_array( $_POST['pulse'] ) ) {
                    
                    // PATCH: unslash + sanitize array

                    $pulse_data = wp_unslash( $_POST['pulse'] );

                    // Pulse ID
                    $pulse_data['id'] = isset( $pulse_data['id'] )
                        ? sanitize_key( $pulse_data['id'] )
                        : '';

                    $pulse_data['title'] = isset( $pulse_data['title'] )
                        ? sanitize_text_field( $pulse_data['title'] )
                        : '';

                    $pulse_data['visibility'] = isset( $pulse_data['visibility'] )
                        ? sanitize_text_field( $pulse_data['visibility'] )
                        : 'public';

                    $pulse_data['enabled'] = ! empty( $pulse_data['enabled'] );

                    // Questions untouched here — validated later
                    $pulse_data['questions'] = $pulse_data['questions'] ?? [];

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

                wp_safe_redirect( admin_url( 'admin.php?page=ppls-pulses&ppls_updated=1' ) );
                exit;

            case 'delete':

                if ( ! empty( $_POST['pulse_id'] ) ) {
                    Pulses::delete(
                        sanitize_key( wp_unslash( $_POST['pulse_id'] ) )
                    );
                }

                wp_safe_redirect( admin_url( 'admin.php?page=ppls-pulses&ppls_deleted=1' ) );
                exit;

            case 'toggle':

                if ( ! empty( $_POST['pulse_id'] ) ) {
                    $pulse_id = sanitize_key( wp_unslash( $_POST['pulse_id'] ) );
                    $pulse    = Pulses::get( $pulse_id );

                    if ( $pulse ) {
                        $pulse['enabled'] = ! $pulse['enabled'];
                        Pulses::save( $pulse );
                    }
                }

                wp_safe_redirect( admin_url( 'admin.php?page=ppls-pulses&ppls_updated=1' ) );
                exit;
        }
    }

    /**
     * Maybe show update or deletion notice.
     *
     * @return void
     */
    public static function maybe_show_notice(): void {
        if (
            empty( $_GET['page'] ) ||
            $_GET['page'] !== 'ppls-pulses'
        ) {
            return;
        }

        if ( ! empty( $_GET['ppls_updated'] ) ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e( 'Pulse updated.', 'plugiva-pulse' ); ?></p>
            </div>
            <?php
        }

        if ( ! empty( $_GET['ppls_deleted'] ) ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e( 'Pulse deleted.', 'plugiva-pulse' ); ?></p>
            </div>
            <?php
        }
    }

}
