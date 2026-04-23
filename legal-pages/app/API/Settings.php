<?php
namespace LegalPage\API;

use WP_Error;
use WP_REST_Response;
use LegalPage\Models\Settings as SettingsModel;

defined( 'ABSPATH' ) || exit;

class Settings {

    public function check_permissions( $request ) {
        return current_user_can( 'manage_options' );
    }

    public function accept_disclaimer( $request ) {

        $updated = SettingsModel::accept_disclaimer();

        if ( $updated !== false ) {
            
            return new WP_REST_Response( [
                'success'   => true,
                'message'   => 'Disclaimer accepted successfully',
                'data'      => [
                    'accepted'  => true,
                    'timestamp' => current_time( 'mysql' )
                ]
            ], 200 );
        }

        return new WP_Error( 'failed_to_save', 'Failed to save disclaimer acceptance', [ 'status' => 500 ] );
    }

    public function save_settings( \WP_REST_Request $request ) {
        $params = $request->get_json_params();

        if ( empty( $params ) || ! is_array( $params ) ) {
            return new WP_Error( 'no_data', __( 'No data provided.', 'legal-pages' ), [ 'status' => 400 ] );
        }

        $updated = SettingsModel::save_general_settings( $params );

        if ( ! $updated ) {
            return new WP_Error( 'save_failed', __( 'Failed to save settings.', 'legal-pages' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Settings saved successfully.', 'legal-pages' ),
            'data'    => SettingsModel::get_general_settings(),
        ], 200 );
    }

    public function get_settings( \WP_REST_Request $request ) {
        return new WP_REST_Response( [
            'success' => true,
            'data'    => SettingsModel::get_general_settings(),
        ], 200 );
    }

    public function reset_settings( \WP_REST_Request $request ) {
        $deleted = SettingsModel::reset_general_settings();

        if ( ! $deleted ) {
            return new WP_Error( 'delete_failed', __( 'Failed to reset saved data.', 'legal-pages' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Saved data reset successfully.', 'legal-pages' ),
        ], 200 );
    }

    // ────────────────────────────────────────────────
    // Popup settings
    // ────────────────────────────────────────────────

    public function save_popup_settings( \WP_REST_Request $request ) {
        $params = $request->get_json_params();

        if ( empty( $params ) || ! is_array( $params ) ) {
            return new WP_Error( 'no_data', __( 'No data provided.', 'legal-pages' ), [ 'status' => 400 ] );
        }

        $updated = SettingsModel::save_popup_settings( $params );

        if ( ! $updated ) {
            return new WP_Error( 'save_failed', __( 'Failed to save popup settings.', 'legal-pages' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Popup settings saved successfully.', 'legal-pages' ),
            'data'    => SettingsModel::get_popup_settings(),
        ], 200 );
    }

    public function get_popup_settings( \WP_REST_Request $request ) {
        return new WP_REST_Response( [
            'success' => true,
            'data'    => SettingsModel::get_popup_settings(),
        ], 200 );
    }

    // ────────────────────────────────────────────────
    // Miscellaneous settings
    // ────────────────────────────────────────────────

    public function save_miscellaneous_settings( \WP_REST_Request $request ) {
        $params = $request->get_json_params();

        if ( empty( $params ) || ! is_array( $params ) ) {
            return new WP_Error( 'no_data', __( 'No data provided.', 'legal-pages' ), [ 'status' => 400 ] );
        }

        $updated = SettingsModel::save_miscellaneous_settings( $params );

        if ( ! $updated ) {
            return new WP_Error( 'save_failed', __( 'Failed to save miscellaneous settings.', 'legal-pages' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Miscellaneous settings saved successfully.', 'legal-pages' ),
            'data'    => SettingsModel::get_miscellaneous_settings(),
        ], 200 );
    }

    public function get_miscellaneous_settings( \WP_REST_Request $request ) {
        return new WP_REST_Response( [
            'success' => true,
            'data'    => SettingsModel::get_miscellaneous_settings(),
        ], 200 );
    }

    // ────────────────────────────────────────────────
    // Templates & shortcodes (read-only)
    // ────────────────────────────────────────────────

    public function get_templates( \WP_REST_Request $request ) {
        $simple   = $request->get_param( 'simple' ) ?: false;
        $page     = $request->get_param( 'page' ) ?: 1;
        $per_page = $request->get_param( 'per_page' ) ?: 10;
        $type     = $request->get_param( 'type' ) ?: 'all';
        $search   = $request->get_param( 'search' ) ?: '';

        // If simple format is explicitly requested, return grouped templates (original behavior)
        if ( $simple ) {
            return new WP_REST_Response( [
                'success' => true,
                'data'    => SettingsModel::get_templates(),
            ], 200 );
        }

        // Otherwise, return paginated templates (new behavior)
        $result = SettingsModel::get_all_templates( $page, $per_page, $type, $search );

        return new WP_REST_Response( [
            'success' => true,
            'data'    => $result,
        ], 200 );
    }

    public function get_template_by_id( \WP_REST_Request $request ) {
        $template_id = $request->get_param( 'id' );

        $template = SettingsModel::get_template_by_id( $template_id );

        if ( ! $template ) {
            return new WP_Error( 'template_not_found', __( 'Template not found.', 'legal-pages' ), [ 'status' => 404 ] );
        }

        return new WP_REST_Response( [
            'success' => true,
            'data'    => $template,
        ], 200 );
    }

    public function get_shortcodes( \WP_REST_Request $request ) {
        return new WP_REST_Response( [
            'success' => true,
            'data'    => SettingsModel::get_shortcodes(),
        ], 200 );
    }

    // ────────────────────────────────────────────────
    // Legal page creation
    // ────────────────────────────────────────────────

     public function save_legal_page( \WP_REST_Request $request ) {
        $params = $request->get_json_params();

        $result = SettingsModel::save_legal_page( $params );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Legal page created successfully.', 'legal-pages' ),
            'data'    => $result,
        ], 200 );
    }

    public function get_legal_pages( \WP_REST_Request $request ) {
        $page     = $request->get_param( 'page' ) ?: 1;
        $per_page = $request->get_param( 'per_page' ) ?: 10;
        $status   = $request->get_param( 'status' ) ?: 'all';
        $search   = $request->get_param( 'search' ) ?: '';

        $result = SettingsModel::get_legal_pages( $page, $per_page, $status, $search );

        return new WP_REST_Response( [
            'success' => true,
            'data'    => $result,
        ], 200 );
    }

     /**
     * Delete a legal page
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function delete_legal_page( \WP_REST_Request $request ) {
        $page_id = $request->get_param( 'id' );

        $result = SettingsModel::delete_legal_page( $page_id );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Legal page deleted successfully.', 'legal-pages' ),
        ], 200 );
    }

    /**
     * Get templates with pagination
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_all_templates( \WP_REST_Request $request ) {
        $page     = $request->get_param( 'page' ) ?: 1;
        $per_page = $request->get_param( 'per_page' ) ?: 10;
        $type     = $request->get_param( 'type' ) ?: 'all';
        $search   = $request->get_param( 'search' ) ?: '';

        $result = SettingsModel::get_all_templates( $page, $per_page, $type, $search );

        return new WP_REST_Response( [
            'success' => true,
            'data'    => $result,
        ], 200 );
    }

    /**
     * Create template
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function create_template( \WP_REST_Request $request ) {
        $params = $request->get_json_params();

        if ( empty( $params['name'] ) ) {
            return new WP_Error( 'missing_name', __( 'Template name is required.', 'legal-pages' ), [ 'status' => 400 ] );
        }

        if ( empty( $params['content'] ) ) {
            return new WP_Error( 'missing_content', __( 'Template content is required.', 'legal-pages' ), [ 'status' => 400 ] );
        }

        $result = SettingsModel::create_template( $params );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Template created successfully.', 'legal-pages' ),
            'data'    => $result,
        ], 200 );
    }

    /**
     * Update template
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function update_template( \WP_REST_Request $request ) {
        $template_id = $request->get_param( 'id' );
        $params      = $request->get_json_params();

        if ( empty( $params['content'] ) ) {
            return new WP_Error( 'missing_content', __( 'Template content is required.', 'legal-pages' ), [ 'status' => 400 ] );
        }

        $result = SettingsModel::update_template( $template_id, $params['content'] );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Template updated successfully.', 'legal-pages' ),
            'data'    => $result,
        ], 200 );
    }

    /**
     * Delete template
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function delete_template( \WP_REST_Request $request ) {
        $template_id = $request->get_param( 'id' );

        if ( ! $template_id ) {
            return new WP_Error( 'missing_id', __( 'Template ID is required.', 'legal-pages' ), [ 'status' => 400 ] );
        }

        $result = SettingsModel::delete_template( $template_id );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Template deleted successfully.', 'legal-pages' ),
        ], 200 );
    }

    public function get_item_schema() {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'disclaimer',
            'type'       => 'object',
            'properties' => [
                'accepted'  => [ 'type' => 'boolean', 'readonly' => true ],
                'timestamp' => [ 'type' => 'string',  'readonly' => true ],
            ],
        ];
    }
}