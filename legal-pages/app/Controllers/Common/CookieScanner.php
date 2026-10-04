<?php
namespace LegalPage\Controllers\Common;

use LegalPage\Traits\Hook;
use LegalPage\Models\Cookie_Scan;

defined( 'ABSPATH' ) || exit;

/**
 * Cookie scanner runtime: keeps the cookies table installed, and on the
 * frontend prints the collector script into pages the admin SPA loads in its
 * hidden scan iframe. See Models\Cookie_Scan for the overall flow.
 */
class CookieScanner {

    use Hook;

    public function __construct() {
        $this->action( 'plugins_loaded', [ $this, 'boot' ] );
    }

    public function boot() {
        if ( legal_pages_cookie_bar_in_legacy_pro() ) {
            return;
        }
        // Creates/upgrades the table on every version, like Activator does for the free plugin's own data.
        $this->action( 'init', [ Cookie_Scan::class, 'maybe_install' ] );
        $this->action( 'wp_footer', [ $this, 'print_collector' ], PHP_INT_MAX );
    }

    public function print_collector() {
        if ( is_admin() || ! Cookie_Scan::is_scan_request() ) {
            return;
        }

        $config = [
            'endpoint' => esc_url_raw( rest_url( 'legal-pages/v1/cookie-scan/report' ) ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'token'    => sanitize_text_field( wp_unslash( $_GET['lp_cookie_scan'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ];
        ?>
        <script>
        ( function ( config ) {
            // Give async/deferred third-party scripts time to set their cookies.
            var DELAY = 3000;

            function storageKeys( store ) {
                var keys = [];
                try {
                    for ( var i = 0; i < store.length; i++ ) {
                        keys.push( store.key( i ) );
                    }
                } catch ( e ) {}
                return keys;
            }

            function cookieNames() {
                if ( ! document.cookie ) {
                    return [];
                }
                return document.cookie.split( ';' ).map( function ( pair ) {
                    var name = pair.split( '=' )[0].trim();
                    try { return decodeURIComponent( name ); } catch ( e ) { return name; }
                } ).filter( Boolean );
            }

            function notifyParent( ok ) {
                if ( window.parent && window.parent !== window ) {
                    window.parent.postMessage( { type: 'lp-cookie-scan-reported', ok: ok }, window.location.origin );
                }
            }

            function report() {
                var url = window.location.href.replace( /([?&])lp_cookie_scan=[^&]*&?/, '$1' ).replace( /[?&]$/, '' );
                fetch( config.endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce },
                    body: JSON.stringify( {
                        token: config.token,
                        url: url,
                        cookies: cookieNames(),
                        localstorage: storageKeys( window.localStorage ),
                        sessionstorage: storageKeys( window.sessionStorage )
                    } )
                } ).then( function ( res ) {
                    notifyParent( res.ok );
                } ).catch( function () {
                    notifyParent( false );
                } );
            }

            if ( document.readyState === 'complete' ) {
                setTimeout( report, DELAY );
            } else {
                window.addEventListener( 'load', function () { setTimeout( report, DELAY ); } );
            }
        } )( <?php echo wp_json_encode( $config ); ?> );
        </script>
        <?php
    }
}
