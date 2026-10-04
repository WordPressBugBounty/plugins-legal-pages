<?php
namespace LegalPage\Controllers\Admin;

use LegalPage\Traits\Hook;

defined( 'ABSPATH' ) || exit;

class Menu {
    use Hook;

    /**
     * Icon URL base path.
     *
     * @var string
     */
    private $icon_url;

    public function __construct() {
        $this->icon_url = LEGAL_PAGES_URL . 'assets/images/icons/';
        $this->action( 'admin_menu', [ $this, 'register_menus' ] );
        $this->action( 'admin_init', [ $this, 'maybe_redirect_to_setup_wizard' ] );
        $this->filter( 'plugin_action_links_' . plugin_basename( LEGAL_PAGES_FILE ), [ $this, 'add_plugin_action_links' ] );
    }

    /**
     * Redirect to the Setup Wizard once, right after a fresh install.
     * `Core\Activator::activate()` sets the transient this checks.
     *
     * @return void
     */
    public function maybe_redirect_to_setup_wizard() {
        if ( ! get_transient( 'adl_lp_do_setup_wizard_redirect' ) ) {
            return;
        }

        delete_transient( 'adl_lp_do_setup_wizard_redirect' );

        if ( wp_doing_ajax() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=adl-legal-pages#/setup-wizard' ) );
        exit;
    }

    /**
     * Prepend "Setup Wizard" and (when unlicensed) "Upgrade to Pro" links to the
     * plugin's action links on the Plugins list page (next to Activate/Deactivate/Delete).
     *
     * @param string[] $links
     * @return string[]
     */
    public function add_plugin_action_links( $links ) {
        $license_status = get_option( 'wplp_license_status', '' );

        if ( $license_status !== 'valid' ) {
            $upgrade_link = sprintf(
                '<a href="%1$s" target="_blank" rel="noopener noreferrer" style="color: #d54e21; font-weight: 600;">%2$s</a>',
                esc_url( 'https://legalpagespro.com/?utm_source=lite-plugin#pricing' ),
                esc_html__( 'Upgrade to Pro', 'legal-pages' )
            );

            array_unshift( $links, $upgrade_link );
        }

        $wizard_link = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( admin_url( 'admin.php?page=adl-legal-pages#/setup-wizard' ) ),
            esc_html__( 'Setup Wizard', 'legal-pages' )
        );

        array_unshift( $links, $wizard_link );

        return $links;
    }

    public function register_menus() {

        add_menu_page(
            __( 'Legal Pages', 'legal-pages' ),
            __( 'Legal Pages', 'legal-pages' ),
            'manage_options',
            'adl-legal-pages',
            [ $this, 'render_main_page' ],
            $this->icon_url . 'legal-page.png',
            30
        );

        add_submenu_page(
            'adl-legal-pages',
            __( 'Settings', 'legal-pages' ),
            __( 'Settings', 'legal-pages' ),
            'manage_options',
            'adl-legal-pages',
            [ $this, 'render_main_page' ]
        );

        add_submenu_page(
            'adl-legal-pages',
            __( 'Add New Legal Page', 'legal-pages' ),
            __( 'Add New Legal Page', 'legal-pages' ),
            'manage_options',
            'adl-legal-pages#/add-new',
            [ $this, 'render_main_page' ]
        );

        add_submenu_page(
            'adl-legal-pages',
            __( 'All Legal Pages', 'legal-pages' ),
            __( 'All Legal Pages', 'legal-pages' ),
            'manage_options',
            'adl-legal-pages#/all-legal-pages',
            [ $this, 'render_main_page' ]
        );

        add_submenu_page(
            'adl-legal-pages',
            __( 'Legal Page Templates', 'legal-pages' ),
            __( 'Legal Page Templates', 'legal-pages' ),
            'manage_options',
            'adl-legal-pages#/legal-page-templates',
            [ $this, 'render_main_page' ]
        );

        $license_status = get_option( 'wplp_license_status', '' );

        if ( $license_status !== 'valid' ) {
            add_submenu_page(
                'adl-legal-pages',
                __( 'PRO FEATURES', 'legal-pages' ),
                __( 'PRO FEATURES', 'legal-pages' ),
                'manage_options',
                'adl-legal-pages#/pro-features',
                [ $this, 'render_main_page' ]
            );

            add_submenu_page(
                'adl-legal-pages',
                __( 'All Popups', 'legal-pages' ),
                __( 'All Popups', 'legal-pages' ),
                'manage_options',
                'adl-legal-pages#/pro-features/all-popups',
                [ $this, 'render_main_page' ]
            );

            add_submenu_page(
                'adl-legal-pages',
                __( 'Cookie Bar', 'legal-pages' ),
                __( 'Cookie Bar', 'legal-pages' ),
                'manage_options',
                'adl-legal-pages#/pro-features/cookie-bar',
                [ $this, 'render_main_page' ]
            );
        }

        do_action( 'lp_register_pro_submenus', $this );

        // Help & Support
        add_submenu_page(
            'adl-legal-pages',
            __( 'Help & Support', 'legal-pages' ),
            __( 'Help & Support', 'legal-pages' ),
            'manage_options',
            'adl-legal-pages#/help-support',
            [ $this, 'render_main_page' ]
        );

        if ( $license_status !== 'valid' ) {
            add_submenu_page(
                'adl-legal-pages',
                __( 'Upgrade to Pro', 'legal-pages' ),
                __( 'Upgrade to Pro', 'legal-pages' ),
                'manage_options',
                'adl-legal-pages#/upgrade-to-pro',
                [ $this, 'render_main_page' ]
            );
        }
        
        do_action( 'lp_register_pro_last_submenus', $this );
    }

    public function render_main_page() {
        echo '<div class="wrap"><div id="adl-legal-pages">Loading...</div></div>';
    }
}