<?php
namespace LegalPage\Controllers\Admin;
use LegalPage\Traits\Hook;

defined( 'ABSPATH' ) || exit;
class Init {

    use Hook;

    public function __construct() {
        // $this->action( 'init', array( $this, 'register_post_type' ) );
    }
    
    public function register_post_type() {
        $args = array(
            'labels'              => array(
                'name'          => __( 'Legal Pages', 'legal-pages' ),
                'singular_name' => __( 'Legal Page', 'legal-pages' ),
            ),
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => false,
            'query_var'           => true,
            'rewrite'             => array( 'slug' => 'adl-legal-pages' ),
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => null,
            'supports'            => array( 'title', 'editor', 'author' ),
            'show_in_rest'        => true,
        );

        register_post_type( 'legal_page', $args );
    }
}