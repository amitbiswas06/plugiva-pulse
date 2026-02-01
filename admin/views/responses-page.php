<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \Plugiva\Pulse\Admin\Responses_Table $table */
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Pulse Responses', 'plugiva-pulse' ); ?></h1>
	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ppls-responses&export=csv' ) ); ?>" class="button">
			<?php esc_html_e( 'Export CSV', 'plugiva-pulse' ); ?>
		</a>
	</p>
	<form method="post" id="ppls-responses-form">
		<?php
		wp_nonce_field( 'bulk-responses' );
		$table->display();
		?>
	</form>
</div>
