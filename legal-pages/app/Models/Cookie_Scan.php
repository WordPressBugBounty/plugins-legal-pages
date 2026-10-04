<?php
namespace LegalPage\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Cookie scanner storage + logic.
 *
 * Flow (modelled on Complianz's local scan): the admin SPA calls start(), then
 * repeatedly next_page(). Each page is fetched server-side for known-service
 * markers, then loaded by the admin in a hidden iframe where the collector
 * script (Controllers\Common\CookieScanner) reads document.cookie + web storage
 * and posts it to store_report(). When the queue is empty the found cookies are
 * enriched from cookiedatabase.org (sync()).
 *
 * Editing single cookies, clearing the table and the manual re-sync are Legal
 * Pages Pro features (LegalPagePro\Models\Cookie_Scan works on this table).
 */
class Cookie_Scan {

    const DB_VERSION        = '1.1.0';
    const DB_VERSION_OPTION = 'adl_lp_cookies_db_version';
    const STATE_OPTION      = 'adl_lp_cookie_scan';
    const NONCE_ACTION      = 'lp_cookie_scan';
    const CDB_ENDPOINT      = 'https://cookiedatabase.org/wp-json/cookiedatabase/v2/cookies/';

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
    ];

    // Storage keys only the admin screens set. The scan runs in the admin's
    // browser, so it would otherwise report them as frontend storage.
    const ADMIN_STORAGE_PATTERN = '/^legal_pages_/';

    // Pages per post type, and an overall cap so a scan stays a few minutes long at most.
    const PER_POST_TYPE = 5;
    const MAX_PAGES     = 40;

    /**
     * True on a frontend page the admin loaded through the scan iframe (valid token + admin).
     * Blocking and the banner step aside so every script runs and sets its cookies.
     */
    public static function is_scan_request(): bool {
        if ( empty( $_GET['lp_cookie_scan'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the param *is* the nonce.
            return false;
        }
        $token = sanitize_text_field( wp_unslash( $_GET['lp_cookie_scan'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return (bool) wp_verify_nonce( $token, self::NONCE_ACTION );
    }

    public static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'adl_lp_cookies';
    }

    public static function maybe_install(): void {
        if ( get_option( self::DB_VERSION_OPTION ) === self::DB_VERSION ) {
            return;
        }
        self::install();
    }

    public static function install(): void {
        global $wpdb;

        $table           = self::table();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(191) NOT NULL,
            storage varchar(20) NOT NULL DEFAULT 'cookie',
            service varchar(191) NOT NULL DEFAULT '',
            category varchar(20) NOT NULL DEFAULT 'unclassified',
            purpose text NOT NULL,
            retention varchar(100) NOT NULL DEFAULT '',
            found_on text NOT NULL,
            source varchar(20) NOT NULL DEFAULT 'detected',
            synced tinyint(1) NOT NULL DEFAULT 0,
            auto_sync tinyint(1) NOT NULL DEFAULT 1,
            ignored tinyint(1) NOT NULL DEFAULT 0,
            first_seen datetime NOT NULL,
            last_seen datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY name_storage (name,storage)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
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

    private static function save_state( array $state ): void {
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

    /**
     * Post types the scan may visit. Posts and pages; Pro adds the rest.
     */
    public static function allowed_post_types(): array {
        return (array) apply_filters( 'legal_pages_cookie_scan_post_types', [ 'post', 'page' ] );
    }

    /**
     * Public post types shown as scan targets (attachments excluded). Types
     * outside allowed_post_types() are listed with `locked` so the SPA can
     * show them as a Pro upsell.
     */
    public static function get_scannable_post_types(): array {
        $types = get_post_types( [ 'public' => true ], 'objects' );
        unset( $types['attachment'] );

        $allowed = self::allowed_post_types();
        $out     = [];
        foreach ( $types as $type ) {
            $out[] = [
                'value'  => $type->name,
                'label'  => $type->labels->name,
                'locked' => ! in_array( $type->name, $allowed, true ),
            ];
        }
        return $out;
    }

    // ── Scan lifecycle ──

    public static function start( array $post_types ): array {
        $valid      = array_intersect( wp_list_pluck( self::get_scannable_post_types(), 'value' ), self::allowed_post_types() );
        $post_types = array_values( array_intersect( array_map( 'sanitize_key', $post_types ), $valid ) );

        $queue = self::build_queue( $post_types );

        $state                      = self::get_state();
        $state['status']            = 'running';
        $state['queue']             = $queue;
        $state['total']             = count( $queue );
        $state['processed']         = 0;
        $state['current_url']       = '';
        $state['post_types']        = $post_types;
        $state['detected_services'] = [];
        $state['started_at']        = current_time( 'mysql' );
        $state['finished_at']       = '';
        self::save_state( $state );

        return self::public_state();
    }

    private static function build_queue( array $post_types ): array {
        $urls = [ home_url( '/' ) ];

        foreach ( $post_types as $post_type ) {
            $ids = get_posts( [
                'post_type'      => $post_type,
                'post_status'    => 'publish',
                'posts_per_page' => self::PER_POST_TYPE,
                'fields'         => 'ids',
                'has_password'   => false,
            ] );
            foreach ( $ids as $id ) {
                $urls[] = get_permalink( $id );
            }
        }

        // Shop pages set the most cookies, so always include them when WooCommerce is active.
        if ( function_exists( 'wc_get_page_id' ) ) {
            foreach ( [ 'shop', 'cart', 'checkout', 'myaccount' ] as $page ) {
                $id = wc_get_page_id( $page );
                if ( $id > 0 ) {
                    $urls[] = get_permalink( $id );
                }
            }
        }

        $urls = array_values( array_unique( array_filter( $urls ) ) );
        return array_slice( $urls, 0, self::MAX_PAGES );
    }

    /**
     * Pop the next URL off the queue, scan its HTML for known services, and
     * return the tokenised URL for the admin to load in its hidden iframe.
     * When the queue is empty the scan is completed and cookies are synced.
     */
    public static function next_page(): array {
        $state = self::get_state();

        if ( 'running' !== $state['status'] ) {
            return [ 'done' => true, 'url' => '', 'state' => self::public_state() ];
        }

        if ( empty( $state['queue'] ) ) {
            $state['status']      = 'complete';
            $state['current_url'] = '';
            $state['finished_at'] = current_time( 'mysql' );
            self::save_state( $state );

            self::sync();

            return [ 'done' => true, 'url' => '', 'state' => self::public_state() ];
        }

        $url = array_shift( $state['queue'] );
        ++$state['processed'];
        $state['current_url']       = $url;
        $state['detected_services'] = self::merge_detected_services( $state['detected_services'], self::detect_services( $url ), $url );
        self::save_state( $state );

        $scan_url = add_query_arg(
            [
                'lp_cookie_scan' => wp_create_nonce( self::NONCE_ACTION ),
            ],
            $url
        );

        return [ 'done' => false, 'url' => $scan_url, 'state' => self::public_state() ];
    }

    public static function stop(): array {
        $state                = self::get_state();
        $state['status']      = 'idle';
        $state['queue']       = [];
        $state['current_url'] = '';
        self::save_state( $state );
        return self::public_state();
    }

    /**
     * Fetch the page anonymously and match it against the Block Services catalog
     * (Cookie_Bar::get_available_services()) — catches third-party services whose
     * cookies live on another domain and so never show up in document.cookie.
     */
    private static function detect_services( string $url ): array {
        $response = wp_remote_get(
            $url,
            [
                'timeout'   => 15,
                'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
            ]
        );
        if ( is_wp_error( $response ) ) {
            return [];
        }

        $html  = wp_remote_retrieve_body( $response );
        $found = [];
        foreach ( Cookie_Bar::get_available_services() as $service ) {
            $pattern = '#' . str_replace( '#', '\#', $service['regex'] ) . '#i';
            if ( @preg_match( $pattern, $html ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors -- user-entered regex may be invalid.
                $found[] = $service['name'];
            }
        }
        return $found;
    }

    private static function merge_detected_services( array $detected, array $names, string $url ): array {
        foreach ( $names as $name ) {
            if ( ! isset( $detected[ $name ] ) ) {
                $detected[ $name ] = [];
            }
            if ( ! in_array( $url, $detected[ $name ], true ) ) {
                $detected[ $name ][] = $url;
            }
        }
        return $detected;
    }

    // ── Cookie storage ──

    /**
     * Save a collector report. $cookies / $local / $session are lists of names.
     */
    public static function store_report( array $cookies, array $local, array $session, string $url ): int {
        $site_url = site_url();
        $stored   = 0;

        $groups = [
            'cookie'         => $cookies,
            'localstorage'   => $local,
            'sessionstorage' => $session,
        ];

        foreach ( $groups as $storage => $names ) {
            foreach ( array_unique( $names ) as $name ) {
                $name = substr( sanitize_text_field( (string) $name ), 0, 191 );
                // Skip empty names and storage keys some plugins build from the site URL.
                if ( '' === $name || false !== strpos( $name, $site_url ) ) {
                    continue;
                }
                if ( 'cookie' !== $storage && preg_match( self::ADMIN_STORAGE_PATTERN, $name ) ) {
                    continue;
                }
                if ( apply_filters( 'legal_pages_cookie_scan_exclude', false, $name, $storage ) ) {
                    continue;
                }
                self::upsert( $name, $storage, $url );
                ++$stored;
            }
        }

        return $stored;
    }

    private static function upsert( string $name, string $storage, string $url ): void {
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

    // ── cookiedatabase.org ──

    /**
     * Enrich cookies with service, purpose, retention and category from
     * cookiedatabase.org. Only cookie names are sent (no site or plugin data).
     * Cookies with auto_sync off (edited by hand) are never touched.
     *
     * @param bool $all Re-sync already synced cookies too (Pro's manual "Sync" button).
     * @return array { synced: int, error: string }
     */
    public static function sync( bool $all = false ): array {
        global $wpdb;

        $table = self::table();
        $where = $all ? 'WHERE auto_sync = 1' : 'WHERE auto_sync = 1 AND synced = 0';
        $rows  = $wpdb->get_results( "SELECT id, name, storage, source FROM {$table} {$where}", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        $result = [ 'synced' => 0, 'error' => '' ];
        if ( ! $rows ) {
            self::record_sync( '' );
            return $result;
        }

        foreach ( array_chunk( $rows, 100 ) as $chunk ) {
            $names   = [];
            $storage = [];
            foreach ( $chunk as $i => $row ) {
                $names[ (string) $i ] = $row['name'];
                if ( 'cookie' !== $row['storage'] ) {
                    $storage[] = $row['name'];
                }
            }

            $response = wp_remote_post(
                self::CDB_ENDPOINT,
                [
                    'timeout' => 30,
                    'headers' => [ 'Content-Type' => 'application/json' ],
                    'body'    => wp_json_encode( [
                        'en'                  => [ 'no-service-set' => $names ],
                        'thirdpartyCookies'   => [],
                        'localstorageCookies' => $storage,
                    ] ),
                ]
            );

            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $result['error'] = __( 'Could not connect to cookiedatabase.org.', 'legal-pages' );
                break;
            }

            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $body['data']['error'] ) ) {
                $result['error'] = sanitize_text_field( $body['data']['error'] );
                break;
            }

            // Response shape: data.en.{service|no-service-set}.{original_name} => cookie object.
            $known = [];
            foreach ( (array) ( $body['data']['en'] ?? [] ) as $cookies ) {
                foreach ( (array) $cookies as $original_name => $info ) {
                    if ( is_array( $info ) ) {
                        $known[ $original_name ] = $info;
                    }
                }
            }

            foreach ( $chunk as $row ) {
                $data = [ 'synced' => 1 ];
                if ( isset( $known[ $row['name'] ] ) ) {
                    $info              = $known[ $row['name'] ];
                    $data['service']   = sanitize_text_field( $info['service'] ?? '' );
                    $data['purpose']   = sanitize_textarea_field( $info['cookieFunction'] ?? '' );
                    $data['retention'] = sanitize_text_field( $info['retention'] ?? '' );
                    $data['category']  = self::map_purpose( (string) ( $info['purpose'] ?? '' ) );
                    if ( ! empty( $info['ignore'] ) ) {
                        $data['ignored'] = 1;
                    }
                    ++$result['synced'];
                }
                $wpdb->update( $table, $data, [ 'id' => $row['id'] ] );
            }
        }

        self::record_sync( $result['error'] );
        return $result;
    }

    private static function record_sync( string $error ): void {
        $state               = self::get_state();
        $state['last_sync']  = current_time( 'mysql' );
        $state['sync_error'] = $error;
        self::save_state( $state );
    }

    /**
     * Map a cookiedatabase.org purpose ("Statistics", "Marketing/Tracking", …) to a consent category.
     */
    private static function map_purpose( string $purpose ): string {
        if ( preg_match( '/statistic|analytic/i', $purpose ) ) {
            return 'statistics';
        }
        if ( preg_match( '/marketing|tracking|advertis/i', $purpose ) ) {
            return 'marketing';
        }
        if ( preg_match( '/preference/i', $purpose ) ) {
            return 'preferences';
        }
        if ( preg_match( '/functional|necessary/i', $purpose ) ) {
            return 'functional';
        }
        return 'unclassified';
    }
}
