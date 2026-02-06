<?php

if ( ! class_exists( 'ADL_LP_enqueue' ) ) :

class ADL_LP_enqueue {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 999 );
    }

    public function admin_enqueue_scripts( $page ) {
        global $ADL_LP;

        /* =====================
         * NOTICE CSS (GLOBAL)
         * ===================== */
        if ( ! wp_style_is( 'adl-notice', 'enqueued' ) ) {
            wp_enqueue_style(
                'adl-notice',
                ADL_LP_ADMIN_ASSETS . 'css/notice.css',
                [],
                ADL_LP_VERSION
            );
        }

        /* =====================
         * LOAD ONLY ON PLUGIN PAGES
         * ===================== */
        $plugin_pages = [
            'adl-legal-pages',
            'all-legal-Pages',
            'legal-page-template',
            'legal-page-support',
            'adl-lp-upgrade',
            'createLegalPage',
        ];

        if ( empty( $_GET['page'] ) || ! in_array( $_GET['page'], $plugin_pages, true ) ) {
            return;
        }

        /* =====================
         * STYLES
         * ===================== */
        wp_enqueue_style( 'adl-lp-bootstrap', ADL_LP_ADMIN_ASSETS . 'css/bootstrap.min.css', [], ADL_LP_VERSION );
        wp_enqueue_style( 'adl-tabs', ADL_LP_ADMIN_ASSETS . 'css/tabs.css', [ 'adl-lp-bootstrap' ], ADL_LP_VERSION );
        wp_enqueue_style( 'adl-main', ADL_LP_ADMIN_ASSETS . 'css/adl-lp-main.css', [ 'adl-lp-bootstrap', 'adl-tabs' ], ADL_LP_VERSION );
        wp_enqueue_style( 'adl-style', ADL_LP_ADMIN_ASSETS . 'css/style.css', [ 'adl-lp-bootstrap', 'adl-tabs' ], ADL_LP_VERSION );

        // Toastr CSS
        wp_enqueue_style(
            'toastr-css',
            ADL_LP_ADMIN_ASSETS . 'css/toastr.css',
            [],
            ADL_LP_VERSION
        );

        /* =====================
         * SCRIPTS
         * ===================== */
        wp_enqueue_script(
            'adl-bootstrap-js',
            ADL_LP_ADMIN_ASSETS . 'js/bootstrap.min.js',
            [ 'jquery' ],
            ADL_LP_VERSION,
            true
        );

        // Toastr JS
        wp_enqueue_script(
            'toastr-js',
            ADL_LP_ADMIN_ASSETS . 'js/toastr.min.js',
            [ 'jquery' ],
            ADL_LP_VERSION,
            true
        );

        // Main plugin JS (depends on Toastr)
        wp_enqueue_script(
            'adl-lp-main-js',
            ADL_LP_ADMIN_ASSETS . 'js/adl-lp-main.js',
            [ 'jquery', 'adl-bootstrap-js', 'toastr-js' ],
            ADL_LP_VERSION,
            true
        );

        /* =====================
         * LOCALIZE SCRIPT
         * ===================== */
        wp_localize_script(
            'adl-lp-main-js',
            'adl_lp_obj',
            [
                'nonceAction' => $ADL_LP->nonceAction(),
                'nonce'       => wp_create_nonce( $ADL_LP->nonceText() ),
                'adminAsset'  => ADL_LP_ADMIN_ASSETS,
            ]
        );

        wp_enqueue_media();
    }
}

endif;
