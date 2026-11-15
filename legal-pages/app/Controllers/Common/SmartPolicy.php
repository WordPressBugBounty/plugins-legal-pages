<?php
namespace LegalPage\Controllers\Common;

use LegalPage\Traits\Hook;
use LegalPage\Services\Smart_Policy as Policy_Service;

defined( 'ABSPATH' ) || exit;

/**
 * Starts Smart Policy detection: when a plugin is switched on or off, when a
 * cookie scan finishes, and once a day. Common, not Admin, so WP-CLI and cron
 * requests are covered. Nothing here runs on a normal page view except
 * scheduling the cron.
 */
class SmartPolicy {

    use Hook;

    private $plugin_change_seen = false;

    public function __construct() {
        $this->action( 'activated_plugin', [ $this, 'plugin_changed' ] );
        $this->action( 'deactivated_plugin', [ $this, 'plugin_changed' ] );
        $this->action( 'legal_pages_cookie_scan_complete', [ $this, 'detect_from_scan' ] );
        $this->action( Policy_Service::CRON_HOOK, [ $this, 'detect_all' ] );
        $this->action( 'init', [ $this, 'schedule' ] );
    }

    /**
     * Wait for shutdown: deactivate_plugins() fires deactivated_plugin before it
     * saves active_plugins, so is_plugin_active() is still true at this point.
     * It also makes a bulk action one run instead of one per plugin.
     */
    public function plugin_changed() {
        if ( $this->plugin_change_seen ) {
            return;
        }
        $this->plugin_change_seen = true;
        $this->action( 'shutdown', [ $this, 'detect_quick' ] );
    }

    public function detect_quick() {
        Policy_Service::detect( Policy_Service::QUICK_CHECKS );
    }

    public function detect_from_scan() {
        Policy_Service::detect( [ 'cookie', 'scan' ] );
    }

    public function detect_all() {
        Policy_Service::detect( Policy_Service::ALL_CHECKS );
    }

    public function schedule() {
        if ( ! wp_next_scheduled( Policy_Service::CRON_HOOK ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', Policy_Service::CRON_HOOK );
        }
    }
}
