<?php
namespace LegalPage\API;

use WP_REST_Request;
use WP_REST_Response;
use LegalPage\Models\Smart_Policy as Policy_Model;
use LegalPage\Services\Smart_Policy as Policy_Service;

defined( 'ABSPATH' ) || exit;

/**
 * Smart Policy admin screen routes. Legal Pages Pro adds overrides and the notice.
 */
class Smart_Policy {

    public function check_permissions() {
        return current_user_can( 'manage_options' );
    }

    /**
     * The first visit runs every check, so an updated site doesn't open on an
     * empty list while it waits for the daily cron.
     */
    public function get_overview( WP_REST_Request $request ) {
        if ( ! Policy_Model::last_checked() ) {
            Policy_Service::detect( Policy_Service::ALL_CHECKS );
        }
        return new WP_REST_Response( [ 'success' => true, 'data' => Policy_Service::overview() ], 200 );
    }

    public function recheck( WP_REST_Request $request ) {
        Policy_Service::detect( Policy_Service::ALL_CHECKS );
        return new WP_REST_Response( [ 'success' => true, 'data' => Policy_Service::overview() ], 200 );
    }
}
