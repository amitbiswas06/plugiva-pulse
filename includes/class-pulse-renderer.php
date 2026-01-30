<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Pulse_Renderer {

	/**
	 * Render a pulse.
	 *
	 * @param string $pulse_id Pulse ID.
	 * @param array  $context  Rendering context.
	 * @return string HTML output.
	 */
	public static function render( string $pulse_id, array $context = [] ): string {

		if ( $pulse_id === '' ) {
			return '';
		}

		$pulse = Pulses::get( $pulse_id );

		if ( ! is_array( $pulse ) ) {
			return '';
		}

		// Visibility rules
		if ( ! self::can_render( $pulse, $context ) ) {
			return '';
		}

		ob_start();

		?>
		<div class="ppls-pulse" data-pulse-id="<?php echo esc_attr( $pulse['id'] ); ?>">

			<form
				class="ppls-pulse-form"
				method="post"
				action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
				data-pulse="<?php echo esc_attr( $pulse['id'] ); ?>"
			><?php
				// Security nonce.
				wp_nonce_field( 'ppls_submit', 'nonce' );
			?>

				<input type="hidden" name="action" value="ppls_submit_pulse">
				<input type="hidden" name="pulse_id" value="<?php echo esc_attr( $pulse['id'] ); ?>">

				<?php
				$started_at = time();

				$session_hash = hash_hmac(
					'sha256',
					$pulse['id'] . '|' . ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) . '|' . gmdate( 'Y-m-d-H' ),
					wp_salt()
				);
				?>

				<input type="hidden" name="meta[started_at]" value="<?php echo esc_attr( $started_at ); ?>">
				<input type="hidden" name="meta[hash]" value="<?php echo esc_attr( $session_hash ); ?>">

				<!-- Honeypot field (bots only) -->
				<input
					type="text"
					name="meta[ppls_hp]"
					value=""
					tabindex="-1"
					autocomplete="off"
					style="position:absolute;left:-9999px;height:0;width:0;opacity:0;"
				>

				<?php if ( ! empty( $pulse['title'] ) ) : ?>
					<h3 class="ppls-pulse-title">
						<?php echo esc_html( $pulse['title'] ); ?>
					</h3>
				<?php endif; ?>

				<?php self::render_questions( $pulse['questions'] ?? [] ); ?>

				<button type="submit" class="ppls-submit"><?php esc_html_e( 'Submit', 'plugiva-pulse' ); ?></button>

			</form>

		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Determine if a pulse can be rendered in the given context.
	 *
	 * @param array $pulse   Pulse data.
	 * @param array $context Render context.
	 * @return bool
	 */
	private static function can_render( array $pulse, array $context ): bool {

		if ( empty( $pulse['enabled'] ) ) {
			return false;
		}

		$is_admin = ! empty( $context['admin'] );

		if ( $pulse['visibility'] === 'admin' && ! $is_admin ) {
			return false;
		}

		return true;
	}

	/**
	 * Render questions list.
	 *
	 * @param array $questions Questions.
	 * @return void
	 */
	private static function render_questions( array $questions ): void {

		foreach ( $questions as $index => $question ) {

			if ( empty( $question['label'] ) || empty( $question['type'] ) ) {
				continue;
			}

			echo '<div class="ppls-question">';

			echo '<label class="ppls-question-label">';
			echo esc_html( $question['label'] );
			echo '</label>';

			self::render_question_input( $question, $index );

			echo '</div>';
		}
	}

	/**
	 * Render the input field for a single question.
	 *
	 * @param array $question Question configuration.
	 * @param int   $index    Question index.
	 * @return void
	 */
	private static function render_question_input( array $question, int $index ): void {

		$name = 'answers[q' . $index . ']';

		switch ( $question['type'] ) {

			case 'emoji':
				?>
				<div class="ppls-input ppls-input-emoji">
					<label>
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="happy">
						<span aria-hidden="true">🙂</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Happy', 'plugiva-pulse' ); ?></span>
					</label>

					<label>
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="neutral">
						<span aria-hidden="true">😐</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Neutral', 'plugiva-pulse' ); ?></span>
					</label>

					<label>
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="sad">
						<span aria-hidden="true">🙁</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Sad', 'plugiva-pulse' ); ?></span>
					</label>
				</div>
				<?php
				break;

			case 'yesno':
				?>
				<div class="ppls-input ppls-input-yes-no">
					<label>
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="yes">
						<?php esc_html_e( 'Yes', 'plugiva-pulse' ); ?>
					</label>

					<label>
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="no">
						<?php esc_html_e( 'No', 'plugiva-pulse' ); ?>
					</label>
				</div>
				<?php
				break;

			case 'text':
				?>
				<div class="ppls-input ppls-input-text">
					<input
						type="text"
						name="<?php echo esc_attr( $name ); ?>"
						class="ppls-text-input"
						autocomplete="off"
					>
				</div>
				<?php
				break;
		}
	}

}
