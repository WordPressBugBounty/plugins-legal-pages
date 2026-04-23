<?php
namespace LegalPage\Controllers\Common;
use WP_REST_Server;
use LegalPage\Traits\Hook;
use LegalPage\Traits\Rest;
use LegalPage\API\Settings;

defined( 'ABSPATH' ) || exit;

class API {
    use Hook;
    use Rest;

    /**
     * Constructor to initialize hooks
     */
    public function __construct() {
        $this->action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register all routes for the disclaimer endpoint
     */
    public function register_routes() {

        // Accept disclaimer endpoint
        $this->register_route(
            '/accept',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( new Settings(), 'accept_disclaimer' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
                // 'schema' => array( new Settings(), 'get_item_schema' ),
            )
        );

        // All settings endpoint
        $this->register_route(
            '/settings',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( new Settings(), 'get_settings' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        $this->register_route(
            '/settings',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( new Settings(), 'save_settings' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        $this->register_route(
            '/settings/reset',
            array(
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( new Settings(), 'reset_settings' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        // All PRO Popup settings
        $this->register_route(
            '/popup-settings',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( new Settings(), 'get_popup_settings' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        $this->register_route(
            '/popup-settings',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( new Settings(), 'save_popup_settings' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        // All Miscellaneous settings
        $this->register_route(
            '/miscellaneous-settings',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( new Settings(), 'get_miscellaneous_settings' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        $this->register_route(
            '/miscellaneous-settings',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( new Settings(), 'save_miscellaneous_settings' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        // All templates related endpoint - supports both simple and paginated views
        $this->register_route(
            '/templates',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( new Settings(), 'get_templates' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                    'args'                => array(
                        'page' => array(
                            'description'       => __( 'Page number for pagination', 'legal-pages' ),
                            'type'              => 'integer',
                            'default'           => 1,
                            'sanitize_callback' => 'absint',
                        ),
                        'per_page' => array(
                            'description'       => __( 'Number of items per page', 'legal-pages' ),
                            'type'              => 'integer',
                            'default'           => 10,
                            'sanitize_callback' => 'absint',
                        ),
                        'type' => array(
                            'description'       => __( 'Filter by template type', 'legal-pages' ),
                            'type'              => 'string',
                            'default'           => 'all',
                            'enum'              => array( 'all', 'free', 'pro' ),
                            'sanitize_callback' => 'sanitize_key',
                        ),
                        'search' => array(
                            'description'       => __( 'Search by template name', 'legal-pages' ),
                            'type'              => 'string',
                            'default'           => '',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),
                        'simple' => array(
                            'description'       => __( 'Return simple format (grouped by type)', 'legal-pages' ),
                            'type'              => 'boolean',
                            'default'           => false,
                            'sanitize_callback' => 'rest_sanitize_boolean',
                        ),
                    ),
                ),
            )
        );

        $this->register_route(
            '/templates/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( new Settings(), 'get_template_by_id' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        // Create template
        $this->register_route(
            '/template',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( new Settings(), 'create_template' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        $this->register_route(
            '/legal-page',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( new Settings(), 'save_legal_page' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        $this->register_route(
            '/shortcodes',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( new Settings(), 'get_shortcodes' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        // ────────────────────────────────────────────────
        // NEW: Legal Pages Listing Endpoints
        // ────────────────────────────────────────────────

        $this->register_route(
            '/legal-pages',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( new Settings(), 'get_legal_pages' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                    'args'                => array(
                        'page' => array(
                            'description'       => __( 'Page number for pagination', 'legal-pages' ),
                            'type'              => 'integer',
                            'default'           => 1,
                            'sanitize_callback' => 'absint',
                        ),
                        'per_page' => array(
                            'description'       => __( 'Number of items per page', 'legal-pages' ),
                            'type'              => 'integer',
                            'default'           => 10,
                            'sanitize_callback' => 'absint',
                        ),
                        'status' => array(
                            'description'       => __( 'Filter by post status', 'legal-pages' ),
                            'type'              => 'string',
                            'default'           => 'all',
                            'enum'              => array( 'all', 'publish', 'draft' ),
                            'sanitize_callback' => 'sanitize_key',
                        ),
                        'search' => array(
                            'description'       => __( 'Search by ID or title', 'legal-pages' ),
                            'type'              => 'string',
                            'default'           => '',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),
                    ),
                ),
            )
        );

        // Delete legal page
        $this->register_route(
            '/legal-page/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( new Settings(), 'delete_legal_page' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );


        // Update template
        $this->register_route(
            '/template/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( new Settings(), 'update_template' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );

        // Delete template
        $this->register_route(
            '/template/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( new Settings(), 'delete_template' ),
                    'permission_callback' => array( new Settings(), 'check_permissions' ),
                ),
            )
        );
    }
}