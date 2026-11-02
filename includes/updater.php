<?php
/**
 * Self-updating from the private GitHub repo stijn/read-progress-button.
 *
 * How it works:
 * - plugin-update-checker (MIT, vendored) looks at the latest GitHub release of the repo and
 *   offers its "read-progress-button.zip" asset as an update.
 * - The repo is private, so it needs a read-only GitHub token: a fine-grained token for this repo
 *   only, with Contents: Read-only. Put it in wp-config.php as
 *   define( 'READ_PROGRESS_BUTTON_GITHUB_TOKEN', '...' ); or paste it on Settings > Read Progress
 *   Button. The token is never shown again after saving, logged or returned by an ability.
 * - WordPress auto-updates are switched on for this plugin only.
 * - Two abilities (plugin-status, self-update) let an MCP connector check and apply an update right
 *   away instead of waiting for WordPress's twice-daily check.
 *
 * @package ReadProgressButton
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __DIR__ ) . '/vendor/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

const READ_PROGRESS_BUTTON_REPO = 'https://github.com/stijn/read-progress-button/';

function read_progress_button_github_token() : string {
	if ( defined( 'READ_PROGRESS_BUTTON_GITHUB_TOKEN' ) && READ_PROGRESS_BUTTON_GITHUB_TOKEN ) {
		return (string) READ_PROGRESS_BUTTON_GITHUB_TOKEN;
	}
	return (string) get_option( 'read_progress_button_github_token', '' );
}

function read_progress_button_update_checker() {
	static $checker = null;
	if ( null === $checker ) {
		$checker = PucFactory::buildUpdateChecker( READ_PROGRESS_BUTTON_REPO, READ_PROGRESS_BUTTON_FILE, 'read-progress-button', 6 );
		$token   = read_progress_button_github_token();
		if ( '' !== $token ) {
			$checker->setAuthentication( $token );
		}
		$checker->getVcsApi()->enableReleaseAssets( '/read-progress-button\.zip($|[?&#])/i' );
	}
	return $checker;
}
read_progress_button_update_checker();

// Auto-update this plugin only.
add_filter( 'auto_update_plugin', function ( $update, $item ) {
	if ( isset( $item->plugin ) && plugin_basename( READ_PROGRESS_BUTTON_FILE ) === $item->plugin ) {
		return true;
	}
	return $update;
}, 10, 2 );

add_action( 'admin_init', function () {
	register_setting( 'read_progress_button_token', 'read_progress_button_github_token', array(
		'type'              => 'string',
		'show_in_rest'      => false,
		'sanitize_callback' => function ( $value ) {
			$value = trim( (string) $value );
			if ( '' === $value ) {
				return (string) get_option( 'read_progress_button_github_token', '' ); // Empty field keeps the saved token.
			}
			if ( '__clear__' === $value ) {
				return '';
			}
			return sanitize_text_field( $value );
		},
	) );
} );

// Abilities: status and immediate self-update, for an MCP connector.
add_action( 'wp_abilities_api_categories_init', function () {
	if ( ! function_exists( 'wp_register_ability_category' ) ) {
		return;
	}
	wp_register_ability_category( 'read-progress-button', array(
		'label'       => 'Read Progress Button',
		'description' => 'Report and install updates of the Read Progress Button plugin.',
	) );
} );

add_action( 'wp_abilities_api_init', function () {
	if ( ! function_exists( 'wp_register_ability' ) ) {
		return;
	}

	$can_update = function () {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return new WP_Error( 'read_progress_button_forbidden', 'This user may not update plugins.' );
		}
		return true;
	};

	$status = function ( bool $check_now ) : array {
		$checker = read_progress_button_update_checker();
		if ( $check_now ) {
			$checker->checkForUpdates();
		}
		$update = $checker->getUpdate();
		$state  = $checker->getUpdateState();
		$errors = array();
		foreach ( (array) $checker->getLastRequestApiErrors() as $e ) {
			if ( isset( $e['error'] ) && is_wp_error( $e['error'] ) ) {
				$errors[] = $e['error']->get_error_message();
			}
		}
		return array(
			'installed_version' => READ_PROGRESS_BUTTON_VERSION,
			'available_version' => $update ? (string) $update->version : null,
			'token_configured'  => '' !== read_progress_button_github_token(),
			'auto_update'       => true,
			'last_check'        => ( $state && $state->getLastCheck() ) ? gmdate( 'c', (int) $state->getLastCheck() ) : null,
			'errors'            => $errors,
		);
	};

	$status_schema = array(
		'type'       => 'object',
		'properties' => array(
			'installed_version' => array( 'type' => 'string' ),
			'available_version' => array( 'type' => array( 'string', 'null' ) ),
			'token_configured'  => array( 'type' => 'boolean' ),
			'auto_update'       => array( 'type' => 'boolean' ),
			'last_check'        => array( 'type' => array( 'string', 'null' ) ),
			'errors'            => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
			'updated'           => array( 'type' => 'boolean' ),
			'message'           => array( 'type' => 'string' ),
		),
	);

	wp_register_ability( 'read-progress-button/plugin-status', array(
		'label'               => 'Read Progress Button: update status',
		'description'         => 'Shows the installed version of the read-progress-button plugin and checks GitHub for a newer release. Never returns the token.',
		'category'            => 'read-progress-button',
		'input_schema'        => array(
			'type'                 => 'object',
			'properties'           => array( 'check_now' => array( 'type' => 'boolean', 'description' => 'Ask GitHub now instead of using the cached result.' ) ),
			'additionalProperties' => false,
		),
		'output_schema'       => $status_schema,
		'permission_callback' => $can_update,
		'execute_callback'    => function ( $input = null ) use ( $status ) {
			return $status( ! empty( $input['check_now'] ) );
		},
		'meta'                => array(
			'mcp'          => array( 'public' => true ),
			'show_in_rest' => true,
			'annotations'  => array( 'readonly' => true, 'destructive' => false, 'idempotent' => true ),
		),
	) );

	wp_register_ability( 'read-progress-button/self-update', array(
		'label'               => 'Read Progress Button: install update now',
		'description'         => 'Checks GitHub for a newer release of THIS plugin (read-progress-button) and installs it right away. Touches no other plugin, theme or setting.',
		'category'            => 'read-progress-button',
		'input_schema'        => array( 'type' => 'object', 'properties' => new stdClass(), 'additionalProperties' => false ),
		'output_schema'       => $status_schema,
		'permission_callback' => $can_update,
		'execute_callback'    => function ( $input = null ) use ( $status ) {
			$before = $status( true );
			if ( ! $before['token_configured'] ) {
				return new WP_Error( 'read_progress_button_no_token', 'No GitHub token configured (Settings > Read Progress Button).' );
			}
			if ( empty( $before['available_version'] ) || version_compare( $before['available_version'], READ_PROGRESS_BUTTON_VERSION, '<=' ) ) {
				return array_merge( $before, array( 'updated' => false, 'message' => 'Already up to date.' ) );
			}
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/misc.php';
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			wp_update_plugins(); // Refresh the update transient so the upgrader sees the new package.
			$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
			$result   = $upgrader->upgrade( plugin_basename( READ_PROGRESS_BUTTON_FILE ) );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			if ( ! $result ) {
				$messages = $upgrader->skin->get_upgrade_messages();
				return new WP_Error( 'read_progress_button_update_failed', 'Update failed: ' . implode( ' | ', array_slice( (array) $messages, -3 ) ) );
			}
			if ( ! is_plugin_active( plugin_basename( READ_PROGRESS_BUTTON_FILE ) ) ) {
				activate_plugin( plugin_basename( READ_PROGRESS_BUTTON_FILE ) );
			}
			return array_merge( $before, array( 'updated' => true, 'message' => 'Installed ' . $before['available_version'] . '. The new code runs from the next request.' ) );
		},
		'meta'                => array(
			'mcp'          => array( 'public' => true ),
			'show_in_rest' => true,
			'annotations'  => array( 'readonly' => false, 'destructive' => false, 'idempotent' => true ),
		),
	) );
} );
