<?php
/**
 * Settings > Read Progress Button: the button's corner, the GitHub token and the update status.
 *
 * A new corner changes every post's HTML, so saving one empties WP Super Cache when it's there
 * (aardling.com's theme then clears Cloudflare too).
 *
 * @package ReadProgressButton
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', function () {
	register_setting( 'read_progress_button', 'read_progress_button_position', array(
		'type'              => 'string',
		'default'           => 'top-left',
		'show_in_rest'      => false,
		'sanitize_callback' => function ( $value ) {
			$value = (string) $value;
			return array_key_exists( $value, read_progress_button_positions() ) ? $value : read_progress_button_position();
		},
	) );
} );

add_action( 'update_option_read_progress_button_position', function ( $old, $new ) {
	if ( $old !== $new && function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}
}, 10, 2 );
add_action( 'add_option_read_progress_button_position', function ( $option, $value ) {
	if ( 'top-left' !== $value && function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}
}, 10, 2 );

add_filter( 'plugin_action_links_' . plugin_basename( READ_PROGRESS_BUTTON_FILE ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=read-progress-button' ) ) . '">Settings</a>' );
	return $links;
} );

add_action( 'admin_menu', function () {
	add_options_page( 'Read Progress Button', 'Read Progress Button', 'manage_options', 'read-progress-button', function () {
		$from_config = defined( 'READ_PROGRESS_BUTTON_GITHUB_TOKEN' ) && READ_PROGRESS_BUTTON_GITHUB_TOKEN;
		$has_token   = '' !== read_progress_button_github_token();
		$state       = read_progress_button_update_checker()->getUpdateState();
		$update      = read_progress_button_update_checker()->getUpdate();
		?>
		<div class="wrap">
			<h1>Read Progress Button</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'read_progress_button' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="read_progress_button_position">Position</label></th>
						<td>
							<select id="read_progress_button_position" name="read_progress_button_position">
								<?php foreach ( read_progress_button_positions() as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>"<?php selected( read_progress_button_position(), $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description">The corner of the screen where the button sits on posts, 50 px from both edges. Saving empties WP Super Cache, so every post shows the change.</p>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Save position' ); ?>
			</form>

			<h2>Updates</h2>
			<p>Installed version: <strong><?php echo esc_html( READ_PROGRESS_BUTTON_VERSION ); ?></strong>.
			<?php if ( $update ) : ?>
				Available: <strong><?php echo esc_html( $update->version ); ?></strong> (installs automatically).
			<?php else : ?>
				No newer release found<?php echo $state && $state->getLastCheck() ? ' (last check ' . esc_html( human_time_diff( $state->getLastCheck() ) ) . ' ago)' : ''; ?>.
			<?php endif; ?></p>
			<p>Updates come from the private GitHub repo <code>stijn/read-progress-button</code> (latest release, asset <code>read-progress-button.zip</code>). Auto-updates are on for this plugin.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'read_progress_button_token' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="read_progress_button_github_token">GitHub token</label></th>
						<td>
							<?php if ( $from_config ) : ?>
								<p>Set in wp-config.php (READ_PROGRESS_BUTTON_GITHUB_TOKEN).</p>
							<?php else : ?>
								<input type="password" id="read_progress_button_github_token" name="read_progress_button_github_token" value="" class="regular-text" autocomplete="off" placeholder="<?php echo $has_token ? esc_attr( 'Saved. Paste a new one to replace it.' ) : ''; ?>">
								<p class="description">Fine-grained personal access token, repository <code>stijn/read-progress-button</code> only, permission Contents: Read-only. <?php echo $has_token ? 'A token is saved. Leave empty to keep it, or type __clear__ to remove it.' : 'No token saved yet: updates cannot be fetched.'; ?></p>
							<?php endif; ?>
						</td>
					</tr>
				</table>
				<?php
				if ( ! $from_config ) {
					submit_button( 'Save token' );
				}
				?>
			</form>
			<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'plugins.php?puc_check_for_updates=1&puc_slug=read-progress-button' ), 'puc_check_for_updates' ) ); ?>">Check for updates now</a></p>
		</div>
		<?php
	} );
} );
