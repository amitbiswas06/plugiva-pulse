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

			<?php if ( ! empty( $pulse['title'] ) ) : ?>
				<h3 class="ppls-pulse-title">
					<?php echo esc_html( $pulse['title'] ); ?>
				</h3>
			<?php endif; ?>

			<?php self::render_questions( $pulse['questions'] ?? [] ); ?>

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

		if ( empty( $questions ) ) {
			return;
		}

		?>
		<ul class="ppls-questions">
			<?php foreach ( $questions as $question ) : ?>
				<li class="ppls-question" data-qid="<?php echo esc_attr( $question['id'] ); ?>">

					<span class="ppls-question-label">
						<?php echo esc_html( $question['label'] ); ?>
					</span>

					<div class="ppls-question-input">
						<?php self::render_question_input( $question ); ?>
					</div>

				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Render question input (display only).
	 *
	 * @param array $question Question data.
	 * @return void
	 */
	private static function render_question_input( array $question ): void {

		switch ( $question['type'] ) {

			case 'emoji':
                ?>
                <ul class="ppls-emoji-scale" aria-hidden="true">
                    <li class="ppls-emoji-option ppls-emoji-positive">smile1</li>
                    <li class="ppls-emoji-option ppls-emoji-neutral">smile2</li>
                    <li class="ppls-emoji-option ppls-emoji-negative">smile3</li>
                </ul>
                <?php
                break;

			case 'yesno':
				?>
				<label>
					<input type="radio" disabled>
					<?php esc_html_e( 'Yes', 'plugiva-pulse' ); ?>
				</label>
				<label>
					<input type="radio" disabled>
					<?php esc_html_e( 'No', 'plugiva-pulse' ); ?>
				</label>
				<?php
				break;

			case 'text':
				?>
				<textarea disabled rows="2"></textarea>
				<?php
				break;
		}
	}
}
