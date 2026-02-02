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
	<form method="post" id="ppls-responses-form">
		<?php
		wp_nonce_field( 'bulk-responses', 'ppls_nonce' );
		$table->display();
		?>
	</form>
</div>
