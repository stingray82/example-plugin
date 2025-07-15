<?php
/**
 * Plugin Name:       Example Plugin
 * Description:       A test plugin demonstrating UUPD_Updater integration.
 * Tested up to:      6.8.2
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Version:           1.0.6.15
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
    define('EXAMPLE_PLUGIN_VERSION', '1.0.6.15');
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
        // 'textdomain' is omitted, so the helper will automatically use 'slug'
        
    ];

    // 3) Call the helper in the UUPD\V1 namespace:
    \UUPD\V1\UUPD_Updater_V1::register( $updater_config );
}, 1 );


?>
