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
				wp_nonce_field( 'ppls_submit', 'ppls_nonce' );
			?>

				<input type="hidden" name="action" value="ppls_submit_pulse">
				<input type="hidden" name="pulse_id" value="<?php echo esc_attr( $pulse['id'] ); ?>">

				<?php
				$started_at = time();
				
				// PATCH: sanitize user agent
				$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
					? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
					: '';

				$session_hash = hash_hmac(
					'sha256',
					$pulse['id'] . '|' . $user_agent . '|' . gmdate( 'Y-m-d-H' ),
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

				<?php 
				// Allow disabling the title via filter.
				$show_title = apply_filters(
					'ppls_show_pulse_title',
					true,
					$pulse
				);

				if ( $show_title ) : ?>
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

		if ( $pulse['visibility'] === 'admin' && ! current_user_can( 'manage_options' ) ) {
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

			echo '<p class="ppls-question-text">';
			echo esc_html( $question['label'] );
			echo '</p>';

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
						<span class="ppls-emoji" data-emoji="happy" aria-hidden="true"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Happy', 'plugiva-pulse' ); ?></span>
					</label>

					<label>
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="neutral">
						<span class="ppls-emoji" data-emoji="neutral" aria-hidden="true"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Neutral', 'plugiva-pulse' ); ?></span>
					</label>

					<label>
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="sad">
						<span class="ppls-emoji" data-emoji="sad" aria-hidden="true"></span>
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
					<textarea
						name="<?php echo esc_attr( $name ); ?>"
						maxlength="500"
						aria-describedby="<?php echo esc_attr( $name ); ?>-help"
					></textarea>
					<p id="<?php echo esc_attr( $name ); ?>-help" class="ppls-help-text">
						<?php esc_html_e( 'Up to 500 characters.', 'plugiva-pulse' ); ?>
					</p>
				</div>
				<?php
				break;
		}
	}


	/**
	 * Generate a stable question ID (qid).
	 *
	 * @param string $question Question text.
	 * @param int    $post_id  Post ID.
	 * @return string
	 * @since 1.2.0
	 */
	private static function generate_qid( string $question, int $post_id ): string {

		$q = trim( $question );
		$q = wp_strip_all_tags( $q );
		$q = strtolower( $q );
		$q = preg_replace( '/\s+/', ' ', $q );

		return hash_hmac(
			'sha256',
			$q . '|' . $post_id,
			wp_salt()
		);
	}


	/**
	 * Filter the inline question options (button values and labels/icons).
	 *
	 * Allows developers to modify or replace the available options
	 * for inline questions such as yes/no or emoji sets.
	 *
	 * Each option key is submitted as the response value, and the label
	 * may be plain text, emoji, or an icon string.
	 *
	 * Example usage:
	 *
	 * add_filter( 'ppls_inline_options', function( $options ) {
	 *
	 *     // Replace yes/no with text labels
	 *     $options['yesno'] = [
	 *         'yes' => 'Yes',
	 *         'no'  => 'No',
	 *     ];
	 *
	 *     // Customize emoji set
	 *     $options['emoji'] = [
	 *         'happy'   => '😄',
	 *         'neutral' => '😐',
	 *         'sad'     => '😢',
	 *     ];
	 *
	 *     // Add a custom type (can be used via shortcode type="rating")
	 *     $options['rating'] = [
	 *         '1' => '⭐',
	 *         '2' => '⭐⭐',
	 *         '3' => '⭐⭐⭐',
	 *         '4' => '⭐⭐⭐⭐',
	 *         '5' => '⭐⭐⭐⭐⭐',
	 *     ];
	 *
	 *     return $options;
	 * });
	 *
	 * @since 1.2.0
	 *
	 * @param array $options {
	 *     Array of option groups keyed by type.
	 *
	 *     @type array $yesno {
	 *         Yes/No options.
	 *
	 *         @type string $yes Label or icon for "yes".
	 *         @type string $no  Label or icon for "no".
	 *     }
	 *
	 *     @type array $emoji {
	 *         Emoji reaction options.
	 *
	 *         @type string $happy   Label or icon for "happy".
	 *         @type string $neutral Label or icon for "neutral".
	 *         @type string $sad     Label or icon for "sad".
	 *     }
	 * }
	 *
	 * @return array Modified options array.
	 */
	public static function render_question_shortcode( $atts ): string {

		$atts = shortcode_atts(
			[
				'q'    => '',
				'type' => 'yesno',
				'id'   => '',
			],
			$atts,
			'ppls_question'
		);

		$question = trim( (string) $atts['q'] );
		if ( $question === '' ) {
			return '';
		}

		$type = sanitize_key( $atts['type'] );

		// --- Options (filterable) ---
		$options = Inline_Utils::get_options();

		// Validate type against options
		if ( ! isset( $options[ $type ] ) ) {
			$type = 'yesno';
		}

		$post_id = get_the_ID() ?: 0;

		$qid = ! empty( $atts['id'] )
			? sanitize_key( $atts['id'] )
			: self::generate_qid( $question, $post_id );

		// --- Security ---
		$started_at = time() - 5;

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
			: '';

		$hash = hash_hmac(
			'sha256',
			$qid . '|' . $user_agent . '|' . gmdate( 'Y-m-d-H' ),
			wp_salt()
		);

		// --- Options (filterable) ---
		$options = Inline_Utils::get_options();

		// Feedback (filterable)
		$feedback = apply_filters(
			'ppls_inline_feedback',
			[
				'icon' => '✓',
				'text' => __( 'Thanks', 'plugiva-pulse' ),
			]
		);

		$current = isset( $options[ $type ] ) ? $options[ $type ] : $options['yesno'];

		ob_start();
		?>

		<div 
			class="ppls-inline-question"
			data-qid="<?php echo esc_attr( $qid ); ?>"
			data-post="<?php echo esc_attr( $post_id ); ?>"
			data-hash="<?php echo esc_attr( $hash ); ?>"
			data-started="<?php echo esc_attr( $started_at ); ?>"
			data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-qtype="<?php echo esc_attr( $type ); ?>"
		>

			<?php wp_nonce_field( 'ppls_submit', 'ppls_nonce' ); ?>

			<p class="ppls-q-text">
				<?php echo esc_html( $question ); ?>
			</p>

			<div class="ppls-options">
				<?php foreach ( $current as $value => $label ) : ?>
					<button 
						type="button" 
						class="ppls-option-btn"
						data-value="<?php echo esc_attr( $value ); ?>"
					>
						<span class="ppls-option-label">
							<?php echo esc_html( Inline_Utils::get_label( $label ) ); ?>
						</span>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="ppls-feedback" hidden>
				<span class="ppls-feedback-icon">
					<?php echo esc_html( $feedback['icon'] ); ?>
				</span>
				<span class="ppls-feedback-text">
					<?php echo esc_html( $feedback['text'] ); ?>
				</span>
			</div>

		</div>

		<?php
		return (string) ob_get_clean();
	}

}
