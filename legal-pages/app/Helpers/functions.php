<?php

/**
 * Check if current screen is a Legal Pages admin page
 *
 * @return bool
 */
function legal_page_is_legal_pages_screen() {

    if ( ! function_exists( 'get_current_screen' ) ) {
        return false;
    }

    $screen = get_current_screen();

    if ( ! $screen ) {
        return false;
    }

    return strpos( $screen->id, 'toplevel_page_adl-legal-pages' ) !== false;
}

/**
 * Get localized data for JavaScript
 *
 * @return array
 */
function legal_page_get_localized_data() {
	return array(
		'rest_base'  => esc_url_raw( get_rest_url() ),
		'nonce'      => wp_create_nonce( 'wp_rest' ),
		'apiUrl'     => rest_url( 'legal-pages/v1' ),
		'ajax_url'   => admin_url( 'admin-ajax.php' ),
		'site_url'   => get_site_url(),
		'assets_url' => LEGAL_PAGES_ASSETS_URL,
		'plugin_url' => LEGAL_PAGES_URL,
		'version'    => LEGAL_PAGES_VERSION,
		'license'    => array(
			'key'    => get_option( 'wplp_license_key', '' ),
			'status' => get_option( 'wplp_license_status', '' ),
		),
		'is_pro'     	=> defined( 'WPLP_PRO_VERSION' ),
		'is_pro_active' => get_option( 'wplp_pro_version', false ),
		'status'     	=> get_option( 'adl_lp_accept_term', '0' ),
		'setup_wizard'  => array(
			'completed' => (bool) get_option( 'adl_lp_setup_wizard_completed', false ),
		),
		// Cookie Bar Pro features (sync, per-cookie edits, customization, Block Services).
		// Legal Pages Pro turns this on only with a valid license.
		'cookie_pro'    => legal_pages_cookie_pro_unlocked(),
		// Admin SPA notices the current user has closed (POST /notices/{notice}/dismiss).
		'dismissed_notices' => \LegalPage\Models\Settings::get_dismissed_notices(),
		// Pro before 2.1.0 is active: the Cookie Bar screen asks for a Pro update instead.
		'cookie_legacy_pro' => legal_pages_cookie_bar_in_legacy_pro() ? array(
			'version'    => defined( 'LEGAL_PAGES_PRO_VERSION' ) ? LEGAL_PAGES_PRO_VERSION : '',
			'update_url' => current_user_can( 'update_plugins' ) ? legal_pages_pro_update_url() : '',
		) : false,
	);
}

/**
 * Where to send the admin to update Legal Pages Pro: straight to the update
 * when WordPress already knows about it, otherwise to the Plugins screen.
 *
 * @return string
 */
function legal_pages_pro_update_url() {
	$plugin_file = defined( 'LEGAL_PAGES_PRO_BASE' ) ? LEGAL_PAGES_PRO_BASE : 'legal-pages-pro/adl-legal-pages-pro.php';
	$updates     = get_site_transient( 'update_plugins' );

	if ( isset( $updates->response[ $plugin_file ] ) ) {
		return wp_nonce_url(
			self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $plugin_file ) ),
			'upgrade-plugin_' . $plugin_file
		);
	}

	return self_admin_url( 'plugins.php' );
}

/**
 * Whether Legal Pages Pro's cookie bar features are available.
 *
 * @return bool
 */
function legal_pages_cookie_pro_unlocked() {
	return (bool) apply_filters( 'legal_pages_cookie_pro_unlocked', false );
}

/**
 * Legal Pages Pro before 2.1.0 shipped its own scanner and banner. With it
 * active the free cookie bar stays off so nothing runs twice.
 *
 * @return bool
 */
function legal_pages_cookie_bar_in_legacy_pro() {
	return class_exists( 'LegalPagePro\\Controllers\\Front\\CookieBar' );
}

/**
 * Replace custom shortcodes in text
 *
 * @param string $content Content to process
 * @return string Processed content
 */
function adl_replace_shortcodes_in_text( $content ) {
	$settings = get_option( 'adl_lp_general', array() );
	$social   = get_option( 'adl_lp_social', array() );

	// Resolve value checking both new (snake_case) and old (camelCase) key formats
	$get = function( $new_key, $old_key ) use ( $settings, $social ) {
		if ( ! empty( $settings[ $new_key ] ) ) return $settings[ $new_key ];
		if ( ! empty( $settings[ $old_key ] ) ) return $settings[ $old_key ];
		if ( ! empty( $social[ $old_key ] )   ) return $social[ $old_key ];
		return '';
	};

	$fb = $get( 'facebook_url',    'facebookUrl' );
	$li = $get( 'linkedin_url',    'linkedInUrl' );
	$tw = $get( 'twitter_url',     'twitterUrl' );

	$site_url  = $get( 'site_url',  'siteUrl' );
	$site_name = $get( 'site_name', 'siteName' ) ?: $get( 'site_name', 'business_name' );

	$shortcodes = array(
		'[siteUrl]' => $site_url
			? '<a href="' . esc_url( $site_url ) . '" target="_blank">' . esc_html( $site_url ) . '</a>'
			: '',

		'[siteName]'       => esc_html( $site_name ),
		'[businessNiche]'  => esc_html( $get( 'business_niche',   'businessNiche' ) ),
		'[phoneNumber]'    => esc_html( $get( 'phone_number',     'phoneNumber' ) ),
		'[emailAddress]'   => esc_html( $get( 'email_address',    'emailAddress' ) ),
		'[streetName]'     => esc_html( $get( 'street_name',      'streetName' ) ),
		'[cityName]'       => esc_html( $get( 'city',             'cityName' ) ),
		'[citynNme]'       => esc_html( $get( 'city',             'cityName' ) ),
		'[stateName]'      => esc_html( $get( 'state',            'stateName' ) ),
		'[countryName]'    => esc_html( $get( 'country_name',     'countryName' ) ),
		'[zipCode]'        => esc_html( $get( 'zip_code',         'zipCode' ) ),
		'[mailingAddress]' => esc_html( $get( 'complete_address', 'mailingAddress' ) ),

		'[facebookUrl]'   => $fb ? '<a href="' . esc_url( $fb ) . '" target="_blank">Find Us on Facebook</a>'        : '',
		// Google+ shut down in 2019 — always blank so any pre-existing page/template still
		// carrying this tag from before it was removed renders cleanly instead of showing raw text.
		'[googlePlusUrl]' => '',
		'[linkedinUrl]'   => $li ? '<a href="' . esc_url( $li ) . '" target="_blank">Connect us on LinkedIn</a>'     : '',
		'[linkedInUrl]'   => $li ? '<a href="' . esc_url( $li ) . '" target="_blank">Connect us on LinkedIn</a>'     : '',
		'[twitterUrl]'    => $tw ? '<a href="' . esc_url( $tw ) . '" target="_blank">Follow Us on Twitter</a>'       : '',
	);

	foreach ( $shortcodes as $shortcode => $value ) {
		$content = str_replace( $shortcode, $value, $content );
	}

	return $content;
}