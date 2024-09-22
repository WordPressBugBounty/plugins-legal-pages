<?php
if ( ! defined('ABSPATH') ) { die( 'Cheating? Direct access is not allowed !!!' ); }

if(!class_exists('ADL_LP_ajax_handler')):

/**
 * Class ADL_LP_ajax_handler.
 * It handles all ajax requests made from ADL Legal Page Plugin
 */
class ADL_LP_ajax_handler {

    /**
     * Register  hooks for  ajax actions.
     */
    public function __construct()
    {
        //All Ajax Hooks
        add_action( 'wp_ajax_general_info_handler', array($this, 'general_info_handler'));
        add_action( 'wp_ajax_reset_general_info_handler', array($this, 'reset_general_info_handler'));
        add_action( 'wp_ajax_social_info_handler', array($this, 'social_info_handler'));
        add_action( 'wp_ajax_reset_social_info_handler', array($this, 'reset_social_info_handler'));
        add_action( 'wp_ajax_misc_info_handler', array($this, 'misc_info_handler'));
        add_action( 'wp_ajax_loadCreateLpPage', array($this, 'loadCreateLpPage'));
        add_action( 'wp_ajax_fetch_and_insert_template_data', array($this, 'fetch_and_insert_template_data'));
        add_action( 'wp_ajax_addNewLegalPage', array($this, 'addNewLegalPage'));
        add_action( 'wp_ajax_moveToTrash', array($this, 'moveToTrash'));
        add_action( 'wp_ajax_addNewLegalTemplate', array($this, 'addNewLegalTemplate'));
        add_action( 'wp_ajax_editLegalTemplate', array($this, 'editLegalTemplate'));
        add_action( 'wp_ajax_deleteLegalTemplate', array($this, 'deleteLegalTemplate'));
    }


    public function deleteLegalTemplate() {
        global $ADL_LP, $wpdb;
        
        if ( current_user_can( 'delete_posts' ) ) {
            if( $ADL_LP->verifyNonce() ) {
                $tmpl_id = ( ! empty( $_POST['template_id'] ) ) ? absint( $_POST['template_id'] ) : 0;
                $success = $wpdb->delete( $ADL_LP->template_table_name, array('id'=>$tmpl_id), array('%d') );
                echo ( $success ) ? 'success': 'error';
            } else {
                esc_html_e( 'invalid nonce', 'legal-pages' );
            }
        } else {
            esc_html_e( 'insufficient permission', 'legal-pages' );
        }
        
        wp_die();
    }


    public function editLegalTemplate() {
        global $ADL_LP, $wpdb;

        if ( ! current_user_can( 'manage_options' ) ) {
            die( 'Permission denied.' );
        }
        
        if($ADL_LP->verifyNonce()) {
            $id = ( !empty($_POST['id']) ) ? absint($_POST['id']) : '';
            $lp_title = ( !empty($_POST['lp_title']) ) ? sanitize_text_field($_POST['lp_title']) : '';
            $adl_lp_template = ( !empty($_POST['adl_lp_template']) ) ? wp_kses_post($_POST['adl_lp_template']) : '';
            $type = ( !empty($_POST['type']) ) ? sanitize_text_field($_POST['type']) : '';
            $success = $wpdb->update($ADL_LP->template_table_name, array('name'=> $lp_title, 'content'=> $adl_lp_template, 'type'=> $type), array('id'=>$id), array('%s', '%s', '%s'), array('%d'));
            echo ($success) ? 'success': 'error';
            wp_die();
        }else {
            echo 'invalid nonce ';
        }

        wp_die();
    }


    /**
     * Save new Legal Page Template to the database.
     */
    public function addNewLegalTemplate() {
        global $ADL_LP, $wpdb;
        if($ADL_LP->verifyNonce()) {
            $lp_title = ( !empty($_POST['lp_title']) ) ? sanitize_text_field($_POST['lp_title']) : '';
            $adl_lp_template = ( !empty($_POST['adl_lp_template']) ) ? wp_kses_post($_POST['adl_lp_template']) : '';
            $success = $wpdb->insert($ADL_LP->template_table_name, array('name'=> $lp_title, 'content'=> $adl_lp_template, 'type'=> 'custom'), array('%s', '%s', '%s'));

            echo ($success) ? 'success': 'error';
            wp_die();
        }else {
            echo 'invalid nonce ';
        }
        wp_die();
    }


    public function moveToTrash() {
        global $ADL_LP;

        if ( ! current_user_can( 'manage_options' ) || ! $ADL_LP->verifyNonce() ) {
            die('Error: Something went wrong');
        }

        $post_id = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null;

        if ( $post_id && wp_trash_post($post_id) ) {
            echo 'success';
        }else{
            echo 'error';
        }

        wp_die();
    }


    public function addNewLegalPage() {
        global $ADL_LP, $wpdb;
        if($ADL_LP->verifyNonce()) {
            $lp_title = ( !empty($_POST['lp_title']) ) ? $_POST['lp_title'] : '';
            $content = ( !empty($_POST['content']) ) ? $_POST['content'] : '';
            $args = array(
                'post_title' => apply_filters('the_title', $lp_title),
                'post_content' => apply_filters('the_content', $content),
                'post_type' => 'page',
                'post_status' => 'publish',
                'meta_input' => array(
                                'is_adl_legal_page' => true,
                    ),
            );

            $res = wp_insert_post( $args, true );
            $p_e_link ="<a href='".get_edit_post_link($res)."'>Edit Page</a>";
            $p_p_link ="<a href='".get_post_permalink($res)."'>View Page</a>";
            $data = array($p_p_link, $p_e_link);
            $encoded_data = json_encode($data, JSON_UNESCAPED_SLASHES);
            echo $encoded_data;
            wp_die();
        }else {
            echo 'invalid nonce ';
        }
        wp_die();

    }


    public function fetch_and_insert_template_data(  ) {
        global $ADL_LP, $wpdb;

        if ( ! current_user_can( 'manage_options' ) || ! $ADL_LP->verifyNonce() ) {
            wp_send_json_error();
            die;
        }

        $id = (!empty($_POST['template_id'])) ? absint($_POST['template_id']) : '';
        $result = $wpdb->get_row($wpdb->prepare("SELECT * from {$ADL_LP->template_table_name}  WHERE id = %d", $id));
        $name = $result->name;
        $content = $result->content;
        $adl_lp_general_find = array('[siteUrl]', '[siteName]','[businessNiche]','[phoneNumber]','[emailAddress]','[streetName]','[countryName]','[cityName]','[stateName]','[zipCode]','[mailingAddress]',);
        $adl_lp_general = get_option('adl_lp_general');
        $adl_lp_social_find = array('[facebookUrl]','[googlePlusUrl]','[linkedInUrl]','[twitterUrl]',);
        $adl_lp_social = get_option('adl_lp_social');
        $fb= ! empty( $adl_lp_social['facebookUrl'] ) ? $adl_lp_social['facebookUrl'] : '';
        $gp= ! empty( $adl_lp_social['googlePlusUrl'] ) ? $adl_lp_social['googlePlusUrl'] : '';
        $li= ! empty( $adl_lp_social['linkedInUrl'] ) ? $adl_lp_social['linkedInUrl'] : '';
        $tt= ! empty( $adl_lp_social['twitterUrl'] ) ? $adl_lp_social['twitterUrl'] : '';
        $social_array = array(
            "<a href='{$fb}' target='_blank'> Find Us on Facebook</a>",
            "<a href='{$gp}' target='_blank'> Connect us on Google Plus</a>",
            "<a href='{$li}' target='_blank'> Connect us on LinkedIn</a>",
            "<a href='{$tt}' target='_blank'> Follow Us on Twitter</a>",
        );
        $mo_content = str_replace($adl_lp_general_find, $adl_lp_general, $content);
        $mo_content = str_replace($adl_lp_social_find, $social_array, $mo_content);

        $data = array($name, $mo_content);
        if(!empty($data)){
            $encodedData = json_encode($data);
            echo $encodedData;
        }else{
            wp_send_json_error();
        }
        wp_die();

    }


    public function loadCreateLpPage() {
        global $ADL_LP;
        $ADL_LP->loadView('settings/tab-content/testCreate');
        wp_die();
    }


    /**
     *  Reset general info in database
     */
    public function reset_general_info_handler() {
        global $ADL_LP;
        if($ADL_LP->verifyNonce()) {
            // nonce is valid, we can proceed
            // next try to build an array of errors and then show the users if they input invalid data.
            // Now we will just sanitize the data to be safe to be stored
            $adl_lp_general = array(
                'siteUrl' => '',
                'siteName' => '',
                'businessNiche' =>  '',
                'phoneNumber' =>  '',
                'emailAddress' =>  '',
                'streetName' =>  '',
                'countryName' =>  '',
                'cityName' =>   '',
                'stateName' => '',
                'zipCode' =>  '',
                'mailingAddress' => '',

            );

            $adl_lp_social = array(
                'facebookUrl' => '',
                'googlePlusUrl' => '',
                'linkedInUrl' => '',
                'mailingAddress' => '',
            );

            update_option('adl_lp_general', $adl_lp_general); // reset saved data
            update_option('adl_lp_social', $adl_lp_social);

            echo 'success';

        } else {
            echo 'invalid nonce';
        };

        die();
    }

    /**
     *  Save general info in database
     */
    public function general_info_handler() {
        global $ADL_LP;
        // get the data, sanitize and save the data and output the response
        if($ADL_LP->verifyNonce()) {
            // nonce is valid, we can proceed
            // next try to build an array of errors and then show the users if they input invalid data.
            // Now we will just sanitize the data to be safe to be stored
            $adl_lp_general = array(
                'siteUrl' => (isset($_POST['siteUrl'])) ? esc_url_raw($_POST['siteUrl']) : '',
                'siteName' => (isset($_POST['siteUrl'])) ? sanitize_text_field($_POST['siteName']): '',
                'businessNiche' => (isset($_POST['businessNiche'])) ? sanitize_text_field($_POST['businessNiche']): '',
                'phoneNumber' => (isset($_POST['phoneNumber'])) ? sanitize_text_field($_POST['phoneNumber']): '',
                'emailAddress' => (isset($_POST['emailAddress'])) ? sanitize_email($_POST['emailAddress']): '',
                'streetName' => (isset($_POST['streetName'])) ? sanitize_text_field($_POST['streetName']): '',
                'countryName' => (isset($_POST['countryName'])) ? sanitize_text_field($_POST['countryName']): '',
                'cityName' =>  (isset($_POST['cityName'])) ? sanitize_text_field($_POST['cityName']): '',
                'stateName' => (isset($_POST['stateName'])) ? sanitize_text_field($_POST['stateName']): '',
                'zipCode' => (isset($_POST['zipCode'])) ? sanitize_text_field($_POST['zipCode']): '',
                'mailingAddress' => (isset($_POST['mailingAddress'])) ? sanitize_text_field($_POST['mailingAddress']): '',

            );

            $adl_lp_social = array(
                'facebookUrl' => (isset($_POST['facebookUrl'])) ? esc_url_raw($_POST['facebookUrl']) : '',
                'googlePlusUrl' => (isset($_POST['googlePlusUrl'])) ? esc_url_raw($_POST['googlePlusUrl']) : '',
                'linkedInUrl' => (isset($_POST['linkedInUrl'])) ? esc_url_raw($_POST['linkedInUrl']) : '',
                'twitterUrl' => (isset($_POST['twitterUrl'])) ? esc_url_raw($_POST['twitterUrl']) : '',

            );

            update_option('adl_lp_social', $adl_lp_social);

            update_option('adl_lp_general', $adl_lp_general);
            echo 'success';

        }else{
                echo 'invalid nonce';
        };

        die();
    }


    public function social_info_handler() {
        global $ADL_LP;
        // get the data, sanitize and save the data and output the response
        if($ADL_LP->verifyNonce()) {
            // nonce is valid, we can proceed
            // next try to build an array of errors and then show the users if they input invalid data.
            // Now we will just sanitize the data to be safe to be stored
            $adl_lp_social = array(
                'facebookUrl' => (isset($_POST['facebookUrl'])) ? esc_url_raw($_POST['facebookUrl']) : '',
                'googlePlusUrl' => (isset($_POST['googlePlusUrl'])) ? esc_url_raw($_POST['googlePlusUrl']) : '',
                'linkedInUrl' => (isset($_POST['linkedInUrl'])) ? esc_url_raw($_POST['linkedInUrl']) : '',
                'twitterUrl' => (isset($_POST['twitterUrl'])) ? esc_url_raw($_POST['twitterUrl']) : '',
                'googlePlusUrl' => (isset($_POST['googlePlusUrl'])) ? esc_url_raw($_POST['googlePlusUrl']) : '',

            );

            update_option('adl_lp_social', $adl_lp_social);
            echo 'success';

        }else{
            echo 'invalid nonce';
        };
        die();
    }

    public function reset_social_info_handler() {
        global $ADL_LP;
        if($ADL_LP->verifyNonce()) {
            $adl_lp_social = array(
                'facebookUrl' => '',
                'googlePlusUrl' => '',
                'linkedInUrl' => '',
                'twitterUrl' => '',
                'googlePlusUrl' => '',

            );

            update_option('adl_lp_social', $adl_lp_social); // reset saved data

            echo 'success';

        } else {
            echo 'invalid nonce';
        };

        die();
    }


    public function misc_info_handler() {
        global $ADL_LP;
        if($ADL_LP->verifyNonce()) {
            // next try to build an array of errors and then show the users if they input invalid data.
            // Now we will just sanitize the data to be safe to be stored
            $adl_lp_misc = array(
                'hide_lp_in_search' => (isset($_POST['hide_lp_in_search'])) ? absint($_POST['hide_lp_in_search']): 0,
                'show_affiliate_disclosure' => (isset($_POST['show_affiliate_disclosure'])) ? absint($_POST['show_affiliate_disclosure']): 0,
                'show_cookie_warning' => (isset($_POST['show_cookie_warning'])) ? absint($_POST['show_cookie_warning']): 0,
                'delete_adl_lp_data' => (isset($_POST['delete_adl_lp_data'])) ? absint($_POST['delete_adl_lp_data']): 0,

            );

            update_option('adl_lp_misc', $adl_lp_misc);
            
            echo 'success';

        } else {
            echo 'invalid nonce';
        };
        die();
    }

}


endif;