<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1>
		<?php esc_html_e( 'Pulses', 'plugiva-pulse' ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ppls-pulses&action=edit' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Add New', 'plugiva-pulse' ); ?>
		</a>
	</h1>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Title', 'plugiva-pulse' ); ?></th>
				<th><?php esc_html_e( 'Visibility', 'plugiva-pulse' ); ?></th>
				<th><?php esc_html_e( 'Status', 'plugiva-pulse' ); ?></th>
				<th><?php esc_html_e( 'Questions', 'plugiva-pulse' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'plugiva-pulse' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $pulses ) ) : ?>
				<tr><td colspan="5"><?php esc_html_e( 'No pulses yet.', 'plugiva-pulse' ); ?></td></tr>
			<?php endif; ?>

			<?php foreach ( $pulses as $pulse ) : ?>
				<tr>
                    <td><?php echo esc_html( $pulse['title'] ); ?></td>
                    <td><?php echo esc_html( ucfirst( $pulse['visibility'] ) ); ?></td>
                    <td><?php echo $pulse['enabled'] ? esc_html__( 'Enabled', 'plugiva-pulse' ) : esc_html__( 'Disabled', 'plugiva-pulse' ); ?></td>
                    <td><?php
                    echo isset( $pulse['questions'] ) && is_array( $pulse['questions'] )
                        ? count( $pulse['questions'] )
                        : 0;
                    ?></td>

                    <td>
                        <ul class="row-actions">
                            <li>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ppls-pulses&action=edit&pulse=' . $pulse['id'] ) ); ?>">
                                    <?php esc_html_e( 'Edit', 'plugiva-pulse' ); ?>
                                </a>
                            </li>

                            <li>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field( 'ppls_pulse_action' ); ?>
                                    <input type="hidden" name="ppls_action" value="toggle">
                                    <input type="hidden" name="pulse_id" value="<?php echo esc_attr( $pulse['id'] ); ?>">
                                    <button type="submit" class="link-button">
                                        <?php echo $pulse['enabled']
                                            ? esc_html__( 'Disable', 'plugiva-pulse' )
                                            : esc_html__( 'Enable', 'plugiva-pulse' ); ?>
                                    </button>
                                </form>
                            </li>

                            <li class="delete">
                                <form method="post" style="display:inline;" onsubmit="return confirm('<?php esc_attr_e( 'Delete this pulse?', 'plugiva-pulse' ); ?>');">
                                    <?php wp_nonce_field( 'ppls_pulse_action' ); ?>
                                    <input type="hidden" name="ppls_action" value="delete">
                                    <input type="hidden" name="pulse_id" value="<?php echo esc_attr( $pulse['id'] ); ?>">
                                    <button type="submit" class="link-button">
                                        <?php esc_html_e( 'Delete', 'plugiva-pulse' ); ?>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </td>
                </tr>

			<?php endforeach; ?>
		</tbody>
	</table>
</div>
