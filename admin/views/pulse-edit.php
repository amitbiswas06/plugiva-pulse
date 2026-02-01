<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_GET['ppls_error'] ) ) : ?>
<div class="notice notice-error">
    <p>
        <?php
        if ( $_GET['ppls_error'] === 'ppls_missing_title' ) {
            esc_html_e( 'Pulse title is required.', 'plugiva-pulse' );
        } elseif ( $_GET['ppls_error'] === 'ppls_invalid_question' ) {
            esc_html_e( 'Each question must have both a label and a type.', 'plugiva-pulse' );
        }
        ?>
    </p>
</div>
<?php endif; ?>

<div class="wrap">
	<h1><?php echo $pulse ? esc_html__( 'Edit Pulse', 'plugiva-pulse' ) : esc_html__( 'Add Pulse', 'plugiva-pulse' ); ?></h1>

	<form method="post">
		<?php wp_nonce_field( 'ppls_pulse_action' ); ?>
        <?php if ( ! empty( $pulse['id'] ) ) : ?>
            <input type="hidden"
                name="pulse[id]"
                value="<?php echo esc_attr( $pulse['id'] ); ?>">
        <?php endif; ?>

		<input type="hidden" name="ppls_action" value="save">

		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Title', 'plugiva-pulse' ); ?></th>
				<td><input type="text" name="pulse[title]" value="<?php echo esc_attr( $pulse['title'] ?? '' ); ?>" class="regular-text"></td>
			</tr>

			<tr>
				<th><?php esc_html_e( 'Visibility', 'plugiva-pulse' ); ?></th>
				<td>
					<select name="pulse[visibility]">
						<option value="public" <?php selected( $pulse['visibility'] ?? '', 'public' ); ?>><?php esc_html_e( 'Public', 'plugiva-pulse' ); ?></option>
						<option value="admin" <?php selected( $pulse['visibility'] ?? '', 'admin' ); ?>><?php esc_html_e( 'Admin only', 'plugiva-pulse' ); ?></option>
					</select>
				</td>
			</tr>

			<tr>
				<th><?php esc_html_e( 'Enabled', 'plugiva-pulse' ); ?></th>
				<td><input type="checkbox" name="pulse[enabled]" value="1" <?php checked( $pulse['enabled'] ?? false ); ?>></td>
			</tr>
		</table>

        <h2><?php esc_html_e( 'Questions', 'plugiva-pulse' ); ?></h2>

        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Label', 'plugiva-pulse' ); ?></th>
                    <th><?php esc_html_e( 'Type', 'plugiva-pulse' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $questions = $pulse['questions'] ?? [];
                for ( $i = 0; $i < 3; $i++ ) :
                    $q = $questions[ $i ] ?? [];
                ?>
                    <tr>
                        <td>
                            <input type="hidden"
                                name="pulse[questions][<?php echo esc_attr( (int) $i ); ?>][id]"
                                value="<?php echo esc_attr( $q['id'] ?? '' ); ?>">

                            <input type="text"
                                name="pulse[questions][<?php echo esc_attr( (int) $i ); ?>][label]"
                                value="<?php echo esc_attr( $q['label'] ?? '' ); ?>"
                                class="regular-text">
                        </td>
                        <td>
                            <select name="pulse[questions][<?php echo esc_attr( (int) $i ); ?>][type]">
                                <option value=""><?php esc_html_e( '— Select —', 'plugiva-pulse' ); ?></option>
                                <option value="emoji" <?php selected( $q['type'] ?? '', 'emoji' ); ?>>
                                    <?php esc_html_e( 'Emoji', 'plugiva-pulse' ); ?>
                                </option>
                                <option value="yesno" <?php selected( $q['type'] ?? '', 'yesno' ); ?>>
                                    <?php esc_html_e( 'Yes / No', 'plugiva-pulse' ); ?>
                                </option>
                                <option value="text" <?php selected( $q['type'] ?? '', 'text' ); ?>>
                                    <?php esc_html_e( 'Short text', 'plugiva-pulse' ); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

		<?php submit_button(); ?>
	</form>
</div>
