<?php
namespace LegalPage\Models;

use LegalPage\Core\Activator;
use LegalPage\Services\Cookie_Scan as Scan_Service;

defined( 'ABSPATH' ) || exit;

/**
 * Cookie scanner storage: the {prefix}adl_lp_cookies rows and the scan state
 * option. The table is created by Core\Activator; the scan flow and the
 * cookiedatabase.org sync are in Services\Cookie_Scan.
 *
 * Editing single cookies, clearing the table and the manual re-sync are Legal
 * Pages Pro features (LegalPagePro\Models\Cookie_Scan works on this table).
 */
class Cookie_Scan {

    const STATE_OPTION = 'adl_lp_cookie_scan';
    const NONCE_ACTION = 'lp_cookie_scan';

    const CATEGORIES = [ 'functional', 'preferences', 'statistics', 'marketing', 'unclassified' ];
    const STORAGES   = [ 'cookie', 'localstorage', 'sessionstorage' ];

    // Cookies this plugin sets itself; cookiedatabase.org doesn't know them.
    const OWN_COOKIES = [
        'lp_cookie_consent' => [
            'service'   => 'Legal Pages',
            'category'  => 'functional',
            'purpose'   => 'store cookie consent preferences',
            'retention' => '1 year',
        ],
        // Set by Legal Pages Pro's consent log.
        'lp_consent_id' => [
            'service'   => 'Legal Pages',
            'category'  => 'functional',
            'purpose'   => 'identify the visitor\'s consent record (proof of consent)',
            'retention' => '1 year',
        ],
        // Set by Legal Pages Pro's geo-targeted banners.
        'lp_region' => [
            'service'   => 'Legal Pages',
            'category'  => 'functional',
            'purpose'   => 'remember which cookie banner applies in the visitor\'s region',
            'retention' => '30 days',
        ],
    ];

    public static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'adl_lp_cookies';
    }

    // ── Kept for Legal Pages Pro 2.1.0, which calls these on this class ──

    public static function install(): void {
        Activator::create_cookies_table();
    }

    public static function maybe_install(): void {
        Activator::maybe_create_cookies_table();
    }

    public static function is_scan_request(): bool {
        return Scan_Service::is_scan_request();
    }

    public static function sync( bool $all = false ): array {
        return Scan_Service::sync( $all );
    }

    // ── State ──

    private static function default_state(): array {
        return [
            'status'            => 'idle', // idle | running | complete
            'queue'             => [],
            'total'             => 0,
            'processed'         => 0,
            'current_url'       => '',
            'post_types'        => [ 'page', 'post' ],
            'detected_services' => [],
            'started_at'        => '',
            'finished_at'       => '',
            'last_sync'         => '',
            'sync_error'        => '',
        ];
    }

    public static function get_state(): array {
        $saved = get_option( self::STATE_OPTION, [] );
        return wp_parse_args( is_array( $saved ) ? $saved : [], self::default_state() );
    }

    public static function save_state( array $state ): void {
        update_option( self::STATE_OPTION, $state, false );
    }

    /**
     * State as returned to the SPA (queue omitted, progress added).
     */
    public static function public_state(): array {
        $state = self::get_state();
        unset( $state['queue'] );
        $state['progress'] = $state['total'] > 0
            ? (int) round( $state['processed'] / $state['total'] * 100 )
            : ( 'complete' === $state['status'] ? 100 : 0 );
        return $state;
    }

    // ── Cookie rows ──

    /**
     * Add a found cookie, or add $url to the pages an existing one was seen on.
     */
    public static function upsert( string $name, string $storage, string $url ): void {
        global $wpdb;

        $table = self::table();
        $now   = current_time( 'mysql' );
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT id, found_on FROM {$table} WHERE name = %s AND storage = %s", $name, $storage ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        if ( $row ) {
            $found_on = json_decode( $row->found_on, true );
            $found_on = is_array( $found_on ) ? $found_on : [];
            if ( $url && ! in_array( $url, $found_on, true ) && count( $found_on ) < 20 ) {
                $found_on[] = $url;
            }
            $wpdb->update(
                $table,
                [ 'found_on' => wp_json_encode( $found_on ), 'last_seen' => $now ],
                [ 'id' => $row->id ]
            );
            return;
        }

        $data = [
            'name'       => $name,
            'storage'    => $storage,
            'purpose'    => '',
            'found_on'   => wp_json_encode( $url ? [ $url ] : [] ),
            'source'     => 'detected',
            'first_seen' => $now,
            'last_seen'  => $now,
        ];
        if ( 'cookie' === $storage && isset( self::OWN_COOKIES[ $name ] ) ) {
            // Pre-described and marked synced; sync() only overwrites fields for names it finds.
            $data = array_merge( $data, self::OWN_COOKIES[ $name ], [ 'synced' => 1 ] );
        }

        $wpdb->insert( $table, $data );
    }

    public static function get_cookies(): array {
        global $wpdb;

        $table = self::table();
        $rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY FIELD(category,'functional','preferences','statistics','marketing','unclassified'), name ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        return array_map( [ __CLASS__, 'format_row' ], $rows ?: [] );
    }

    private static function format_row( array $row ): array {
        $found_on = json_decode( $row['found_on'], true );
        return [
            'id'         => (int) $row['id'],
            'name'       => $row['name'],
            'storage'    => $row['storage'],
            'service'    => $row['service'],
            'category'   => $row['category'],
            'purpose'    => $row['purpose'],
            'retention'  => $row['retention'],
            'found_on'   => is_array( $found_on ) ? $found_on : [],
            'source'     => $row['source'],
            'synced'     => (bool) $row['synced'],
            'auto_sync'  => (bool) $row['auto_sync'],
            'ignored'    => (bool) $row['ignored'],
            'first_seen' => $row['first_seen'],
            'last_seen'  => $row['last_seen'],
        ];
    }

    public static function get_cookie( int $id ): ?array {
        global $wpdb;

        $table = self::table();
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $row ? self::format_row( $row ) : null;
    }

    /**
     * Rows cookiedatabase.org may update: auto_sync ones, and only unsynced ones unless $all.
     */
    public static function rows_to_sync( bool $all ): array {
        global $wpdb;

        $table = self::table();
        $where = $all ? 'WHERE auto_sync = 1' : 'WHERE auto_sync = 1 AND synced = 0';
        return $wpdb->get_results( "SELECT id, name, storage, source FROM {$table} {$where}", ARRAY_A ) ?: []; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    public static function update_row( int $id, array $data ): void {
        global $wpdb;
        $wpdb->update( self::table(), $data, [ 'id' => $id ] );
    }
}
