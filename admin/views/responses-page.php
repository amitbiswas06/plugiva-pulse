<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \Plugiva\Pulse\Admin\Responses_Table $table */
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Pulse Responses', 'plugiva-pulse' ); ?></h1>
	<p>
		<?php
		$export_url = wp_nonce_url(
			admin_url( 'admin.php?page=ppls-responses&export=csv' ),
			'ppls_export_csv', 'ppls_nonce'
		);
		?>
		<a href="<?php echo esc_url( $export_url ); ?>" class="button">
			<?php esc_html_e( 'Export CSV', 'plugiva-pulse' ); ?>
		</a>
	</p>
	<?php if ( ! defined( 'PPLS_PRO_ACTIVE' ) ) : ?>
		<div class="description" style="margin-top:6px;">
			<?php
			esc_html_e(
				'Plugiva Pulse Pro adds advanced exports like date filtering, pulse-based CSVs, and scheduled delivery.',
				'plugiva-pulse'
			);
			?>
		</div>
	<?php endif; ?>
	<form method="post" id="ppls-responses-form">
		<input type="hidden" name="page" value="ppls-responses" />
		<?php
		wp_nonce_field( 'bulk-responses', 'ppls_nonce' );
		$table->display();
		?>
	</form>
</div>
