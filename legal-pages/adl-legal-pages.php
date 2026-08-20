<?php
/*
Plugin Name: Legal Pages
Plugin URI: https://wpwax.com/product/legal-pages-pro
Description: A very useful plugin to generate legal pages for your websites/ business. It is simple, easy and elegant to use. It comes with ready-made templates which gives you even better experience creating legal pages with ease. You can customize the page template too.
Version: 1.6.3
Author: wpWax
Author URI: https://wpwax.com
License: GPLv2 or later
Text Domain: legal-pages
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2016 wpwax.
*/
namespace LegalPage;

defined( 'ABSPATH' ) || exit;

define( 'LEGAL_PAGES_FILE', __FILE__ );
define( 'LEGAL_PAGES_VERSION', '1.6.3' );
define( 'LEGAL_PAGES_PLUGIN_DIR', plugin_dir_path( LEGAL_PAGES_FILE ) );
define( 'LEGAL_PAGES_URL', plugin_dir_url( LEGAL_PAGES_FILE ) );
define( 'LEGAL_PAGES_ASSETS_URL', LEGAL_PAGES_URL . 'assets/' );
define( 'LEGAL_PAGES_BUILD_URL', LEGAL_PAGES_URL . 'build/' );
define( 'LEGAL_PAGES_SPA_URL', LEGAL_PAGES_URL . 'spa/' );


if ( ! defined( 'WPLP_REMOTE_POST_ID' ) ) {
    define( 'WPLP_REMOTE_POST_ID', 3315 );
}

if ( ! defined( 'WPLP_REMOTE_URL' ) ) {
    define( 'WPLP_REMOTE_URL', 'https://wpwax.com' );
}

// if ( ! defined( 'WPLP_VERSION' ) ) {
//     define( 'WPLP_VERSION', '1.7.2' );
// }

/**
 * Composer autoloader or manual fallback
 */

require_once 'vendor/autoload.php';

// add_action( 'plugins_loaded', function() {
//    echo LEGAL_PAGES_ASSETS_URL . '/admin/js/admin.js';
// } );

// Bootstrap the Initializer
if ( class_exists( 'LegalPage\\Core\\Initializer' ) ) {
    $legalpage_init = Core\Initializer::get_instance();
    $legalpage_init->init();
}

// Activation hook
// Auto-run on plugin update (without needing deactivate/activate)
// Run on 'init' hook instead of 'plugins_loaded' to ensure WordPress is fully loaded
add_action( 'init', function() {
    $installed_version = get_option( 'adl_lp_plugin_version' );

    // If version is different (update) or not set (fresh install), run activation tasks
    if ( $installed_version !== LEGAL_PAGES_VERSION ) {
        if ( class_exists( 'LegalPage\\Core\\Activator' ) ) {
            Core\Activator::activate();
        }
    }
}, 5 ); // Priority 5 to run early on init but after WordPress is loaded

// Also register traditional activation hook for fresh installs
register_activation_hook( __FILE__, function() {
    if ( class_exists( 'LegalPage\\Core\\Activator' ) ) {
        Core\Activator::activate();
    }
} );

// Deactivation hook
register_deactivation_hook( __FILE__, function() {
    if ( class_exists( 'PhotoVault\\Core\\Deactivator' ) ) {
        Core\Deactivator::deactivate();
    }
} );