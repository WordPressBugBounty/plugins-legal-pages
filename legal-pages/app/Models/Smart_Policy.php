<?php
namespace LegalPage\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Smart Policy storage: the saved detection result. Detection itself is in
 * Services\Smart_Policy.
 *
 * Options aren't autoloaded: the frontend only needs them on pages that hold
 * [lp_smart_policy].
 */
class Smart_Policy {

    const DETECTED_OPTION     = 'lp_smart_policy_detected';
    const LAST_CHECKED_OPTION = 'lp_smart_policy_last_checked';
    const SHORTCODE           = 'lp_smart_policy';

    /**
     * Detected services: [ id => [ 'sources' => [ 'plugin' ], 'detected_at' => timestamp ] ].
     */
    public static function get_detected(): array {
        $detected = get_option( self::DETECTED_OPTION, [] );
        return is_array( $detected ) ? $detected : [];
    }

    public static function save_detected( array $detected ): void {
        update_option( self::DETECTED_OPTION, $detected, false );
    }

    /**
     * When every check last ran, or 0 if that has never happened.
     */
    public static function last_checked(): int {
        return (int) get_option( self::LAST_CHECKED_OPTION, 0 );
    }

    public static function save_last_checked( int $time ): void {
        update_option( self::LAST_CHECKED_OPTION, $time, false );
    }

    /**
     * True if a published post of $post_types contains $text. Legal pages are left
     * out, so a policy that mentions a service doesn't count as using it.
     *
     * Slow on big sites (LIKE can't use an index): never call this on a frontend request.
     */
    public static function content_contains( string $text, array $post_types ): bool {
        global $wpdb;

        $post_types = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );
        if ( ! $post_types ) {
            return false;
        }

        $in = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT 1 FROM {$wpdb->posts} p
                WHERE p.post_status = 'publish'
                    AND p.post_type IN ( {$in} )
                    AND p.post_content LIKE %s
                    AND NOT EXISTS (
                        SELECT 1 FROM {$wpdb->postmeta} m
                        WHERE m.post_id = p.ID AND m.meta_key = 'is_adl_legal_page' AND m.meta_value = '1'
                    )
                LIMIT 1",
                array_merge( $post_types, [ '%' . $wpdb->esc_like( $text ) . '%' ] )
            )
        );
        // phpcs:enable
    }

    /**
     * Published and draft posts of any type holding [lp_smart_policy].
     *
     * @return array[] { id, title, status, url }
     */
    public static function pages_with_shortcode(): array {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title, post_status FROM {$wpdb->posts}
                WHERE post_status IN ( 'publish', 'draft' ) AND post_content LIKE %s
                ORDER BY post_title ASC",
                '%' . $wpdb->esc_like( '[' . self::SHORTCODE ) . '%'
            ),
            ARRAY_A
        );

        return array_map(
            static function ( $row ) {
                return [
                    'id'     => (int) $row['ID'],
                    'title'  => $row['post_title'],
                    'status' => $row['post_status'],
                    'url'    => (string) get_permalink( (int) $row['ID'] ),
                ];
            },
            $rows ?: []
        );
    }
}
