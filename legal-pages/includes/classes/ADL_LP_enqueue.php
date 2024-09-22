<?php

if (!class_exists('ADL_LP_enqueue')):
/**
 * Class ADL_LP_enqueue.
 * It enqueue all scripts and styles needed by the plugin
 */
class ADL_LP_enqueue {


    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 999);
    }


    public function admin_enqueue_scripts($page) {
        global $pagenow, $typenow, $ADL_LP;
        wp_enqueue_style('adl-notice', ADL_LP_ADMIN_ASSETS . 'css/notice.css',);
        if ( 'admin.php' == $pagenow ) {
           wp_enqueue_style( 'adl-lp-bootstrap', ADL_LP_ADMIN_ASSETS . 'css/bootstrap.min.css', false, ADL_LP_VERSION );
            wp_enqueue_style('adl-tabs', ADL_LP_ADMIN_ASSETS . 'css/tabs.css', array('adl-lp-bootstrap'), ADL_LP_VERSION);
            wp_enqueue_style('adl-main', ADL_LP_ADMIN_ASSETS . 'css/adl-lp-main.css', array('adl-lp-bootstrap', 'adl-tabs'), ADL_LP_VERSION);
            wp_enqueue_style('adl-style', ADL_LP_ADMIN_ASSETS . 'css/style.css', array('adl-lp-bootstrap', 'adl-tabs'), ADL_LP_VERSION);
            wp_enqueue_script( 'adl-bootstrap-js', ADL_LP_ADMIN_ASSETS . 'js/bootstrap.min.js', array( 'jquery' ), ADL_LP_VERSION, true );
           

            wp_enqueue_script( 'adl-lp-main-js', ADL_LP_ADMIN_ASSETS . 'js/adl-lp-main.js', array(
                'jquery',
                'adl-bootstrap-js',
            ), ADL_LP_VERSION, true );

            $adl_lp_obj = array(
                'nonceAction' => $ADL_LP->nonceAction(),
                'nonce'       => wp_create_nonce( $ADL_LP->nonceText() ),
                'adminAsset'  => ADL_LP_ADMIN_ASSETS,
            );
            wp_localize_script( 'adl-lp-main-js', 'adl_lp_obj', $adl_lp_obj );
            wp_enqueue_media();

        }
        if ( 'index.php' == $pagenow || 'plugins.php' == $pagenow || 'adl-legal-pages' == $pagenow || 'all-legal-Pages' == $pagenow ) {
            //wp_enqueue_style('wcpcsu-notice', WCPCSU_URL . 'admin/css/wcpcsu-notice.css');
            wp_enqueue_style('adl-notice', ADL_LP_ADMIN_ASSETS . 'css/notice.css',);
        }

    }

}



endif;