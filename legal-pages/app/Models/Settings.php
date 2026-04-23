<?php
namespace LegalPage\Models;

defined( 'ABSPATH' ) || exit;

class Settings {

    // ────────────────────────────────────────────────
    // Templates
    // ────────────────────────────────────────────────

    public static function get_templates() {
        global $wpdb;
        $table = $wpdb->prefix . 'adl_lp_templates';

        $results = $wpdb->get_results(
            "SELECT id, name, content, type FROM $table ORDER BY type ASC, name ASC",
            ARRAY_A
        );

        $templates = $results ?: [];

        $free = array_values( array_filter( $templates, fn($t) => $t['type'] === 'free' ) );
        $pro  = array_values( array_filter( $templates, fn($t) => $t['type'] === 'pro'  ) );

        return compact( 'free', 'pro' );
    }

    public static function get_template_by_id( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'adl_lp_templates';

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT id, name, content, type FROM $table WHERE id = %d", $id ),
            ARRAY_A
        ) ?: null;
    }

    // ────────────────────────────────────────────────
    // Legal page creation
    // ────────────────────────────────────────────────

    public static function save_legal_page( $params ) {
        if ( empty( $params['title'] ) ) {
            return new \WP_Error(
                'missing_title',
                __( 'Page title is required.', 'legal-pages' ),
                [ 'status' => 400 ]
            );
        }

        if ( empty( $params['content'] ) ) {
            return new \WP_Error(
                'missing_content',
                __( 'Page content is required.', 'legal-pages' ),
                [ 'status' => 400 ]
            );
        }

        $args = [
            'post_title'   => sanitize_text_field( $params['title'] ),
            'post_content' => wp_kses_post( $params['content'] ),
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'meta_input'   => [
                'is_adl_legal_page' => 1,
            ],
        ];

        if ( ! empty( $params['id'] ) ) {
            $args['ID'] = (int) $params['id'];
        }

        $page_id = wp_insert_post( $args, true );

        if ( is_wp_error( $page_id ) ) {
            return new \WP_Error(
                'save_failed',
                __( 'Failed to save legal page.', 'legal-pages' ),
                [ 'status' => 500 ]
            );
        }

        $subtitle = ! empty( $params['subtitle'] ) ? sanitize_text_field( $params['subtitle'] ) : 'Legal Page - ' . $args['post_title'];

        update_post_meta( $page_id, '_legal_page_subtitle', $subtitle );

        return [
            'id'        => $page_id,
            'edit_url'  => get_edit_post_link( $page_id, 'raw' ),
            'view_url'  => get_permalink( $page_id ),
            'title'     => get_the_title( $page_id ),
            'subtitle'  => get_post_meta( $page_id, '_legal_page_subtitle', true ),
        ];
    }


    // ────────────────────────────────────────────────
    // General settings
    // ────────────────────────────────────────────────

    public static function get_general_settings() {
        $defaults = [
            'site_url'         => get_site_url(),
            'site_name'        => '',
            'business_niche'   => '',
            'phone_number'     => '',
            'email_address'    => '',
            'street_name'      => '',
            'city'             => '',
            'state'            => '',
            'country_name'     => '',
            'zip_code'         => '',
            'complete_address' => '',
            'facebook_url'     => '',
            'google_plus_url'  => '',
            'linkedin_url'     => '',
            'twitter_url'      => '',
        ];

        $saved  = get_option( 'adl_lp_general', [] );
        $social = get_option( 'adl_lp_social', [] );

        if ( ! is_array( $saved ) ) {
            $saved = [];
        }

        if ( ! is_array( $social ) ) {
            $social = [];
        }

        $mapped = [
            'site_url'         => $saved['site_url'] ?? $saved['siteUrl'] ?? '',
            'site_name'        => $saved['site_name'] ?? $saved['business_name'] ?? $saved['siteName'] ?? '',
            'business_niche'   => $saved['business_niche'] ?? $saved['businessNiche'] ?? '',
            'phone_number'     => $saved['phone_number'] ?? $saved['phoneNumber'] ?? '',
            'email_address'    => $saved['email_address'] ?? $saved['emailAddress'] ?? '',
            'street_name'      => $saved['street_name'] ?? $saved['streetName'] ?? '',
            'city'             => $saved['city'] ?? $saved['cityName'] ?? '',
            'state'            => $saved['state'] ?? $saved['stateName'] ?? '',
            'country_name'     => $saved['country_name'] ?? $saved['countryName'] ?? '',
            'zip_code'         => $saved['zip_code'] ?? $saved['zipCode'] ?? '',
            'complete_address' => $saved['complete_address'] ?? $saved['mailingAddress'] ?? '',
        ];

        $mapped['facebook_url'] = $saved['facebook_url']
            ?? $social['facebookUrl']
            ?? '';

        $mapped['google_plus_url'] = $saved['google_plus_url']
            ?? $social['googlePlusUrl']
            ?? '';

        $mapped['linkedin_url'] = $saved['linkedin_url']
            ?? $social['linkedInUrl']
            ?? '';

        $mapped['twitter_url'] = $saved['twitter_url']
            ?? $social['twitterUrl']
            ?? '';

        $mapped['site_url']  = ! empty( $mapped['site_url'] )  ? $mapped['site_url']  : get_site_url();
        $mapped['site_name'] = ! empty( $mapped['site_name'] ) ? $mapped['site_name'] : get_bloginfo( 'name' );

        return wp_parse_args( $mapped, $defaults );
    }

    public static function save_general_settings( $data ) {
        $fields = [
            'site_url'         => 'esc_url_raw',
            'site_name'        => 'sanitize_text_field',
            'business_niche'   => 'sanitize_text_field',
            'phone_number'     => 'sanitize_text_field',
            'email_address'    => 'sanitize_email',
            'street_name'      => 'sanitize_text_field',
            'city'             => 'sanitize_text_field',
            'state'            => 'sanitize_text_field',
            'country_name'     => 'sanitize_text_field',
            'zip_code'         => 'sanitize_text_field',
            'complete_address' => 'sanitize_textarea_field',
            'facebook_url'     => 'esc_url_raw',
            'google_plus_url'  => 'esc_url_raw',
            'linkedin_url'     => 'esc_url_raw',
            'twitter_url'      => 'esc_url_raw',
        ];

        $sanitized = [];

        foreach ( $fields as $key => $sanitizer ) {
            $sanitized[ $key ] = isset( $data[ $key ] ) ? call_user_func( $sanitizer, $data[ $key ] ) : '';
        }

        return update_option( 'adl_lp_general', $sanitized, false );
    }

    public static function reset_general_settings() {
        return delete_option( 'adl_lp_general' );
    }

    /**
     * Get popup settings
     *
     * @return array Popup settings
     */
    public static function get_popup_settings() {
    $defaults = [
        'disable_popup_title'   => false,
        'popup_notice_title'    => '',
        'agreement_text'        => '',
        'accept_button_text'    => '',
        'popup_width'           => '',
        'popup_height'          => '',
        'stop_showing_popup'    => false,
        'disable_popup_js_css'  => false,
    ];

    $settings = get_option( 'adl_lp_popup', [] );

    if ( ! is_array( $settings ) ) {
        $settings = [];
    }

    $width = $settings['popup_width'] ?? '';
    if ( is_string( $width ) ) {
        if ( strpos( $width, '%' ) !== false ) {
            $width = (int) $width;
            $width = ($width / 100) * 1200;
        } elseif ( strpos( $width, 'vh' ) !== false || strpos( $width, 'vw' ) !== false ) {
            $width = 600;
        }
    }

    $height = $settings['popup_height'] ?? '';
    if ( is_string( $height ) ) {
        if ( strpos( $height, 'vh' ) !== false ) {
            $height = (int) $height;
            $height = ( $height / 100 ) * 800;
        } elseif ( strpos( $height, '%' ) !== false ) {
            $height = (int) $height;
            $height = ( $height / 100 ) * 800;
        }
    }

    $mapped = [
        'disable_popup_title'  => $settings['disable_popup_title'] ?? $settings['disabled_pop_notice_title'] ?? false,
        'popup_notice_title'   => $settings['popup_notice_title'] ?? '',
        'agreement_text'       => $settings['agreement_text'] ?? '',
        'accept_button_text'   => $settings['accept_button_text'] ?? $settings['accept_btn_text'] ?? '',
        'popup_width'          => is_numeric($width) ? (int) $width : '',
        'popup_height'         => is_numeric($height) ? (int) $height : '',
        'stop_showing_popup'   => $settings['stop_showing_popup'] ?? $settings['user_can_close_popup'] ?? false,
        'disable_popup_js_css' => $settings['disable_popup_js_css'] ?? $settings['disabled_pop_js_css'] ?? false,
    ];

    return wp_parse_args( $mapped, $defaults );
}

    /**
     * Save popup settings
     *
     * @param array $params Popup settings parameters
     * @return bool True if updated, false otherwise
     */
    public static function save_popup_settings( $params ) {
        if ( empty( $params ) || ! is_array( $params ) ) {
            return false;
        }

        $defaults = [
            'disable_popup_title'   => false,
            'popup_notice_title'    => 'We value your privacy',
            'agreement_text'        => 'We use cookies to improve your experience.',
            'accept_button_text'    => 'Accept',
            'popup_width'           => 800,
            'popup_height'          => 600,
            'stop_showing_popup'    => false,
            'disable_popup_js_css'  => false,
        ];

        $fields = [
            'disable_popup_title'   => 'rest_sanitize_boolean',
            'popup_notice_title'    => 'sanitize_text_field',
            'agreement_text'        => 'sanitize_textarea_field',
            'accept_button_text'    => 'sanitize_text_field',
            'popup_width'           => 'sanitize_text_field',
            'popup_height'          => 'sanitize_text_field',
            'stop_showing_popup'    => 'rest_sanitize_boolean',
            'disable_popup_js_css'  => 'rest_sanitize_boolean',
        ];

        $sanitized = [];

        foreach ( $fields as $key => $callback ) {
            if ( isset( $params[ $key ] ) ) {
                $value = call_user_func( $callback, $params[ $key ] );

                if ( $value === '' || $value === null ) {
                    $sanitized[ $key ] = $defaults[ $key ];
                } else {
                    $sanitized[ $key ] = $value;
                }

            } else {
                $sanitized[ $key ] = $defaults[ $key ];
            }
        }

        return update_option( 'adl_lp_popup', $sanitized, false );
    }

    /**
     * Get miscellaneous settings
     *
     * @return array Miscellaneous settings
     */
    public static function get_miscellaneous_settings() {
        $defaults = array(
            'hide_lp_in_search'  => false,
            'delete_adl_lp_data'   => false,
        );

        $settings = get_option( 'adl_lp_misc', array() );

        if ( ! is_array( $settings ) ) {
            $settings = array();
        }

        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Save miscellaneous settings
     *
     * @param array $params Miscellaneous settings parameters
     * @return bool True if updated, false otherwise
     */
    public static function save_miscellaneous_settings( $params ) {
        if ( empty( $params ) || ! is_array( $params ) ) {
            return false;
        }

        $fields = array(
            'hide_lp_in_search'     => 'rest_sanitize_boolean',
            'delete_adl_lp_data'    => 'rest_sanitize_boolean',
        );

        $sanitized = array();

        foreach ( $fields as $key => $callback ) {
            if ( isset( $params[ $key ] ) ) {
                $sanitized[ $key ] = call_user_func( $callback, $params[ $key ] );
            } else {
                $sanitized[ $key ] = false;
            }
        }

        return update_option( 'adl_lp_misc', $sanitized, false );
    }

    /**
     * Get shortcodes
     *
     * @return array Shortcodes data
     */
    public static function get_shortcodes() {
        $settings = get_option( 'adl_lp_general', array() );

        return array(
            'siteUrl'                 => ! empty( $settings['site_url'] ) ? $settings['site_url'] : '',
            'siteName'                => ! empty( $settings['site_name'] ) ? $settings['site_name'] : '',
            'businessNiche'           => ! empty( $settings['business_niche'] ) ? $settings['business_niche'] : '',
            'phoneNumber'             => ! empty( $settings['phone_number'] ) ? $settings['phone_number'] : '',
            'emailAddress'            => ! empty( $settings['email_address'] ) ? $settings['email_address'] : '',
            'streetName'              => ! empty( $settings['street_name'] ) ? $settings['street_name'] : '',
            'citynNme'                => ! empty( $settings['city'] ) ? $settings['city'] : '',
            'stateName'               => ! empty( $settings['state'] ) ? $settings['state'] : '',
            'countryName'             => ! empty( $settings['country_name'] ) ? $settings['country_name'] : '',
            'zipCode'                 => ! empty( $settings['zip_code'] ) ? $settings['zip_code'] : '',
            'mailingAddress'          => ! empty( $settings['complete_address'] ) ? $settings['complete_address'] : '',
            'facebookUrl'             => ! empty( $settings['facebook_url'] ) ? $settings['facebook_url'] : '',
        );
    }

    /**
     * Accept disclaimer
     *
     * @return bool True if updated, false otherwise
     */
    public static function accept_disclaimer() {
        return update_option( 'adl_lp_accept_term', '1' );
    }

    // ────────────────────────────────────────────────
    // NEW: Legal Pages Listing & Management
    // ────────────────────────────────────────────────

    /**
     * Get all legal pages with filters
     *
     * @param int    $page     Current page number
     * @param int    $per_page Items per page
     * @param string $status   Filter by status (all, publish, draft)
     * @param string $search   Search query
     * @return array
     */
    public static function get_legal_pages( $page = 1, $per_page = 10, $status = 'all', $search = '' ) {
        // Base query args
        $args = [
            'post_type'      => 'page',
            'posts_per_page' => (int) $per_page,
            'paged'          => (int) $page,
            'meta_query'     => [
                [
                    'key'     => 'is_adl_legal_page',
                    'value'   => '1',
                    'compare' => '='
                ]
            ],
            'orderby'        => 'date',
            'order'          => 'DESC'
        ];

        // Filter by status
        if ( $status !== 'all' ) {
            $args['post_status'] = sanitize_key( $status );
        } else {
            $args['post_status'] = [ 'publish', 'draft' ];
        }

        // Search filter
        if ( ! empty( $search ) ) {
            $search = sanitize_text_field( $search );
            
            // Check if search is numeric (ID search)
            if ( is_numeric( $search ) ) {
                $args['p'] = (int) $search;
            } else {
                $args['s'] = $search;
            }
        }

        // Execute query
        $query = new \WP_Query( $args );

        // Get counts first
        $counts = self::get_legal_pages_counts();

        // If no posts found, return empty result
        if ( ! $query->have_posts() ) {
            return [
                'pages'        => [],
                'total_pages'  => 0,
                'total'        => 0,
                'current_page' => (int) $page,
                'per_page'     => (int) $per_page,
                'counts'       => $counts
            ];
        }

        // Format posts
        $pages = [];
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $pages[] = [
                'id'         => $post_id,
                'title'      => get_the_title(),
                'subtitle'   => get_post_meta( $post_id, '_legal_page_subtitle', true ),
                'author'     => get_the_author(),
                'created_at' => get_the_date( 'Y-m-d H:i' ),
                'shortcode'  => '[wpwax_legal_page id="' . $post_id . '"]',
                'view_url'   => get_permalink( $post_id ),
                'edit_url'   => get_edit_post_link( $post_id, 'raw' ),
                'status'     => get_post_status( $post_id )
            ];
        }
        wp_reset_postdata();

        return [
            'pages'        => $pages,
            'total_pages'  => $query->max_num_pages,
            'total'        => $query->found_posts,
            'current_page' => (int) $page,
            'per_page'     => (int) $per_page,
            'counts'       => $counts
        ];
    }

    /**
     * Get counts for different statuses
     *
     * @return array
     */
    private static function get_legal_pages_counts() {
        global $wpdb;

        // Count all legal pages
        $all_count = $wpdb->get_var(
            "SELECT COUNT(DISTINCT p.ID) 
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'page'
             AND pm.meta_key = 'is_adl_legal_page'
             AND pm.meta_value = '1'
             AND p.post_status IN ('publish', 'draft')"
        );

        // Count published
        $published_count = $wpdb->get_var(
            "SELECT COUNT(DISTINCT p.ID) 
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'page'
             AND pm.meta_key = 'is_adl_legal_page'
             AND pm.meta_value = '1'
             AND p.post_status = 'publish'"
        );

        // Count draft
        $draft_count = $wpdb->get_var(
            "SELECT COUNT(DISTINCT p.ID) 
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'page'
             AND pm.meta_key = 'is_adl_legal_page'
             AND pm.meta_value = '1'
             AND p.post_status = 'draft'"
        );

        return [
            'all'       => (int) $all_count,
            'published' => (int) $published_count,
            'draft'     => (int) $draft_count
        ];
    }

    /**
     * Delete a legal page
     *
     * @param int $page_id Page ID to delete
     * @return bool|\WP_Error
     */
    public static function delete_legal_page( $page_id ) {
        $page_id = (int) $page_id;

        // Verify it's a legal page
        $is_legal_page = get_post_meta( $page_id, 'is_adl_legal_page', true );
        
        if ( $is_legal_page !== '1' ) {
            return new \WP_Error(
                'not_legal_page',
                __( 'This is not a legal page.', 'legal-pages' ),
                [ 'status' => 400 ]
            );
        }

        // Check if post exists
        $post = get_post( $page_id );
        if ( ! $post ) {
            return new \WP_Error(
                'page_not_found',
                __( 'Page not found.', 'legal-pages' ),
                [ 'status' => 404 ]
            );
        }

        // Delete the post (force delete, bypass trash)
        $deleted = wp_delete_post( $page_id, true );

        if ( ! $deleted ) {
            return new \WP_Error(
                'delete_failed',
                __( 'Failed to delete page.', 'legal-pages' ),
                [ 'status' => 500 ]
            );
        }

        return true;
    }

    /**
     * Get templates with pagination and filters
     *
     * @param int    $page     Current page number
     * @param int    $per_page Items per page
     * @param string $type     Filter by type (all, free, pro)
     * @param string $search   Search query
     * @return array
     */
    public static function get_all_templates( $page = 1, $per_page = 10, $type = 'all', $search = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'adl_lp_templates';

        // Build WHERE clause
        $where = array( '1=1' );
        $where_values = array();

        // Filter by type
        if ( $type !== 'all' ) {
            $where[] = 'type = %s';
            $where_values[] = sanitize_key( $type );
        }

        // Search filter
        if ( ! empty( $search ) ) {
            $where[] = 'name LIKE %s';
            $where_values[] = '%' . $wpdb->esc_like( sanitize_text_field( $search ) ) . '%';
        }

        $where_clause = implode( ' AND ', $where );

        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$table} WHERE {$where_clause}";
        if ( ! empty( $where_values ) ) {
            $count_query = $wpdb->prepare( $count_query, $where_values );
        }
        $total = (int) $wpdb->get_var( $count_query );

        // Calculate pagination
        $offset = ( $page - 1 ) * $per_page;
        $total_pages = ceil( $total / $per_page );

        // Get templates
        $templates_query = "SELECT id, name, content, type 
                           FROM {$table} 
                           WHERE {$where_clause} 
                           ORDER BY type ASC, name ASC 
                           LIMIT %d OFFSET %d";
        
        $templates_values = array_merge( $where_values, array( (int) $per_page, (int) $offset ) );
        $templates_query = $wpdb->prepare( $templates_query, $templates_values );
        
        $templates = $wpdb->get_results( $templates_query, ARRAY_A );

        // Get counts for tabs
        $counts = self::get_template_counts();

        return [
            'templates'    => $templates ?: [],
            'total_pages'  => $total_pages,
            'total'        => $total,
            'current_page' => (int) $page,
            'per_page'     => (int) $per_page,
            'counts'       => $counts
        ];
    }

    /**
     * Get template counts by type
     *
     * @return array
     */
    private static function get_template_counts() {
        global $wpdb;
        $table = $wpdb->prefix . 'adl_lp_templates';

        $all_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        
        $free_count = (int) $wpdb->get_var( 
            $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE type = %s", 'free' )
        );
        
        $pro_count = (int) $wpdb->get_var( 
            $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE type = %s", 'pro' )
        );

        return [
            'all'  => $all_count,
            'free' => $free_count,
            'pro'  => $pro_count
        ];
    }

    /**
     * Update template content
     *
     * @param int    $template_id Template ID
     * @param string $content     New content
     * @return array|\WP_Error
     */
    public static function update_template( $template_id, $content ) {
        global $wpdb;
        $table = $wpdb->prefix . 'adl_lp_templates';

        $template_id = (int) $template_id;

        // Check if template exists
        $template = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $template_id ),
            ARRAY_A
        );

        if ( ! $template ) {
            return new \WP_Error(
                'template_not_found',
                __( 'Template not found.', 'legal-pages' ),
                [ 'status' => 404 ]
            );
        }

        // Update template
        $updated = $wpdb->update(
            $table,
            array( 'content' => wp_kses_post( $content ) ),
            array( 'id' => $template_id ),
            array( '%s' ),
            array( '%d' )
        );

        if ( $updated === false ) {
            return new \WP_Error(
                'update_failed',
                __( 'Failed to update template.', 'legal-pages' ),
                [ 'status' => 500 ]
            );
        }

        // Return updated template
        return array(
            'id'      => $template_id,
            'name'    => $template['name'],
            'content' => wp_kses_post( $content ),
            'type'    => $template['type']
        );
    }

    /**
     * Delete template
     *
     * @param int $template_id Template ID
     * @return bool|\WP_Error
     */
    public static function delete_template( $template_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'adl_lp_templates';

        $template_id = (int) $template_id;

        // Check if template exists
        $template = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $template_id ),
            ARRAY_A
        );

        if ( ! $template ) {
            return new \WP_Error(
                'template_not_found',
                __( 'Template not found.', 'legal-pages' ),
                [ 'status' => 404 ]
            );
        }

        // Delete template
        $deleted = $wpdb->delete(
            $table,
            array( 'id' => $template_id ),
            array( '%d' )
        );

        if ( $deleted === false ) {
            return new \WP_Error(
                'delete_failed',
                __( 'Failed to delete template.', 'legal-pages' ),
                [ 'status' => 500 ]
            );
        }

        return true;
    }

    /**
     * Create new template
     *
     * @param array $params Template data
     * @return array|\WP_Error
     */
    public static function create_template( $params ) {
        global $wpdb;
        $table = $wpdb->prefix . 'adl_lp_templates';

        if ( empty( $params['name'] ) ) {
            return new \WP_Error(
                'missing_name',
                __( 'Template name is required.', 'legal-pages' ),
                [ 'status' => 400 ]
            );
        }

        if ( empty( $params['content'] ) ) {
            return new \WP_Error(
                'missing_content',
                __( 'Template content is required.', 'legal-pages' ),
                [ 'status' => 400 ]
            );
        }

        // Sanitize inputs
        $name       = sanitize_text_field( $params['name'] );
        $content    = wp_kses_post( $params['content'] );
        $type       = ! empty( $params['type'] ) ? sanitize_text_field( $params['type'] ) : 'free'; // Default to 'free'

        // Check if template with same name already exists
        $existing_template = $wpdb->get_var(
            $wpdb->prepare( "SELECT id FROM {$table} WHERE name = %s", $name )
        );

        if ( $existing_template ) {
            return new \WP_Error(
                'duplicate_name',
                __( 'A template with this name already exists.', 'legal-pages' ),
                [ 'status' => 400 ]
            );
        }

        // Insert new template
        $inserted = $wpdb->insert(
            $table,
            array(
                'name'    => $name,
                'content' => $content,
                'type'    => $type
            ),
            array( '%s', '%s', '%s' )
        );

        if ( $inserted === false ) {
            return new \WP_Error(
                'insert_failed',
                __( 'Failed to create template.', 'legal-pages' ),
                [ 'status' => 500 ]
            );
        }

        $template_id = $wpdb->insert_id;

        // Return created template
        return array(
            'id'      => $template_id,
            'name'    => $name,
            'content' => $content,
            'type'    => $type
        );
    }
}