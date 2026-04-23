<?php
namespace LegalPage\Controllers\Front;
use LegalPage\Traits\Hook;

defined( 'ABSPATH' ) || exit;

class Shortcode {
    use Hook;

    public function __construct() {
        $this->action( 'init', array( $this, 'register_shortcodes' ) );
        $this->filter( 'the_content', array( $this, 'replace_shortcodes' ), 10 );
    }

    /**
     * Register all shortcodes
     */
    public function register_shortcodes() {
        add_shortcode( 'wpwax_legal_page', array( $this, 'legal_page_shortcode' ) );
        add_shortcode( 'siteUrl', array( $this, 'siteurl_shortcode' ) );
        add_shortcode( 'siteName', array( $this, 'sitename_shortcode' ) );
        add_shortcode( 'businessNiche', array( $this, 'businessniche_shortcode' ) );
        add_shortcode( 'phoneNumber', array( $this, 'phonenumber_shortcode' ) );
        add_shortcode( 'emailAddress', array( $this, 'emailaddress_shortcode' ) );
        add_shortcode( 'streetName', array( $this, 'streetname_shortcode' ) );
        add_shortcode( 'citynNme', array( $this, 'cityname_shortcode' ) );
        add_shortcode( 'stateName', array( $this, 'statename_shortcode' ) );
        add_shortcode( 'countryName', array( $this, 'countryname_shortcode' ) );
        add_shortcode( 'zipCode', array( $this, 'zipcode_shortcode' ) );
        add_shortcode( 'mailingAddress', array( $this, 'completemailingaddress_shortcode' ) );
        add_shortcode( 'facebookUrl', array( $this, 'facebookurl_shortcode' ) );
        add_shortcode( 'googlePlusUrl', array( $this, 'googleplusurl_shortcode' ) );
        add_shortcode( 'linkedInUrl', array( $this, 'linkedinurl_shortcode' ) );
        add_shortcode( 'twitterUrl', array( $this, 'twitterurl_shortcode' ) );
    }

    /**
     * Get settings from database
     */
    private function get_settings() {
        return get_option( 'adl_lp_general', array() );
    }

    /**
     * Site URL shortcode
     */
    public function siteurl_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['site_url'] ) ? esc_html( $settings['site_url'] ) : '';
    }

    /**
     * Site name shortcode
     */
    public function sitename_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['site_name'] ) ? esc_html( $settings['site_name'] ) : '';
    }

    /**
     * Business niche shortcode
     */
    public function businessniche_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['business_niche'] ) ? esc_html( $settings['business_niche'] ) : '';
    }

    /**
     * Phone number shortcode
     */
    public function phonenumber_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['phone_number'] ) ? esc_html( $settings['phone_number'] ) : '';
    }

    /**
     * Email address shortcode
     */
    public function emailaddress_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['email_address'] ) ? esc_html( $settings['email_address'] ) : '';
    }

    /**
     * Street name shortcode
     */
    public function streetname_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['street_name'] ) ? esc_html( $settings['street_name'] ) : '';
    }

    /**
     * City name shortcode
     */
    public function cityname_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['city'] ) ? esc_html( $settings['city'] ) : '';
    }

    /**
     * State name shortcode
     */
    public function statename_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['state'] ) ? esc_html( $settings['state'] ) : '';
    }

    /**
     * Country name shortcode
     */
    public function countryname_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['country_name'] ) ? esc_html( $settings['country_name'] ) : '';
    }

    /**
     * Zip code shortcode
     */
    public function zipcode_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['zip_code'] ) ? esc_html( $settings['zip_code'] ) : '';
    }

    /**
     * Complete mailing address shortcode
     */
    public function completemailingaddress_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['complete_address'] ) ? esc_html( $settings['complete_address'] ) : '';
    }

    /**
     * Facebook URL shortcode
     */
    public function facebookurl_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['facebook_url'] ) ? esc_url( $settings['facebook_url'] ) : '';
    }

    /**
     * Google Plus URL shortcode
     */
    public function googleplusurl_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['google_plus_url'] ) ? esc_url( $settings['google_plus_url'] ) : '';
    }

    /**
     * LinkedIn URL shortcode
     */
    public function linkedinurl_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['linkedin_url'] ) ? esc_url( $settings['linkedin_url'] ) : '';
    }

    /**
     * Twitter URL shortcode
     */
    public function twitterurl_shortcode( $atts ) {
        $settings = $this->get_settings();
        return ! empty( $settings['twitter_url'] ) ? esc_url( $settings['twitter_url'] ) : '';
    }

    /**
     * Display legal page content by ID
     */

    public function legal_page_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts );

        $page_id = intval( $atts['id'] );

        if ( ! $page_id ) {
            return '<!-- Legal page ID not specified -->';
        }

        // Verify this is a legal page
        $is_legal_page = get_post_meta( $page_id, 'is_adl_legal_page', true );

        if ( $is_legal_page !== '1' ) {
            return '<!-- Invalid legal page ID -->';
        }

        // Get the page content
        $page = get_post( $page_id );

        if ( ! $page || $page->post_type !== 'page' ) {
            return '<!-- Legal page not found -->';
        }

        // Store the global $post temporarily and swap with our page
        global $post;
        $original_post = $post;
        $post = $page;

        // Setup post data for our page to ensure all filters work properly
        setup_postdata( $post );

        // Get the content
        $content = $page->post_content;

        // First replace the custom shortcodes with actual values
        $content = adl_replace_shortcodes_in_text( $content );

        // Apply WordPress content filters to maintain formatting
        // This is the key - it formats the content like WordPress does
        $content = wpautop( $content ); // Converts line breaks to <p> tags
        $content = do_shortcode( $content ); // Process any remaining WordPress shortcodes
        $content = wptexturize( $content ); // Apply proper typography
        $content = convert_chars( $content ); // Convert special characters
        $content = str_replace( ']]>', ']]&gt;', $content );

        // Restore the original post
        $post = $original_post;
        if ( $original_post ) {
            setup_postdata( $original_post );
        }

        // Wrap in a div with a class for styling
        return '<div class="wpwax-legal-page-content entry-content">' . $content . '</div>';
    }

    /**
     * Replace shortcodes in content with actual values
     *
     * @param string $content Post content
     * @return string Modified content
     */
    public function replace_shortcodes( $content ) {
        global $post;

        // Only process if it's a legal page
        if ( ! isset( $post->ID ) || get_post_meta( $post->ID, 'is_adl_legal_page', true ) !== '1' ) {
            return $content;
        }

        return adl_replace_shortcodes_in_text( $content );
    }
}