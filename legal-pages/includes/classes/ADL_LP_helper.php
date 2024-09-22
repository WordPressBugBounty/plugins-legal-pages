<?php

if ( ! defined('ABSPATH') ) { die( 'Cheating? Direct access is not allowed !!!' ); }

if( !class_exists( 'ADL_LP_helper' ) ) :

/**
 * Class ADL_LP_helper
 * It provides useful helper methods to use throughout the plugin
 */
class ADL_LP_helper {
    private $nonce_action = 'adl_LP_nonce_action';
    private $nonce_name = 'adl_LP_nonce';

    public function __construct(){
        if ( ! defined('ABSPATH') ) { return; }
        add_action('init', array( $this, 'check_req_php_version' ), 100 );
    }

    public function verifyNonce( ){
        $nonce      = (!empty($_REQUEST[$this->nonceName()])) ? $_REQUEST[$this->nonceName()] : null ;
        $nonceAction  = $this->nonceAction();
        if( !wp_verify_nonce( $nonce, $nonceAction ) ) return false;
        return true;
    }

    public function nonceAction(){
        return $this->nonce_action;
    }

    public function nonceName(){
        return $this->nonce_name;
    }

    public function check_req_php_version( ){
        if ( version_compare( PHP_VERSION, '5.4', '<' )) {
            add_action( 'admin_notices', array($this, 'adl_LP_notice'), 100 );
            // deactivate the plugin because required php version is less.
            add_action( 'admin_init', array($this, 'adl_LP_deactivate_self'), 100 );

            return;
        }
    }

    public function adl_LP_notice() { ?>
        <div class="error"> <p>
                <?php
                printf(esc_html__('%s requires minimum PHP 5.4 to function properly. Please upgrade PHP version. The Plugin has been auto-deactivated.. You have PHP version %d', ADL_LP_TEXTDOMAIN), ADL_LP_PLUGIN_NAME, PHP_VERSION);
                ?>
            </p></div>
        <?php
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }

    public function adl_LP_deactivate_self() {
        deactivate_plugins( ADL_LP_BASE );
    }

    /**
     * Display date in a human readable form. It generally displays relative time eg. 1 hour ego, 5 mins ago etc.
     * @param string      $d    Optional. Format to use for retrieving the time the post
     *                          was written. Either 'G', 'U', or php date format defaults
     *                          to the value specified in the time_format option. Default empty.
     * @param int|WP_Post $post_or_ID WP_Post object or ID. Default is global $post object.
     * @return string Formatted date string in a relative time form for human readability.
     */
    public function humanDate( $post_or_ID, $d='U' ) {
        return human_time_diff( get_the_time($d, $post_or_ID), current_time('timestamp') ) . ' ago';
    }

    /**
     * It returns the name of the author and his/her post/pages link
     * @param int $post_author_ID The id of the author of the page or the post
     * @param string $post_type The name of the post type to link users. eg. page, post etc
     *
     * @return string It returns the name of the author of a page or post and display a link to the post or page.
     */
    public function author_name_and_post_link( $post_author_ID = 1, $post_type='post' ) {
        return "<a href='" . get_admin_url() . "/edit.php?post_type={$post_type}&author={$post_author_ID}' > " . get_the_author_meta('display_name', $post_author_ID) . "</a>";
    }

    /**
     * It returns the url to a specific admin page with an id, page, and action query string.
     * @param int $id The id of the post or page to edit
     * @param string $action    The name of the action to add to the url such as create , edit, delete etc
     * @param string $page  The name of the page where you would like to send the link.
     * @param bool $echo  Echo out the url string by default. Set false to return the value instead of echoing.
     *
     * @return string It returns the url to a specific admin page with an id, page, and action query string.
     */
    public function adl_lp_action_link( $id=0, $action='edit', $page='legal-page-template', $echo=true) {
        $url = get_admin_url() . "admin.php?page={$page}&action={$action}&id={$id}";
        if (!$echo) return $url;
        echo $url;
    }

    public function upgrade_to_pro_link( $plugin_slug='legal-pages-pro', $link_text = 'Pro Version', $target='_blank' ) {
        return "<a class='wplp-btn-primary' href='https://wpwax.com/product/legal-pages-pro/' target='{$target}'>{$link_text}</a>";
    }

    public function adl_lp_plugin_page( $tab = 'home', $page='adl-legal-pages' ) {
        return get_admin_url() ."admin.php?page={$page}";
    }

    public function legal_pages() {
        global $ADL_LP, $wpdb;
        $sql1 = array(
            'post_type'  => 'page',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key'     => 'is_adl_legal_page',
                    'value'   => true,
                    'compare' => '=',
                ),

            ),
        );

        return new WP_Query( $sql1 ); // get all legal pages
    }

    public function legal_page_templates() {
        global $ADL_LP, $wpdb;
        $sql2 = 'SELECT * FROM '.$ADL_LP->template_table_name .' LIMIT 40';
        return $wpdb->get_results($sql2); // get all legal templates

    }

}

endif;
