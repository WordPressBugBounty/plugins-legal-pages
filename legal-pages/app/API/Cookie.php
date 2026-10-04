<?php
namespace LegalPage\API;

use WP_REST_Request;
use WP_REST_Response;
use LegalPage\Models\Cookie_Bar;
use LegalPage\Models\Cookie_Scan;

defined( 'ABSPATH' ) || exit;

/**
 * Cookie bar settings + cookie scanner routes. Legal Pages Pro adds the
 * editing routes (sync, clear, per-cookie update, Block Services).
 */
class Cookie {

    public function check_permissions() {
        return current_user_can( 'manage_options' );
    }

    public function get_settings( WP_REST_Request $request ) {
        return new WP_REST_Response( [ 'success' => true, 'data' => Cookie_Bar::get() ], 200 );
    }

    public function save_settings( WP_REST_Request $request ) {
        $params = (array) $request->get_json_params();
        return new WP_REST_Response( [ 'success' => true, 'data' => Cookie_Bar::save( $params ) ], 200 );
    }

    public function get_scan( WP_REST_Request $request ) {
        return new WP_REST_Response( [
            'success' => true,
            'data'    => [
                'state'      => Cookie_Scan::public_state(),
                'cookies'    => Cookie_Scan::get_cookies(),
                'post_types' => Cookie_Scan::get_scannable_post_types(),
            ],
        ], 200 );
    }

    public function start_scan( WP_REST_Request $request ) {
        $post_types = $request->get_param( 'post_types' );
        $state      = Cookie_Scan::start( is_array( $post_types ) ? $post_types : [] );
        return new WP_REST_Response( [ 'success' => true, 'data' => $state ], 200 );
    }

    public function next_page( WP_REST_Request $request ) {
        return new WP_REST_Response( [ 'success' => true, 'data' => Cookie_Scan::next_page() ], 200 );
    }

    public function stop_scan( WP_REST_Request $request ) {
        return new WP_REST_Response( [ 'success' => true, 'data' => Cookie_Scan::stop() ], 200 );
    }

    /**
     * Receives the collector payload posted from the scanned page inside the admin's iframe.
     */
    public function report( WP_REST_Request $request ) {
        $token = (string) $request->get_param( 'token' );
        if ( ! wp_verify_nonce( $token, Cookie_Scan::NONCE_ACTION ) ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => 'Invalid scan token.' ], 403 );
        }

        $cookies = $request->get_param( 'cookies' );
        $local   = $request->get_param( 'localstorage' );
        $session = $request->get_param( 'sessionstorage' );

        // HttpOnly cookies are invisible to document.cookie but arrive with this request.
        $cookies = array_merge( is_array( $cookies ) ? $cookies : [], array_keys( $_COOKIE ) );

        $stored = Cookie_Scan::store_report(
            $cookies,
            is_array( $local ) ? $local : [],
            is_array( $session ) ? $session : [],
            esc_url_raw( (string) $request->get_param( 'url' ) )
        );

        return new WP_REST_Response( [ 'success' => true, 'data' => [ 'stored' => $stored ] ], 200 );
    }
}
