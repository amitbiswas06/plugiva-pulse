<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \Plugiva\Pulse\Admin\Responses_Table $table */
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Pulse Responses', 'plugiva-pulse' ); ?></h1>

	<form method="post" onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to delete selected responses?', 'plugiva-pulse' ); ?>');">
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ppls-responses&export=csv' ) ); ?>" class="button">
				<?php esc_html_e( 'Export CSV', 'plugiva-pulse' ); ?>
			</a>
		</p>
		<?php
		wp_nonce_field( 'bulk-responses' );
		$table->display();
		?>
	</form>
</div>
