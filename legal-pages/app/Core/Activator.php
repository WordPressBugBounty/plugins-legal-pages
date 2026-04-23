<?php
namespace LegalPage\Core;

use LegalPage\Helpers\Utility;

/**
 * Activator Class
 * Handles plugin activation tasks
 *
 * @package LegalPage
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Activator {

    /**
     * Activation tasks
     *
     * @return void
     */
    public static function activate() {
        global $wpdb;

        // Define table name
        $template_table_name = $wpdb->prefix . 'adl_lp_templates';

        if ( ! get_option( 'adl_lp_excludePage' ) ) {
            update_option( 'adl_lp_excludePage', 'true' );
        }
        if ( ! get_option( 'adl_lp_general' ) ) {
            update_option( 'adl_lp_general', array() );
        }

        // $get_data = get_option( 'adl_lp_general', [] );
        // if ( ! $get_data ) {
        //     update_option( 'adl_lp_general', [] );
        // } else {
        //     $data = is_array( $get_data ) ? $get_data : maybe_unserialize( $get_data );
        //     // if ( ! is_array( $data ) ) {
        //         update_option( 'adl_lp_general', [] );
        //     // }
        // }
        if ( ! get_option( 'adl_lp_social' ) ) {
            update_option( 'adl_lp_social', array() );
        }
        if ( ! get_option( 'adl_lp_accept_term' ) ) {
            update_option( 'adl_lp_accept_term', 0 );
        }
        if ( ! get_option( 'adl_lp_misc' ) ) {
            $adl_lp_misc = array(
                'hide_lp_in_search'  => 0,
                'delete_adl_lp_data' => 0,
            );
            update_option( 'adl_lp_misc', $adl_lp_misc );
        }

        // Create database table
        self::create_template_table( $template_table_name );

        self::migrate_from_old_version( $template_table_name );

        // Migrate existing templates to use shortcodes instead of hardcoded values
        self::migrate_templates_to_shortcodes( $template_table_name );

        self::migrate_legal_pages_to_shortcodes();

        self::insert_free_templates( $template_table_name );
        self::insert_pro_templates( $template_table_name );
        update_option( 'adl_lp_plugin_version', LEGAL_PAGES_VERSION );

        flush_rewrite_rules();
    }

    /**
     * Migrate existing WordPress legal pages:
     * replace hardcoded setting values back to shortcodes
     */
    private static function migrate_legal_pages_to_shortcodes() {

        // if ( get_option( 'adl_lp_pages_shortcode_migration_' . LEGAL_PAGES_VERSION ) ) {
        //     return;
        // }

        $general = get_option( 'adl_lp_general', array() );
        $social  = get_option( 'adl_lp_social',  array() );

        $all_settings = array_merge( $general, $social );

        $get = function( $new_key, $old_key ) use ( $all_settings ) {
            if ( ! empty( $all_settings[ $new_key ] ) ) return $all_settings[ $new_key ];
            if ( ! empty( $all_settings[ $old_key ] ) ) return $all_settings[ $old_key ];
            return '';
        };

        $fb = $get( 'facebook_url',    'facebookUrl' );
        $gp = $get( 'google_plus_url', 'googlePlusUrl' );
        $li = $get( 'linkedin_url',    'linkedInUrl' );
        $tw = $get( 'twitter_url',     'twitterUrl' );

        // 1. WordPress editor-generated anchor patterns (highest priority)
        $wp_editor_patterns = array(
            '<a href="[facebookUrl]" target="_blank" rel="noopener"> Find Us on Facebook</a>'        => '[facebookUrl]',
            '<a href="[facebookUrl]" target="_blank" rel="noopener">Find Us on Facebook</a>'         => '[facebookUrl]',
            '<a href="[googlePlusUrl]" target="_blank" rel="noopener"> Connect us on Google Plus</a>' => '[googlePlusUrl]',
            '<a href="[googlePlusUrl]" target="_blank" rel="noopener">Connect us on Google Plus</a>'  => '[googlePlusUrl]',
            '<a href="[linkedInUrl]" target="_blank" rel="noopener"> Connect us on LinkedIn</a>'      => '[linkedInUrl]',
            '<a href="[linkedInUrl]" target="_blank" rel="noopener">Connect us on LinkedIn</a>'       => '[linkedInUrl]',
            '<a href="[twitterUrl]" target="_blank" rel="noopener"> Follow Us on Twitter</a>'         => '[twitterUrl]',
            '<a href="[twitterUrl]" target="_blank" rel="noopener">Follow Us on Twitter</a>'          => '[twitterUrl]',
        );

        // 2. Old plugin anchor patterns with real URLs
        $anchor_replacements = array();

        if ( $fb ) {
            $anchor_replacements[ "<a href='{$fb}' target='_blank'> Find Us on Facebook</a>" ]       = '[facebookUrl]';
            $anchor_replacements[ "<a href=\"{$fb}\" target=\"_blank\"> Find Us on Facebook</a>" ]    = '[facebookUrl]';
            $anchor_replacements[ $fb ] = '[facebookUrl]';
        }
        if ( $gp ) {
            $anchor_replacements[ "<a href='{$gp}' target='_blank'> Connect us on Google Plus</a>" ]      = '[googlePlusUrl]';
            $anchor_replacements[ "<a href=\"{$gp}\" target=\"_blank\"> Connect us on Google Plus</a>" ]   = '[googlePlusUrl]';
            $anchor_replacements[ $gp ] = '[googlePlusUrl]';
        }
        if ( $li ) {
            $anchor_replacements[ "<a href='{$li}' target='_blank'> Connect us on LinkedIn</a>" ]     = '[linkedInUrl]';
            $anchor_replacements[ "<a href=\"{$li}\" target=\"_blank\"> Connect us on LinkedIn</a>" ]  = '[linkedInUrl]';
            $anchor_replacements[ $li ] = '[linkedInUrl]';
        }
        if ( $tw ) {
            $anchor_replacements[ "<a href='{$tw}' target='_blank'> Follow Us on Twitter</a>" ]       = '[twitterUrl]';
            $anchor_replacements[ "<a href=\"{$tw}\" target=\"_blank\"> Follow Us on Twitter</a>" ]    = '[twitterUrl]';
            $anchor_replacements[ $tw ] = '[twitterUrl]';
        }

        // 3. Plain field value replacements
        $map = array(
            'site_url'         => '[siteUrl]',       'siteUrl'         => '[siteUrl]',
            'site_name'        => '[siteName]',      'siteName'        => '[siteName]',   'business_name' => '[siteName]',
            'business_niche'   => '[businessNiche]', 'businessNiche'   => '[businessNiche]',
            'phone_number'     => '[phoneNumber]',   'phoneNumber'     => '[phoneNumber]',
            'email_address'    => '[emailAddress]',  'emailAddress'    => '[emailAddress]',
            'street_name'      => '[streetName]',    'streetName'      => '[streetName]',
            'city'             => '[cityName]',      'cityName'        => '[cityName]',
            'state'            => '[stateName]',     'stateName'       => '[stateName]',
            'country_name'     => '[countryName]',   'countryName'     => '[countryName]',
            'zip_code'         => '[zipCode]',       'zipCode'         => '[zipCode]',
            'complete_address' => '[mailingAddress]','mailingAddress'  => '[mailingAddress]',
        );

        $plain_replacements = array();
        foreach ( $map as $key => $shortcode ) {
            if ( ! empty( $all_settings[ $key ] ) && strlen( $all_settings[ $key ] ) > 3 ) {
                $plain_replacements[ $all_settings[ $key ] ] = $shortcode;
            }
        }

        // Merge in priority order: wp_editor > anchor > plain
        $replacements = array_merge( $wp_editor_patterns, $anchor_replacements, $plain_replacements );

        // Sort by key length descending to avoid partial replacements
        uksort( $replacements, function( $a, $b ) {
            return strlen( $b ) - strlen( $a );
        } );

        if ( empty( $replacements ) ) {
            update_option( 'adl_lp_pages_shortcode_migration_' . LEGAL_PAGES_VERSION, true );
            return;
        }

        $legal_pages = get_posts( array(
            'post_type'      => 'page',
            'post_status'    => array( 'publish', 'draft' ),
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'is_adl_legal_page',
                    'value'   => array( '1', 1, true ),
                    'compare' => 'IN',
                ),
            ),
            'fields' => 'ids',
        ) );

        foreach ( $legal_pages as $page_id ) {
            $post = get_post( $page_id );
            if ( ! $post ) continue;

            $content          = $post->post_content;
            $original_content = $content;

            foreach ( $replacements as $value => $shortcode ) {
                $content = str_replace( $value, $shortcode, $content );
            }

            if ( $content !== $original_content ) {
                wp_update_post( array(
                    'ID'           => $page_id,
                    'post_content' => $content,
                ) );
            }
        }

        update_option( 'adl_lp_pages_shortcode_migration_' . LEGAL_PAGES_VERSION, true );
    }

    /**
     * Migrate existing template content to use shortcodes instead of hardcoded values
     * This ensures that when users update settings, all templates reflect the changes
     *
     * @param string $table_name Table name
     * @return void
     */
    private static function migrate_templates_to_shortcodes( $table_name ) {
        // Skip if already migrated in this version
        if ( get_option( 'adl_lp_shortcode_migration_done_' . LEGAL_PAGES_VERSION ) ) {
            return;
        }

        global $wpdb;

        // Get current settings to find hardcoded values
        $settings = get_option( 'adl_lp_general', array() );

        // Define shortcode mappings (shortcode => setting_key)
        $shortcode_mappings = array(
            '[siteUrl]'         => 'site_url',
            '[siteName]'        => 'site_name',
            '[businessNiche]'   => 'business_niche',
            '[phoneNumber]'     => 'phone_number',
            '[emailAddress]'    => 'email_address',
            '[streetName]'      => 'street_name',
            '[cityName]'        => 'city',
            '[citynNme]'        => 'city', // Typo variant
            '[stateName]'       => 'state',
            '[countryName]'     => 'country_name',
            '[zipCode]'         => 'zip_code',
            '[mailingAddress]'  => 'complete_address',
            '[facebookUrl]'     => 'facebook_url',
            '[googlePlusUrl]'   => 'google_plus_url',
            '[linkedinUrl]'     => 'linkedin_url',
            '[twitterUrl]'      => 'twitter_url',
        );

        // Get all templates from database
        $templates = $wpdb->get_results(
            "SELECT id, content FROM {$table_name}",
            ARRAY_A
        );

        if ( empty( $templates ) ) {
            update_option( 'adl_lp_shortcode_migration_done_' . LEGAL_PAGES_VERSION, true );
            return;
        }

        // Process each template
        foreach ( $templates as $template ) {
            $content = $template['content'];
            $original_content = $content;

            // Replace hardcoded values with shortcodes
            // Order matters - replace longer/more specific values first to avoid partial matches
            $values_to_replace = array();
            
            foreach ( $shortcode_mappings as $shortcode => $setting_key ) {
                if ( ! empty( $settings[ $setting_key ] ) ) {
                    $value = $settings[ $setting_key ];
                    $values_to_replace[ $value ] = $shortcode;
                }
            }

            // Sort by value length (descending) to replace longer strings first
            uksort( $values_to_replace, function( $a, $b ) {
                return strlen( $b ) - strlen( $a );
            } );

            // Perform replacements
            foreach ( $values_to_replace as $value => $shortcode ) {
                // Only replace if the value is not empty and is a reasonable length
                // This prevents replacing single characters or empty strings
                if ( strlen( $value ) > 2 ) {
                    $content = str_replace( $value, $shortcode, $content );
                }
            }

            // Only update if content changed
            if ( $content !== $original_content ) {
                $wpdb->update(
                    $table_name,
                    array( 'content' => $content ),
                    array( 'id' => $template['id'] ),
                    array( '%s' ),
                    array( '%d' )
                );
            }
        }

        // Mark migration as done for this version
        update_option( 'adl_lp_shortcode_migration_done_' . LEGAL_PAGES_VERSION, true );
    }

    /**
     * Migrate data from old version to new version
     *
     * @param string $table_name Table name
     * @return void
     */
    private static function migrate_from_old_version( $table_name ) {
        global $wpdb;

        // Old type values that belong to legacy templates — delete them entirely
        $old_types = array(
            '1a2b3c4d5e6f7g8h9i',
            '10j',
            '4d',
            'abcdefghij',
            '1a2b3c4d5e6f7g8h9i10j',
            '4d5e6f7g8h',
            '2b3c9i',
            '3c',
            '4d5e7g',
            '6f',
            '7g',
            '8h',
            '4d5e6f7g8h9i',
            '1a2b3c4d5e',
        );

        foreach ( $old_types as $old_type ) {
            $wpdb->delete(
                $table_name,
                array( 'type' => $old_type ),
                array( '%s' )
            );
        }

        // Reset flags so fresh clean templates get re-inserted
        delete_option( 'adl_free_templates_inserted' );
        delete_option( 'adl_pro_templates_inserted' );
        update_option( 'adl_templates_migrated', true );
    }

    /**
     * Get template name mapping (old name => new filename)
     *
     * @return array Template name mapping
     */
    private static function get_template_name_mapping() {
        return array(
            // Free templates mapping
            'Terms of Use'                           => 'term-new.php',
            'Privacy Policy'                         => 'privacy.php',
            'Cookie Privacy Policy'                  => 'privacy-cookie-policy.php',
            'DMCA'                                   => 'dmca.php',
            'California Consumer Privacy Act (CCPA)' => 'ccpa.php',
            
            // Pro templates mapping
            'Advertising Disclosures'                => 'advertising-disclosures.php',
            'Confidentiality Disclosure'             => 'confidentiality-disclosures.php',
            'End-user License Agreement'             => 'end-user-license-agreement.php',
            'Forced Agreement to the Terms'          => 'terms-latest.php',
            'Terms'                                  => 'terms.php',
            'California Privacy Rights'              => 'privacy-california.php',
            'EU Privacy Policy'                      => 'eu-privacy.php',
            'Earnings Disclaimer'                    => 'earnings.php',
            'Disclaimer'                             => 'disclaimer.php',
            'Testimonials Disclosure'                => 'testimonial-disclosure.php',
            'Linking Policy'                         => 'linking-policy.php',
            'Refund-Policy'                          => 'refund-policy.php',
            'Affiliate Agreement'                    => 'affiliate-agreement.php',
            'Antispam'                               => 'antispam.php',
            'FTC Statement'                          => 'ftcstatement.php',
            'Medical Disclaimer'                     => 'medical-disclaimer.php',
            'Amazon Affiliate'                       => 'amazon-affiliate.php',
            'Double Dart Cookie'                     => 'double-dart-cookie.php',
            'External Links Policy'                  => 'external-links.php',
            'Affiliate Disclosure'                   => 'affiliate-disclosure.php',
            'FB Policy'                              => 'fbpolicy.php',
            'About Us'                               => 'about-us.php',
            'Digital Goods Refund Policy'            => 'digital-goods-refund-policy.php',
            "COPPA - Children's Online Privacy Policy" => 'coppa.php',
            'GDPR Cookie Policy'                     => 'gdpr-cookie-policy.php',
            'GDPR Privacy Policy'                    => 'gdpr-privacy-policy.php',
        );
    }

    /**
     * Get template name from filename or vice versa
     *
     * @param string $search Search value (filename or template name)
     * @param bool $reverse If true, search by template name to get filename
     * @return string|false Template name/filename or false if not found
     */
    private static function get_mapped_template( $search, $reverse = false ) {
        $mapping = self::get_template_name_mapping();
        
        if ( $reverse ) {
            // Search by filename to get template name
            $result = array_search( $search, $mapping );
            return $result !== false ? $result : false;
        } else {
            // Search by template name to get filename
            return isset( $mapping[ $search ] ) ? $mapping[ $search ] : false;
        }
    }

    /**
     * Insert free templates
     *
     * @param string $table_name Table name
     * @return void
     */
    private static function insert_free_templates( $table_name ) {
        global $wpdb;

        // Skip if already inserted
        if ( get_option( 'adl_free_templates_inserted' ) && ! get_option( 'adl_templates_migrated' ) ) {
            return;
        }

        $free_templates_dir = LEGAL_PAGES_PLUGIN_DIR . 'views/templates/free/';

        if ( ! is_dir( $free_templates_dir ) ) {
            return;
        }

        $free_template_files = scandir( $free_templates_dir );
        $template_mapping = self::get_template_name_mapping();

        foreach ( $free_template_files as $file ) {
            if ( $file === '.' || $file === '..' || pathinfo( $file, PATHINFO_EXTENSION ) !== 'php' ) {
                continue;
            }

            // Try to get the proper template name from mapping
            $template_name = self::get_mapped_template( $file, true );
            if ( ! $template_name ) {
                continue;
            }

            // Check if template already exists by name
            $existing_template = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE name = %s",
                    $template_name
                )
            );

            if ( $existing_template ) {
                continue;
            }

            // Get template content
            $template_content = Utility::get_template( 'templates/free/' . $file );

            if ( empty( $template_content ) ) {
                continue;
            }

            // Insert template
            $wpdb->insert(
                $table_name,
                array(
                    'name'    => $template_name,
                    'content' => $template_content,
                    'type'    => 'free'
                ),
                array( '%s', '%s', '%s' )
            );
        }

        update_option( 'adl_free_templates_inserted', true );
    }

    /**
     * Insert pro templates
     *
     * @param string $table_name Table name
     * @return void
     */
    private static function insert_pro_templates( $table_name ) {
        global $wpdb;

        // Skip if already inserted
        if ( get_option( 'adl_pro_templates_inserted' ) ) {
            return;
        }

        $pro_templates_dir = LEGAL_PAGES_PLUGIN_DIR . 'views/templates/pro/';

        if ( ! is_dir( $pro_templates_dir ) ) {
            return;
        }

        $pro_template_files = scandir( $pro_templates_dir );
        // $template_mapping = self::get_template_name_mapping();

        foreach ( $pro_template_files as $file ) {
            if ( $file === '.' || $file === '..' || pathinfo( $file, PATHINFO_EXTENSION ) !== 'php' ) {
                continue;
            }

            // Try to get the proper template name from mapping
            $template_name = self::get_mapped_template( $file, true );

            if ( ! $template_name ) {
                continue;
            }

            // Check if template already exists by name
            $existing_template = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE name = %s",
                    $template_name
                )
            );

            if ( $existing_template ) {
                continue;
            }

            // Get template content
            $template_content = Utility::get_template( 'templates/pro/' . $file );

            if ( empty( $template_content ) ) {
                continue;
            }

            // Insert template
            $wpdb->insert(
                $table_name,
                array(
                    'name'    => $template_name,
                    'content' => $template_content,
                    'type'    => 'pro'
                ),
                array( '%s', '%s', '%s' )
            );
        }

        update_option( 'adl_pro_templates_inserted', true );
    }

    /**
     * Create template database table
     *
     * @param string $table_name Table name
     * @return void
     */
    private static function create_template_table( $table_name ) {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id int(11) unsigned NOT NULL AUTO_INCREMENT,
            name text COLLATE utf8mb4_unicode_ci NOT NULL,
            content longtext COLLATE utf8mb4_unicode_ci NOT NULL,
            type varchar(50) DEFAULT '',
            PRIMARY KEY  (id)
        ) ENGINE=InnoDB {$charset_collate};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}