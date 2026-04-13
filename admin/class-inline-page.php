<?php
namespace Plugiva\Pulse\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline Feedback admin page.
 *
 * Displays usage guidance for inline shortcode and developer hooks.
 */
final class Inline_Page {

	/**
	 * Render the inline feedback page.
	 *
	 * @return void
	 */
	public static function render(): void {
		?>

		<div class="wrap">
			<h1><?php echo esc_html__( 'Inline Feedback', 'plugiva-pulse' ); ?></h1>

			<p>
				<?php echo esc_html__( 'Use inline feedback to collect quick reactions directly inside your content.', 'plugiva-pulse' ); ?>
			</p>

			<h2><?php echo esc_html__( 'Basic Usage', 'plugiva-pulse' ); ?></h2>

			<pre>[ppls_question q="Was this helpful?" type="yesno"]</pre>

			<p><?php echo esc_html__( 'You can also use emoji-based feedback:', 'plugiva-pulse' ); ?></p>

			<pre>[ppls_question q="How do you feel about this?" type="emoji"]</pre>

			<h2><?php echo esc_html__( 'Available Types', 'plugiva-pulse' ); ?></h2>

			<ul>
				<li><strong>yesno</strong> - &#128077; / &#128078;</li>
				<li><strong>emoji</strong> - &#128513; / &#128528; / &#128577;</li>
			</ul>

			<h2><?php echo esc_html__( 'Tips', 'plugiva-pulse' ); ?></h2>

			<ul>
				<li><?php echo esc_html__( 'Place inline feedback after key sections or at the end of posts.', 'plugiva-pulse' ); ?></li>
				<li><?php echo esc_html__( 'Use it on checkout or thank you pages for instant user feedback.', 'plugiva-pulse' ); ?></li>
				<li><?php echo esc_html__( 'Combine with custom types for advanced use cases.', 'plugiva-pulse' ); ?></li>
			</ul>

			<h2><?php echo esc_html__( 'Developer Notes', 'plugiva-pulse' ); ?></h2>

			<p>
				<?php echo esc_html__( 'Extend inline feedback using filters such as ppls_inline_options, ppls_inline_feedback, and ppls_inline_hash_window.', 'plugiva-pulse' ); ?>
			</p>
            <p>
				<?php echo esc_html__( 'Please visit plugin details page for more information.', 'plugiva-pulse' ); ?>
			</p>

		</div>

		<?php
	}
}