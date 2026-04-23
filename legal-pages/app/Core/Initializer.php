<?php
namespace LegalPage\Core;

/**
 * Plugin Initializer Class
 *
 * @package LegalPage
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Initializer {
    
    /**
     * Plugin instance
     *
     * @var Initializer
     */
    private static $instance = null;
    
    /**
     * Get plugin instance (Singleton pattern)
     *
     * @return Initializer
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Constructor is private for singleton
    }
    
    /**
     * Initialize plugin
     * Called from main legal-pages.php file
     *
     * @return void
     */
    public function init() {
        /**
         * Fires before the initialization process starts.
         */
        do_action( 'legal_pages_before_initialize', $this );

        $this->load_textdomain();
        // $this->load_config();
        $this->init_controllers();

        /**
         * Fires after the initialization process is completed.
         */
        do_action( 'legal_pages_after_initialize', $this );
    }
    
    /**
     * Load plugin textdomain for translations
     *
     * @return void
     */
    private function load_textdomain() {
        load_plugin_textdomain(
            'legal-pages',
            false,
            dirname( plugin_basename( LEGAL_PAGES_FILE ) ) . '/languages'
        );
    }

    /**
     * Loads configuration files for the plugin.
     *
     * @return void
     */
    // private function load_config() {
    //     /**
    //      * Fires before loading the configuration files.
    //      */
    //     do_action( 'legal_pages_before_load_config' );

    //     $config_files = glob( LEGAL_PAGES_PLUGIN_DIR . 'app/Config/*.php' );
    //     $config_files = apply_filters( 'legal_pages_config_files', $config_files );

    //     foreach ( $config_files as $config_file ) {
    //         if ( file_exists( $config_file ) ) {
    //             require_once $config_file;
    //         }
    //     }

    //     /**
    //      * Fires after loading the configuration files.
    //      */
    //     do_action( 'legal_pages_after_load_config' );
    // }
    
    /**
     * Initialize all controllers
     *
     * @return void
     */
    private function init_controllers() {
        /**
         * Fires before loading controllers.
         */
        do_action( 'legal_pages_before_load_controllers' );

        // Load Admin Controllers
        if ( is_admin() ) {
            $this->load_admin_controllers();
        }

        // Load Frontend Controllers
        if ( ! is_admin() ) {
            $this->load_frontend_controllers();
        }

        // Load Common Controllers (both admin and frontend)
        $this->load_common_controllers();

        /**
         * Fires after loading controllers.
         */
        do_action( 'legal_pages_after_load_controllers' );
    }

    /**
     * Load admin controllers
     *
     * @return void
     */
    private function load_admin_controllers() {
        /**
         * Fires before loading admin controllers.
         */
        do_action( 'legal_pages_before_load_admin_controllers' );

        $controller_dir = LEGAL_PAGES_PLUGIN_DIR . 'app/Controllers/Admin/';
        
        if ( ! is_dir( $controller_dir ) ) {
            return;
        }

        $controllers = glob( $controller_dir . '*.php' );
        $controllers = apply_filters( 'legal_pages_admin_controllers', $controllers );

        foreach ( $controllers as $file ) {
            $class_name = basename( $file, '.php' );
            $controller = "\\LegalPage\\Controllers\\Admin\\{$class_name}";

            if ( class_exists( $controller ) ) {
                new $controller();
            }
        }

        /**
         * Fires after loading admin controllers.
         */
        do_action( 'legal_pages_after_load_admin_controllers' );
    }

    /**
     * Load frontend controllers
     *
     * @return void
     */
    private function load_frontend_controllers() {
        /**
         * Fires before loading frontend controllers.
         */
        do_action( 'legal_pages_before_load_frontend_controllers' );

        $controller_dir = LEGAL_PAGES_PLUGIN_DIR . 'app/Controllers/Front/';
        
        if ( ! is_dir( $controller_dir ) ) {
            return;
        }

        $controllers = glob( $controller_dir . '*.php' );
        $controllers = apply_filters( 'legal_pages_frontend_controllers', $controllers );

        foreach ( $controllers as $file ) {
            $class_name = basename( $file, '.php' );
            $controller = "\\LegalPage\\Controllers\\Front\\{$class_name}";

            if ( class_exists( $controller ) ) {
                new $controller();
            }
        }

        /**
         * Fires after loading frontend controllers.
         */
        do_action( 'legal_pages_after_load_frontend_controllers' );
    }

    /**
     * Load common controllers (both admin and frontend)
     *
     * @return void
     */
    private function load_common_controllers() {
        /**
         * Fires before loading common controllers.
         */
        do_action( 'legal_pages_before_load_common_controllers' );

        $controller_dir = LEGAL_PAGES_PLUGIN_DIR . 'app/Controllers/Common/';
        
        if ( ! is_dir( $controller_dir ) ) {
            return;
        }

        $controllers = glob( $controller_dir . '*.php' );
        $controllers = apply_filters( 'legal_pages_common_controllers', $controllers );

        foreach ( $controllers as $file ) {
            $class_name = basename( $file, '.php' );
            $controller = "\\LegalPage\\Controllers\\Common\\{$class_name}";

            if ( class_exists( $controller ) ) {
                new $controller();
            }
        }

        /**
         * Fires after loading common controllers.
         */
        do_action( 'legal_pages_after_load_common_controllers' );
    }
}