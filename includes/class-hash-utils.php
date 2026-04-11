<?php

namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hash_Utils {

    /**
     * Generate valid hashes for a given identifier.
     *
     * @param string $id Unique identifier (pulse_id or qid)
     * @param string $user_agent
     * @param int    $window
     * @return array<string>
     */
    public static function get_valid_hashes( string $id, string $user_agent, int $window ): array {

        $current_bucket  = (int) floor( time() / $window );
        $previous_bucket = $current_bucket - 1;

        return [
            hash_hmac( 'sha256', $id . '|' . $user_agent . '|' . $current_bucket, wp_salt() ),
            hash_hmac( 'sha256', $id . '|' . $user_agent . '|' . $previous_bucket, wp_salt() ),
        ];
    }


    /**
     * Get hashes using current request context.
     *
     * @param string $id
     * @param string $type 'inline' or 'pulse'
     * @return array<string>
     */
    public static function get_request_hashes( string $id, string $type = 'inline' ): array {

        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
            ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
            : '';

        // Different windows per type (VERY IMPORTANT)
        if ( $type === 'inline' ) {
            $window = apply_filters( 'ppls_inline_hash_window', HOUR_IN_SECONDS );
        } else {
            $window = apply_filters( 'ppls_pulse_hash_window', HOUR_IN_SECONDS );
        }

        return self::get_valid_hashes( $id, $user_agent, $window );
    }


}