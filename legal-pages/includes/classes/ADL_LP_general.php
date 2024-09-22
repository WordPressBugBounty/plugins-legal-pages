<?php
if ( ! defined('ABSPATH') ) { die( 'Cheating? Direct access is not allowed !!!' ); }
if(!class_exists('ADL_LP_general')):
class ADL_LP_general {


    public function __construct(){

        add_action('admin_menu', array($this, 'show_admin_menu'));
    }

    /**
     * Show all menu pages for Legal pages
     */
    public function show_admin_menu() {
        add_menu_page(
            __('Legal Pages', ADL_LP_TEXTDOMAIN),
            __('Legal Pages', ADL_LP_TEXTDOMAIN),
            'manage_options',
            'adl-legal-pages',
            array($this, 'general_setting'),
            'dashicons-welcome-add-page',
            20
            );

        add_submenu_page('adl-legal-pages',
            __('Settings', ADL_LP_TEXTDOMAIN),
            __('Settings', ADL_LP_TEXTDOMAIN),
            'manage_options',
            'adl-legal-pages',
            array($this, 'general_setting')
        );

        add_submenu_page('adl-legal-pages',
            __('Add New Legal Page', ADL_LP_TEXTDOMAIN),
            __('Add New Legal Page', ADL_LP_TEXTDOMAIN),
            'manage_options',
            'createLegalPage',
            array($this, 'add_new_legal_page')
        );

        add_submenu_page('adl-legal-pages',
            __('All Legal Pages', ADL_LP_TEXTDOMAIN),
            __('All Legal Pages', ADL_LP_TEXTDOMAIN),
            'manage_options',
            'all-legal-Pages',
            array($this, 'all_legal_page')
        );

        add_submenu_page('adl-legal-pages',
            __('Legal Page Templates', ADL_LP_TEXTDOMAIN),
            __('Legal Page Templates', ADL_LP_TEXTDOMAIN),
            'manage_options',
            'legal-page-template',
            array($this, 'legal_page_template')
        );

        add_submenu_page('adl-legal-pages',
            __('Help & Support', ADL_LP_TEXTDOMAIN),
            __('Help & Support', ADL_LP_TEXTDOMAIN),
            'manage_options',
            'legal-page-support',
            array($this, 'get_support')
        );

        add_submenu_page('adl-legal-pages',
            __('Get More Templates', ADL_LP_TEXTDOMAIN),
            "<span style='color:#00b9eb;'>".__('Upgrade to Pro', ADL_LP_TEXTDOMAIN)."</span>",
            'manage_options',
            'adl-lp-upgrade',
            array($this, 'upgrade_notice')
        );

    }

    public function upgrade_notice(  ) {
        global $ADL_LP;
        $ADL_LP->loadView('upgrade-features');
    }

    public function show_create_legal_page(  ) {
        // this function is kept empty intentionally to redirect menu pages to the specific setting tab using javascripts.
    }

    public function show_create_template(  ) {
        global $ADL_LP, $wpdb;
        $sql1 = $wpdb->prepare("SELECT * FROM {$this->template_table_name} LIMIT %d", 40);
        $adl_lp_templates = $wpdb->get_results($sql1); // get all legal templates
        $ADL_LP->loadView('settings/tab-content/create-edit-templates', $adl_lp_templates);
    }

    public function add_new_legal_page() {
        global $ADL_LP, $wpdb;
         $ADL_LP->loadView('settings/tab-content/create-page');
    }

    public function all_legal_page() {
        global $ADL_LP;
        $adl_legal_pages = $ADL_LP->legal_pages();
        $ADL_LP->loadView('settings/tab-content/list-legal-pages', $adl_legal_pages);
    }

    public function legal_page_template() {
        global $ADL_LP;
        $adl_lp_templates = $ADL_LP->legal_page_templates();
        if( ! empty( $_GET['action'] ) && ( 'new-template' == $_GET['action'] || 'edit' == $_GET['action'])  ) {
            $ADL_LP->loadView('settings/tab-content/create-edit-templates', $adl_lp_templates);
        }else{
            $ADL_LP->loadView('settings/tab-content/create-edit-templates-for-tab', $adl_lp_templates);
        }

    }

    public function get_support() {
        global $ADL_LP;
        $ADL_LP->loadView('support');
    }

    public function general_setting() {
        global $ADL_LP;
        $data = array(
                'adl_legal_pages' => $ADL_LP->get_lp_pages(),
                'adl_lp_templates' => $ADL_LP->get_lp_templates(),
        );
        // if terms already accepted then show the setting else tell the users to accept terms
        if ( get_option('adl_lp_accept_term') ) {
            $ADL_LP->loadView('settings/general-settings', $data);
            //update_option('adl_lp_accept_term', 0);// test Disclaimer page uncommenting this.
        }else {
            $this->acceptTermsAndCondition();
        }
    }

    public function acceptTermsAndCondition() {
        global $ADL_LP;
        // set adl_lp_accept_term when user use the plugin for the first time and them load the general  setting. else show the terms page.
        if ( isset( $_POST['adl_lp_submit'] ) && 'Accept' == $_POST['adl_lp_submit'] && isset( $_POST['adl_accept_terms'] ) && wp_verify_nonce( $_POST['adl_lp_accept_terms_nonce_field'], 'adl_lp_accept_terms_nonce' ) ) {
            update_option('adl_lp_accept_term', $_POST['adl_accept_terms']);
            $this->general_setting();
        }else{
            $ADL_LP->loadView('disclaimer');
        }
    }
}

endif;