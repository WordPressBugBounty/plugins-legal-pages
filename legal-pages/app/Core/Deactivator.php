<?php
namespace LegalPage\Core;

/**
 * Deactivator Class
 * Handles plugin deactivation tasks
 *
 * @package LegalPage
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Deactivator {

    /**
     * Deactivation tasks
     *
     * @return void
     */
    public static function deactivate() {
        global $wpdb;

        // Get plugin settings
        $misc_settings = get_option( 'adl_lp_misc' );

        // Check if user wants to delete plugin data on deactivation
        if ( ! empty( $misc_settings['delete_adl_lp_data'] ) ) {
            self::remove_plugin_data();
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Remove all plugin data from database
     *
     * @return void
     */
    private static function remove_plugin_data() {
        global $wpdb;

        // Define table name
        $template_table_name = $wpdb->prefix . 'adl_lp_templates';

        // Delete plugin options
        delete_option( 'adl_lp_excludePage' );
        delete_option( 'adl_lp_general' );
        delete_option( 'adl_lp_social' );
        delete_option( 'adl_lp_accept_term' );
        delete_option( 'adl_lp_misc' );
        delete_option( 'adl_demo_inserted' );
        delete_option( 'adl_ccpa_demo_inserted' );
        delete_option( 'wplp_legal_page_discount' );

        // Drop custom table
        $wpdb->query( "DROP TABLE IF EXISTS {$template_table_name}" );

        // Delete post meta for legal pages
        $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'is_adl_legal_page'" );
    }
}