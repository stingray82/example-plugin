<?php
/**
 * Plugin Name:       Example Plugin
 * Description:       A test plugin demonstrating UUPD_Updater integration.
 * Tested up to:      6.8.2
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Version:           1.62.26-beta.2
 * Author:            Nathan Foley
 * Author URI:        https://reallyusefulplugins.com
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       example-plugin
 * Website:           https://reallyusefulplugins.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define our plugin version
if ( ! defined( 'EXAMPLE_PLUGIN_VERSION' ) ) {
    define('EXAMPLE_PLUGIN_VERSION', '1.62.26-beta.2');
}

// ──────────────────────────────────────────────────────────────────────────
//  Updater bootstrap (plugins_loaded priority 1):
// ──────────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', function() {
    // 1) Load our universal drop-in. Because that file begins with "namespace UUPD\V1;",
    //    both the class and the helper live under UUPD\V1.
    require_once __DIR__ . '/inc/updater.php';

    // 2) Build a single $updater_config array:
    $updater_config = [
        'plugin_file' => plugin_basename( __FILE__ ),             // e.g. "simply-static-export-notify/simply-static-export-notify.php"
        'slug'        => 'example-plugin',           // must match your updater‐server slug
        'name'        => 'example-plugin',         // human‐readable plugin name
        'version'     => EXAMPLE_PLUGIN_VERSION, // same as the VERSION constant above
        'key'         => 'testkey123',                 // your secret key for private updater
        'server'      => 'https://raw.githubusercontent.com/stingray82/example-plugin/main/uupd/index.json',
        //'server'      => 'https://updater.reallyusefulplugins.com/u/',
        'allow_prerelease' => (bool) get_option( 'example_plugin_allow_prerelease', false ),
        
    ];

    // 3) Call the helper in the UUPD\V1 namespace:
    \UUPD\V1\UUPD_Updater_V1::register( $updater_config );
}, 20 );


// ──────────────────────────────────────────────────────────────────────────
//  Settings: "Allow prereleases" toggle + top-level admin page
// ──────────────────────────────────────────────────────────────────────────

// 1) Use an option to store the toggle (boolean).
add_action( 'admin_init', function () {
    register_setting(
        'example_plugin_settings',
        'example_plugin_allow_prerelease',
        [
            'type'              => 'boolean',
            'sanitize_callback' => static function ( $v ) { return (int) ! empty( $v ); },
            'default'           => 0,
        ]
    );

    add_settings_section(
        'example_plugin_updates',
        __( 'Update Settings', 'example-plugin' ),
        '__return_false',
        'example-plugin' // page slug
    );

    add_settings_field(
        'example_plugin_allow_prerelease_field',
        __( 'Allow prereleases', 'example-plugin' ),
        function () {
            $val = (bool) get_option( 'example_plugin_allow_prerelease', false );
            ?>
            <label>
                <input type="checkbox" name="example_plugin_allow_prerelease" value="1" <?php checked( $val, true ); ?> />
                <?php esc_html_e( 'Enable beta/RC/dev updates', 'example-plugin' ); ?>
            </label>
            <p class="description">
                <?php esc_html_e( 'When enabled, the updater will offer pre-release versions (alpha, beta, RC, dev).', 'example-plugin' ); ?>
            </p>
            <?php
        },
        'example-plugin',
        'example_plugin_updates'
    );
} );

// 2) Add a top-level admin menu page.
add_action( 'admin_menu', function () {
    add_menu_page(
        __( 'Example Plugin', 'example-plugin' ),
        __( 'Example Plugin', 'example-plugin' ),
        'manage_options',
        'example-plugin',
        'example_plugin_render_settings_page',
        'dashicons-admin-generic',
        65
    );
} );

// 3) Render the settings page (includes a manual "Check for updates" button).
function example_plugin_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $slug      = 'example-plugin';
    $nonce     = wp_create_nonce( 'uupd_manual_check_' . $slug );
    $check_url = admin_url( sprintf(
        'admin.php?action=uupd_manual_check&slug=%s&_wpnonce=%s',
        rawurlencode( $slug ),
        $nonce
    ) );

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Example Plugin', 'example-plugin' ); ?></h1>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'example_plugin_settings' );
            do_settings_sections( 'example-plugin' );
            submit_button( __( 'Save Changes', 'example-plugin' ) );
            ?>
        </form>

        <hr />
        <p>
            <?php esc_html_e( 'Need to refresh update data now?', 'example-plugin' ); ?>
        </p>
        <p>
            <a href="<?php echo esc_url( $check_url ); ?>" class="button button-secondary">
                <?php esc_html_e( 'Check for updates now', 'example-plugin' ); ?>
            </a>
        </p>
    </div>
    <?php
}

// 4) Feed the option into the updater at runtime.
add_filter(
    'uupd/allow_prerelease/example-plugin',
    function ( $allow, $slug ) {
        return (bool) get_option( 'example_plugin_allow_prerelease', false );
    },
    10,
    2
);





