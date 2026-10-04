<?php
namespace LegalPage\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Cookie bar settings (option `adl_lp_cookie`) and the known-services catalog
 * the scanner matches pages against.
 *
 * The consent banner is a Legal Pages Pro feature (LegalPagePro\Controllers\Front\ConsentBanner),
 * so the free plugin doesn't store any of these settings itself: get() returns
 * defaults() (banner off), and Pro, only with a valid license, supplies and saves
 * the stored values through the `legal_pages_cookie_settings` /
 * `legal_pages_cookie_settings_save` filters. The free plugin still reads them
 * (e.g. the cookie list's "change your consent" link) and owns the services catalog.
 */
class Cookie_Bar {

    const OPTION_KEY   = 'adl_lp_cookie';
    const SERVICES_KEY = 'adl_lp_cookie_available_services';

    // Consent categories a visitor can opt in to. "functional" is always granted and not listed.
    const OPTIONAL_CATEGORIES = [ 'preferences', 'statistics', 'marketing' ];

    // On/off settings, always returned as booleans.
    const TOGGLE_KEYS = [ 'show_cookie_warning', 'auto_reload', 'show_manage_button', 'google_consent_mode' ];

    // Default consent category for the built-in services; everything else is "marketing".
    const SERVICE_CATEGORIES = [
        'Google Analytics'   => 'statistics',
        'Google Gtag'        => 'statistics',
        'Google Tag Manager' => 'statistics',
        'Jetpack Stats'      => 'statistics',
        'Matomo'             => 'statistics',
    ];

    public static function defaults(): array {
        return [
            'show_cookie_warning'       => false,
            'auto_reload'               => false,
            'cookie_display_position'   => 'bottom',
            'cookie_policy_link'        => 'https://www.cookiesandyou.com/',
            'learn_more_link_text'      => 'Learn more',
            'cookie_message'            => 'This website uses cookies to ensure you get the best experience on our website.',
            'cookie_dismiss_button'     => 'Dismiss',
            'cookie_deny_button'        => 'Deny',
            'cookie_accept_button'      => 'Accept',
            'cookie_message_bg'         => '#1f2937',
            'cookie_message_color'      => '#ffffff',
            'cookie_button_bg'          => '#7c3aed',
            'cookie_button_text_color'  => '#ffffff',
            'cookie_title'              => 'Manage Consent',
            'cookie_preferences_button' => 'View preferences',
            'cookie_save_button'        => 'Save preferences',
            'cookie_manage_button'      => 'Manage consent',
            'cookie_categories'         => self::OPTIONAL_CATEGORIES,
            'show_manage_button'        => true,
            'google_consent_mode'       => false,
        ];
    }

    private static function stored(): array {
        $raw   = get_option( self::OPTION_KEY, [] );
        $saved = is_array( $raw ) ? $raw : maybe_unserialize( $raw );
        return is_array( $saved ) ? $saved : [];
    }

    public static function get(): array {
        $data = (array) apply_filters( 'legal_pages_cookie_settings', self::defaults(), self::stored() );

        foreach ( self::TOGGLE_KEYS as $key ) {
            $data[ $key ] = (bool) $data[ $key ];
        }
        $data['cookie_categories'] = self::sanitize_categories( $data['cookie_categories'] );

        return $data;
    }

    /**
     * Without Pro nothing changes: stored values are kept as they are, so
     * settings saved while Pro was licensed come back if the license is renewed.
     */
    public static function save( array $params ): array {
        $stored = (array) apply_filters( 'legal_pages_cookie_settings_save', self::stored(), $params );

        update_option( self::OPTION_KEY, $stored );

        return self::get();
    }

    public static function sanitize_categories( $categories ): array {
        $categories = is_array( $categories ) ? $categories : [];
        // Keep the canonical order regardless of the order they were ticked in.
        return array_values( array_intersect( self::OPTIONAL_CATEGORIES, $categories ) );
    }

    public static function category_labels(): array {
        return [
            'functional'  => __( 'Functional', 'legal-pages' ),
            'preferences' => __( 'Preferences', 'legal-pages' ),
            'statistics'  => __( 'Statistics', 'legal-pages' ),
            'marketing'   => __( 'Marketing', 'legal-pages' ),
        ];
    }

    /**
     * Category descriptions shown in the banner's preferences panel and the cookie list.
     */
    public static function category_descriptions(): array {
        return apply_filters( 'legal_pages_cookie_category_descriptions', [
            'functional'  => __( 'Needed for the site to work, for example to keep you logged in or remember your consent choices. Always active.', 'legal-pages' ),
            'preferences' => __( 'Remember choices you make, such as your language or region.', 'legal-pages' ),
            'statistics'  => __( 'Help us understand how visitors use the site so we can improve it.', 'legal-pages' ),
            'marketing'   => __( 'Used to show relevant ads and to track visitors across websites.', 'legal-pages' ),
        ] );
    }

    // ── Known services catalog (scan detection; Pro's Block Services edits it) ──

    public static function default_service_category( string $name ): string {
        return self::SERVICE_CATEGORIES[ $name ] ?? 'marketing';
    }

    public static function get_available_services(): array {
        $raw      = get_option( self::SERVICES_KEY, null );
        $saved    = null === $raw ? [] : ( is_array( $raw ) ? $raw : maybe_unserialize( $raw ) );
        $services = ( is_array( $saved ) && count( $saved ) > 0 ) ? $saved : self::default_services();

        // Built-in services, and ones saved before categories existed, get their default category.
        return array_map(
            static function ( $svc ) {
                if ( empty( $svc['category'] ) || ! in_array( $svc['category'], self::OPTIONAL_CATEGORIES, true ) ) {
                    $svc['category'] = self::default_service_category( (string) ( $svc['name'] ?? '' ) );
                }
                return $svc;
            },
            $services
        );
    }

    public static function save_available_services( array $services ): array {
        $clean = [];
        foreach ( $services as $svc ) {
            if ( empty( $svc['name'] ) || empty( $svc['regex'] ) ) {
                continue;
            }
            $name     = sanitize_text_field( $svc['name'] );
            $category = $svc['category'] ?? '';
            $clean[]  = [
                'id'       => (int) ( $svc['id'] ?? time() ),
                'name'     => $name,
                'regex'    => sanitize_text_field( $svc['regex'] ),
                'category' => in_array( $category, self::OPTIONAL_CATEGORIES, true ) ? $category : self::default_service_category( $name ),
            ];
        }
        update_option( self::SERVICES_KEY, $clean );
        return $clean;
    }

    private static function default_services(): array {
        return [
            [ 'id' => 1,  'name' => 'AWIN',                                       'regex' => 'www\.dwin1\.com\/' ],
            [ 'id' => 2,  'name' => 'Facebook Pixel',                             'regex' => 'connect\.facebook\.net|www\.facebook\.com\/tr' ],
            [ 'id' => 3,  'name' => 'Facebook Social Plugins',                    'regex' => 'www\.facebook\.com\/plugins\/like\.php|www\.facebook\.com\/plugins\/ajax\.php\?action\=likeboxfrontend' ],
            [ 'id' => 4,  'name' => 'Google Adsense',                             'regex' => 'pagead2\.googlesyndication\.com\/pagead\/js\/adsbygoogle\.js' ],
            [ 'id' => 5,  'name' => 'Google AdWords Remarketing/Conversion Tag',  'regex' => 'googleadservices\.com\/pagead\/conversion\.js|googleads\.g\.doubleclick' ],
            [ 'id' => 6,  'name' => 'Google Analytics',                           'regex' => 'google-analytics\.com\/analytics\.js' ],
            [ 'id' => 7,  'name' => 'Google Gtag',                                'regex' => 'googletagmanager\.com\/gtag\/js' ],
            [ 'id' => 8,  'name' => 'Google Maps',                                'regex' => 'maps\.google\.com|google\.\[\w\.\]+\/maps\/|maps\.googleapis\.com' ],
            [ 'id' => 9,  'name' => 'Google Tag Manager',                         'regex' => 'www\.googletagmanager\.com\/gtm\.js' ],
            [ 'id' => 10, 'name' => 'Instagram',                                  'regex' => 'instagram\.com\/embed\.js|platform\.instagram\.com\/.*\/embeds\.js' ],
            [ 'id' => 11, 'name' => 'Jetpack Stats',                              'regex' => '\/\/stats\.wp\.com' ],
            [ 'id' => 12, 'name' => 'LinkedIn Pixel',                             'regex' => 'snap\.licdn\.com' ],
            [ 'id' => 13, 'name' => 'Matomo',                                     'regex' => '\/piwik\.js|\/matomo\.js' ],
            [ 'id' => 14, 'name' => 'Twitter',                                    'regex' => 'platform\.twitter\.com' ],
            [ 'id' => 15, 'name' => 'Vimeo',                                      'regex' => 'player\.vimeo\.com\/video' ],
            [ 'id' => 16, 'name' => 'YouTube',                                    'regex' => 'youtube\.com\/embed' ],
        ];
    }
}
