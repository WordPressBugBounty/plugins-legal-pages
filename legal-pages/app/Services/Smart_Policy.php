<?php
namespace LegalPage\Services;

use LegalPage\Models\Cookie_Scan as Scan_Model;
use LegalPage\Models\Smart_Policy as Policy_Model;

defined( 'ABSPATH' ) || exit;

/**
 * Smart Policy: which services we recognize, and which of them this site uses.
 *
 * Detection only ever runs from admin actions and cron, never on a frontend
 * page load. [lp_smart_policy] (Legal Pages Pro) reads the saved result, and
 * clause text is added by Pro through the lp_smart_policy_services filter,
 * because the output is a Pro feature (see docs/smart-policy-plan.md).
 */
class Smart_Policy {

    const CRON_HOOK = 'lp_smart_policy_daily';

    // Cheap checks, safe to run right after a plugin is switched on or off.
    const QUICK_CHECKS = [ 'plugin', 'option' ];

    // Every rule type. A run that covers all of these updates "last checked".
    const ALL_CHECKS = [ 'plugin', 'option', 'cookie', 'scan', 'content' ];

    /**
     * Categories in the order the policy shows them.
     */
    public static function categories(): array {
        return [
            'ecommerce'   => __( 'Ecommerce & Payments', 'legal-pages' ),
            'forms'       => __( 'Forms', 'legal-pages' ),
            'newsletter'  => __( 'Newsletter', 'legal-pages' ),
            'analytics'   => __( 'Analytics', 'legal-pages' ),
            'advertising' => __( 'Advertising', 'legal-pages' ),
            'embedded'    => __( 'Embedded Content', 'legal-pages' ),
            'comments'    => __( 'Comments', 'legal-pages' ),
        ];
    }

    /**
     * Known services in category order, each with name, category, detect (a list
     * of rules, any match counts) and privacy_url. Entries without a name, a rule
     * or a known category are left out.
     *
     * @return array [ id => service ]
     */
    public static function registry(): array {
        /**
         * Filters the services Smart Policy recognizes.
         *
         * @param array $services [ id => [ name, category, detect, privacy_url, clause ] ].
         */
        $services = (array) apply_filters( 'lp_smart_policy_services', self::default_services() );

        $registry = [];
        foreach ( array_keys( self::categories() ) as $category ) {
            foreach ( $services as $id => $service ) {
                if ( empty( $service['name'] ) || empty( $service['detect'] ) || ( $service['category'] ?? '' ) !== $category ) {
                    continue;
                }
                // A single rule is accepted too: 'detect' => [ 'type' => …, 'value' => … ].
                if ( isset( $service['detect']['type'] ) ) {
                    $service['detect'] = [ $service['detect'] ];
                }
                // privacy_name: the name visitors see in the policy, when that isn't the service name.
                $registry[ $id ] = wp_parse_args( $service, [ 'privacy_url' => '', 'privacy_name' => $service['name'] ] );
            }
        }
        return $registry;
    }

    /**
     * privacy_url is the policy of the company the visitor's data goes to. Plugins
     * that keep their data on this site (forms, Easy Digital Downloads) and WordPress
     * comments get none: their makers' policies only cover the makers' own websites
     * (Contact Form 7's says so), so a link would point visitors at the wrong policy.
     * WooCommerce is the same kind of plugin: its order data stays on the site, and
     * the payment gateways have their own entries. Marketing confirmed the URLs 2026-11-09.
     *
     * Rule types: plugin (value = plugin file), option (name + value), cookie
     * (value = name, a trailing * matches any ending), scan (value = a service
     * name from Cookie_Bar::get_available_services()), content (value = text in
     * published posts).
     */
    private static function default_services(): array {
        return [
            'woocommerce'      => [
                'name'     => 'WooCommerce',
                'category' => 'ecommerce',
                'detect'   => [ self::plugin_rule( 'woocommerce/woocommerce.php' ) ],
            ],
            'stripe'           => [
                'name'     => 'Stripe',
                'category' => 'ecommerce',
                'detect'   => [ self::plugin_rule( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php' ) ],
                'privacy_url' => 'https://stripe.com/privacy',
            ],
            'paypal'           => [
                'name'     => 'PayPal',
                'category' => 'ecommerce',
                'detect'   => [ self::plugin_rule( 'woocommerce-paypal-payments/woocommerce-paypal-payments.php' ) ],
                'privacy_url' => 'https://www.paypal.com/legalhub/privacy-full',
            ],
            'edd'              => [
                'name'     => 'Easy Digital Downloads',
                'category' => 'ecommerce',
                'detect'   => [ self::plugin_rule( 'easy-digital-downloads/easy-digital-downloads.php' ) ],
            ],
            'contact_form_7'   => [
                'name'     => 'Contact Form 7',
                'category' => 'forms',
                'detect'   => [ self::plugin_rule( 'contact-form-7/wp-contact-form-7.php' ) ],
            ],
            'wpforms'          => [
                'name'     => 'WPForms',
                'category' => 'forms',
                'detect'   => [
                    self::plugin_rule( 'wpforms-lite/wpforms.php' ),
                    self::plugin_rule( 'wpforms/wpforms.php' ),
                ],
            ],
            'gravity_forms'    => [
                'name'     => 'Gravity Forms',
                'category' => 'forms',
                'detect'   => [ self::plugin_rule( 'gravityforms/gravityforms.php' ) ],
            ],
            'fluent_forms'     => [
                'name'     => 'Fluent Forms',
                'category' => 'forms',
                'detect'   => [ self::plugin_rule( 'fluentform/fluentform.php' ) ],
            ],
            'mailchimp'        => [
                'name'     => 'Mailchimp for WP',
                'category' => 'newsletter',
                'detect'   => [ self::plugin_rule( 'mailchimp-for-wp/mailchimp-for-wp.php' ) ],
                'privacy_url' => 'https://mailchimp.com/legal/privacy/',
                'privacy_name' => 'Mailchimp',
            ],
            'mailpoet'         => [
                'name'     => 'MailPoet',
                'category' => 'newsletter',
                'detect'   => [ self::plugin_rule( 'mailpoet/mailpoet.php' ) ],
                'privacy_url' => 'https://automattic.com/privacy/',
            ],
            'google_analytics' => [
                'name'     => 'Google Analytics',
                'category' => 'analytics',
                'detect'   => [
                    [ 'type' => 'cookie', 'value' => '_ga' ],
                    [ 'type' => 'cookie', 'value' => '_ga_*' ],
                    self::plugin_rule( 'google-site-kit/google-site-kit.php' ),
                ],
                'privacy_url' => 'https://policies.google.com/privacy',
            ],
            'jetpack'          => [
                'name'     => 'Jetpack',
                'category' => 'analytics',
                'detect'   => [
                    self::plugin_rule( 'jetpack/jetpack.php' ),
                    [ 'type' => 'scan', 'value' => 'Jetpack Stats' ],
                ],
                'privacy_url' => 'https://automattic.com/privacy/',
            ],
            'meta_pixel'       => [
                'name'     => 'Meta Pixel',
                'category' => 'advertising',
                'detect'   => [
                    [ 'type' => 'cookie', 'value' => '_fbp' ],
                    [ 'type' => 'scan', 'value' => 'Facebook Pixel' ],
                    // Meta's own plugin sets _fbp from the server, with no script in the page.
                    self::plugin_rule( 'official-facebook-pixel/facebook-for-wordpress.php' ),
                ],
                'privacy_url' => 'https://www.facebook.com/privacy/policy/',
            ],
            'google_ads'       => [
                'name'     => 'Google Ads',
                'category' => 'advertising',
                'detect'   => [
                    [ 'type' => 'cookie', 'value' => '_gcl_au' ],
                    [ 'type' => 'scan', 'value' => 'Google AdWords Remarketing/Conversion Tag' ],
                ],
                'privacy_url' => 'https://policies.google.com/privacy',
            ],
            'youtube'          => [
                'name'     => __( 'YouTube embeds', 'legal-pages' ),
                'category' => 'embedded',
                'detect'   => [
                    // WordPress embeds store the plain video URL, not the /embed/ one.
                    [ 'type' => 'content', 'value' => 'youtube.com/embed' ],
                    [ 'type' => 'content', 'value' => 'youtube.com/watch' ],
                    [ 'type' => 'content', 'value' => 'youtu.be' ],
                    [ 'type' => 'content', 'value' => 'youtube-nocookie.com/embed' ],
                    [ 'type' => 'scan', 'value' => 'YouTube' ],
                ],
                'privacy_url' => 'https://policies.google.com/privacy',
                'privacy_name' => 'YouTube',
            ],
            'vimeo'            => [
                'name'     => __( 'Vimeo embeds', 'legal-pages' ),
                'category' => 'embedded',
                'detect'   => [
                    [ 'type' => 'content', 'value' => 'vimeo.com/' ],
                    [ 'type' => 'scan', 'value' => 'Vimeo' ],
                ],
                'privacy_url' => 'https://vimeo.com/privacy',
                'privacy_name' => 'Vimeo',
            ],
            'google_maps'      => [
                'name'     => __( 'Google Maps embeds', 'legal-pages' ),
                'category' => 'embedded',
                'detect'   => [
                    [ 'type' => 'content', 'value' => 'google.com/maps/embed' ],
                    [ 'type' => 'scan', 'value' => 'Google Maps' ],
                ],
                'privacy_url' => 'https://policies.google.com/privacy',
                'privacy_name' => 'Google Maps',
            ],
            'comments'         => [
                'name'     => __( 'Comments', 'legal-pages' ),
                'category' => 'comments',
                'detect'   => [ [ 'type' => 'option', 'name' => 'default_comment_status', 'value' => 'open' ] ],
            ],
            'gravatar'         => [
                'name'     => 'Gravatar',
                'category' => 'comments',
                'detect'   => [ [ 'type' => 'option', 'name' => 'show_avatars', 'value' => '1' ] ],
                'privacy_url' => 'https://automattic.com/privacy/',
            ],
            'akismet'          => [
                'name'     => 'Akismet',
                'category' => 'comments',
                'detect'   => [ self::plugin_rule( 'akismet/akismet.php' ) ],
                'privacy_url' => 'https://automattic.com/privacy/',
            ],
        ];
    }

    private static function plugin_rule( string $file ): array {
        return [ 'type' => 'plugin', 'value' => $file ];
    }

    /**
     * Everything the admin screen shows. Pro adds overrides through the filter.
     * Clause text stays out of it: the screen doesn't need it.
     */
    public static function overview(): array {
        $detected = Policy_Model::get_detected();
        $services = [];

        foreach ( self::registry() as $id => $service ) {
            $services[] = [
                'id'          => $id,
                'name'        => $service['name'],
                'category'    => $service['category'],
                'privacy_url' => $service['privacy_url'],
                'detected'    => isset( $detected[ $id ] ),
                'sources'     => $detected[ $id ]['sources'] ?? [],
                'detected_at' => $detected[ $id ]['detected_at'] ?? 0,
            ];
        }

        /**
         * Filters the data for the Smart Policy admin screen.
         *
         * @param array $overview See below.
         */
        return (array) apply_filters( 'legal_pages_smart_policy_data', self::lock_state() + [
            'services'       => $services,
            'categories'     => self::categories(),
            'detected_count' => count( array_filter( array_column( $services, 'detected' ) ) ),
            'last_checked'   => Policy_Model::last_checked(),
            'has_scan'       => ! legal_pages_cookie_bar_in_legacy_pro() && '' !== Scan_Model::get_state()['finished_at'],
            'pages'          => Policy_Model::pages_with_shortcode(),
            'notice'         => null,
        ] );
    }

    /**
     * What stands between this site and the Pro controls. Sent with the overview,
     * not through LEGAL_PAGES: Pro prints that object too, and a Pro older than
     * this feature wouldn't carry the keys.
     *
     * `lock`: '' (unlocked), 'upgrade' (no Pro), 'license' (Pro without a valid
     * license) or 'update' (licensed, but this Pro doesn't have Smart Policy).
     */
    private static function lock_state(): array {
        /**
         * Filters whether the Pro parts of Smart Policy are available.
         *
         * @param bool $unlocked Legal Pages Pro turns this on with a valid license.
         */
        if ( apply_filters( 'legal_pages_smart_policy_unlocked', false ) ) {
            return [ 'lock' => '', 'pro_update_url' => '' ];
        }

        if ( ! defined( 'LEGAL_PAGES_PRO_VERSION' ) ) {
            return [ 'lock' => 'upgrade', 'pro_update_url' => '' ];
        }

        if ( 'valid' !== get_option( 'wplp_license_status', '' ) ) {
            return [ 'lock' => 'license', 'pro_update_url' => '' ];
        }

        return [
            'lock'           => 'update',
            'pro_update_url' => current_user_can( 'update_plugins' ) ? legal_pages_pro_update_url() : '',
        ];
    }

    /**
     * Run the given checks and save the result. Only results of those check types
     * are replaced, so a plugin-only run keeps what a cookie scan found. Without a
     * usable scan, the old cookie and scanner results stay as they are.
     *
     * Fires lp_smart_policy_detected when the list of services changes. Until the
     * first run of every check, $baseline is true: what's found then isn't "new",
     * it's what the site already had.
     *
     * @param string[] $types Rule types to check.
     * @return array The saved list.
     */
    public static function detect( array $types = self::QUICK_CHECKS ): array {
        $registry = self::registry();
        $old      = Policy_Model::get_detected();
        $now      = time();
        $baseline = 0 === Policy_Model::last_checked();

        $scan   = array_intersect( $types, [ 'cookie', 'scan' ] ) ? self::latest_scan() : [];
        $checks = $scan ? $types : array_values( array_diff( $types, [ 'cookie', 'scan' ] ) );

        $new = self::merge( array_intersect_key( $old, $registry ), self::find_matches( $registry, $checks, $scan ), $checks, $now );

        if ( $new !== $old ) {
            Policy_Model::save_detected( $new );
        }

        if ( ! array_diff( self::ALL_CHECKS, $types ) ) {
            Policy_Model::save_last_checked( $now );
        }

        if ( array_keys( $new ) !== array_keys( $old ) ) {
            /**
             * Fires when a detection run changes which services the site uses.
             *
             * @param array $new      Detected services after the run.
             * @param array $old      Detected services before the run.
             * @param bool  $baseline True while the first complete check hasn't happened.
             */
            do_action( 'lp_smart_policy_detected', $new, $old, $baseline );
        }

        return $new;
    }

    /**
     * What the latest cookie scan saw, or [] when there's no scan to trust: none
     * finished, the last one was stopped, or it reported nothing at all (the
     * iframe failed, or Pro's Clear emptied the table). Skipping those keeps a
     * failed scan from shrinking a policy.
     *
     * Rows are never deleted, so "seen in this scan" comes from last_seen.
     *
     * @return array { cookie: string[], scan: string[] }
     */
    private static function latest_scan(): array {
        if ( legal_pages_cookie_bar_in_legacy_pro() ) {
            return [];
        }

        $state = Scan_Model::get_state();
        if ( 'complete' !== $state['status'] ) {
            return [];
        }

        $rows = Scan_Model::rows_seen_since( $state['started_at'] );
        if ( ! $rows ) {
            return [];
        }

        $cookies = [];
        foreach ( $rows as $row ) {
            if ( 'cookie' === $row['storage'] && ! $row['ignored'] ) {
                $cookies[] = $row['name'];
            }
        }

        return [ 'cookie' => $cookies, 'scan' => array_keys( $state['detected_services'] ) ];
    }

    /**
     * @return array [ id => [ rule types that matched ] ]
     */
    private static function find_matches( array $registry, array $types, array $scan ): array {
        $matches = [];
        foreach ( $registry as $id => $service ) {
            foreach ( $service['detect'] as $rule ) {
                $type = $rule['type'] ?? '';
                // Once a type matched, the service's other rules of that type can't add anything.
                if ( in_array( $type, $types, true ) && ! isset( $matches[ $id ][ $type ] ) && self::rule_matches( $rule, $scan ) ) {
                    $matches[ $id ][ $type ] = $type;
                }
            }
        }
        return $matches;
    }

    private static function rule_matches( array $rule, array $scan ): bool {
        $value = (string) ( $rule['value'] ?? '' );

        switch ( $rule['type'] ) {
            case 'plugin':
                return self::is_plugin_active( $value );
            case 'option':
                return (string) get_option( (string) ( $rule['name'] ?? '' ) ) === $value;
            case 'cookie':
                return self::cookie_found( $value, $scan['cookie'] ?? [] );
            case 'scan':
                return in_array( $value, $scan['scan'] ?? [], true );
            case 'content':
                return '' !== $value && Policy_Model::content_contains( $value, self::content_post_types() );
            default:
                return false;
        }
    }

    /**
     * A trailing * in $pattern matches any ending (_ga_* for GA4's _ga_<ID>).
     */
    private static function cookie_found( string $pattern, array $names ): bool {
        if ( '*' !== substr( $pattern, -1 ) ) {
            return in_array( $pattern, $names, true );
        }

        $prefix = substr( $pattern, 0, -1 );
        foreach ( $names as $name ) {
            if ( '' !== $prefix && 0 === strpos( $name, $prefix ) ) {
                return true;
            }
        }
        return false;
    }

    private static function content_post_types(): array {
        return (array) apply_filters( 'lp_smart_policy_content_post_types', [ 'post', 'page' ] );
    }

    /**
     * is_plugin_active() only exists in admin by default; detection also runs from cron.
     */
    private static function is_plugin_active( string $file ): bool {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active( $file );
    }

    /**
     * Replace the $types sources in $detected with $matches. A service keeps its
     * detected_at for as long as it stays on the list, and leaves the list when it
     * has no source left. Sorted so an unchanged result compares equal to the old one.
     */
    private static function merge( array $detected, array $matches, array $types, int $now ): array {
        foreach ( $detected as $id => $service ) {
            $detected[ $id ]['sources'] = array_values( array_diff( (array) ( $service['sources'] ?? [] ), $types ) );
        }

        foreach ( $matches as $id => $sources ) {
            $detected[ $id ] = [
                'sources'     => array_merge( $detected[ $id ]['sources'] ?? [], array_values( $sources ) ),
                'detected_at' => $detected[ $id ]['detected_at'] ?? $now,
            ];
        }

        foreach ( $detected as $id => $service ) {
            if ( ! $service['sources'] ) {
                unset( $detected[ $id ] );
                continue;
            }
            sort( $detected[ $id ]['sources'] );
        }

        ksort( $detected );
        return $detected;
    }
}
