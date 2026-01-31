<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \Plugiva\Pulse\Admin\Responses_Table $table */
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Pulse Responses', 'plugiva-pulse' ); ?></h1>

	<form method="post">
		<?php
		$table->display();
		?>
	</form>
</div>
