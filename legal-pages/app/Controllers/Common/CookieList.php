<?php
namespace LegalPage\Controllers\Common;

use LegalPage\Traits\Hook;
use LegalPage\Models\Cookie_Scan;
use LegalPage\Models\Cookie_Bar;

defined( 'ABSPATH' ) || exit;

/**
 * [legal_pages_cookie_list] — renders the cookies found by the scanner, grouped
 * by consent category, for the cookie policy page. Ignored cookies are left out.
 *
 * Attributes:
 *   heading     Section heading; empty string hides it. Default "Cookies used on this website".
 *   categories  Comma-separated subset to show (functional,preferences,statistics,marketing,unclassified).
 *   show_manage "yes"/"no" — append a "change your consent" link when the banner is enabled. Default yes.
 *
 * Common (not Front) so the tag is also offered in the admin shortcode picker
 * via the `legal_pages_shortcodes` filter.
 */
class CookieList {

    use Hook;

    const CATEGORY_ORDER = [ 'functional', 'preferences', 'statistics', 'marketing', 'unclassified' ];

    public function __construct() {
        $this->action( 'plugins_loaded', [ $this, 'boot' ] );
    }

    public function boot() {
        if ( legal_pages_cookie_bar_in_legacy_pro() ) {
            return;
        }
        $this->add_shortcode( 'legal_pages_cookie_list', [ $this, 'render' ] );
        $this->filter( 'legal_pages_shortcodes', [ $this, 'add_to_picker' ] );
        $this->action( 'init', [ $this, 'register_manage_consent_fallback' ], 99 );
    }

    public function add_to_picker( $shortcodes ) {
        $shortcodes['legal_pages_cookie_list'] = '';
        // The consent banner and its shortcode are Legal Pages Pro's.
        if ( legal_pages_cookie_pro_unlocked() ) {
            $shortcodes['legal_pages_manage_consent'] = '';
        }
        return $shortcodes;
    }

    /**
     * [legal_pages_manage_consent] belongs to Legal Pages Pro's banner. Without
     * it (or without a license) render nothing, so pages never show the raw tag.
     */
    public function register_manage_consent_fallback() {
        if ( ! shortcode_exists( 'legal_pages_manage_consent' ) ) {
            $this->add_shortcode( 'legal_pages_manage_consent', '__return_empty_string' );
        }
    }

    /**
     * Group visible cookies by category, merging rows whose names differ only by a
     * per-site/per-user hash (e.g. wordpress_logged_in_<32 hex>) into one "name_*" row.
     */
    public static function grouped_cookies(): array {
        $groups = [];
        foreach ( Cookie_Scan::get_cookies() as $cookie ) {
            if ( $cookie['ignored'] ) {
                continue;
            }
            $name = preg_replace( '/[0-9a-f]{32}$/i', '*', $cookie['name'] );
            $key  = $name . '|' . $cookie['storage'];

            $category = in_array( $cookie['category'], self::CATEGORY_ORDER, true ) ? $cookie['category'] : 'unclassified';
            if ( isset( $groups[ $category ][ $key ] ) ) {
                continue;
            }
            $groups[ $category ][ $key ] = array_merge( $cookie, [ 'name' => $name ] );
        }

        $ordered = [];
        foreach ( self::CATEGORY_ORDER as $category ) {
            if ( ! empty( $groups[ $category ] ) ) {
                $ordered[ $category ] = array_values( $groups[ $category ] );
            }
        }
        return $ordered;
    }

    public function render( $atts ) {
        $atts = shortcode_atts(
            [
                'heading'     => __( 'Cookies used on this website', 'legal-pages' ),
                'categories'  => '',
                'show_manage' => 'yes',
            ],
            $atts,
            'legal_pages_cookie_list'
        );

        $groups = self::grouped_cookies();
        if ( '' !== trim( $atts['categories'] ) ) {
            $wanted = array_map( 'trim', explode( ',', strtolower( $atts['categories'] ) ) );
            $groups = array_intersect_key( $groups, array_flip( $wanted ) );
        }

        if ( ! $groups ) {
            // Visitors see nothing; admins get a pointer to the scanner.
            if ( ! current_user_can( 'manage_options' ) ) {
                return '';
            }
            return sprintf(
                '<p class="lp-cookie-list-empty"><em>%s</em></p>',
                esc_html__( 'Legal Pages: no cookies to list yet. Run a scan under Legal Pages → Cookie Bar → Website Cookie Scan. (Only administrators see this message.)', 'legal-pages' )
            );
        }

        $labels                 = Cookie_Bar::category_labels();
        $labels['unclassified'] = __( 'Other', 'legal-pages' );
        $descriptions           = Cookie_Bar::category_descriptions();
        $descriptions['unclassified'] = __( 'Cookies we have not yet assigned to a category.', 'legal-pages' );
        $storage_labels         = [
            'cookie'         => __( 'Cookie', 'legal-pages' ),
            'localstorage'   => __( 'Local storage', 'legal-pages' ),
            'sessionstorage' => __( 'Session storage', 'legal-pages' ),
        ];

        $state     = Cookie_Scan::get_state();
        $settings  = Cookie_Bar::get();
        $last_scan = $state['finished_at'] ? mysql2date( get_option( 'date_format' ), $state['finished_at'] ) : '';

        ob_start();
        ?>
        <div class="lp-cookie-list">
            <?php if ( '' !== $atts['heading'] ) : ?>
                <h3 class="lp-cookie-list-heading"><?php echo esc_html( $atts['heading'] ); ?></h3>
            <?php endif; ?>

            <?php if ( $last_scan ) : ?>
                <p class="lp-cookie-list-updated">
                    <?php
                    /* translators: %s: date of the last cookie scan */
                    echo esc_html( sprintf( __( 'This list was last updated on %s.', 'legal-pages' ), $last_scan ) );
                    ?>
                </p>
            <?php endif; ?>

            <?php foreach ( $groups as $category => $cookies ) : ?>
                <div class="lp-cookie-list-category lp-cookie-list-<?php echo esc_attr( $category ); ?>">
                    <h4><?php echo esc_html( $labels[ $category ] ?? $category ); ?></h4>
                    <?php if ( ! empty( $descriptions[ $category ] ) ) : ?>
                        <p><?php echo esc_html( $descriptions[ $category ] ); ?></p>
                    <?php endif; ?>
                    <div class="lp-cookie-list-table-wrap">
                        <table class="lp-cookie-list-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php esc_html_e( 'Name', 'legal-pages' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Service', 'legal-pages' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Purpose', 'legal-pages' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Retention', 'legal-pages' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Type', 'legal-pages' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $cookies as $cookie ) : ?>
                                    <tr>
                                        <td><code><?php echo esc_html( $cookie['name'] ); ?></code></td>
                                        <td><?php echo esc_html( $cookie['service'] ?: '—' ); ?></td>
                                        <td><?php echo esc_html( $cookie['purpose'] ?: '—' ); ?></td>
                                        <td><?php echo esc_html( $cookie['retention'] ?: '—' ); ?></td>
                                        <td><?php echo esc_html( $storage_labels[ $cookie['storage'] ] ?? $cookie['storage'] ); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ( 'no' !== strtolower( $atts['show_manage'] ) && ! empty( $settings['show_cookie_warning'] ) ) : ?>
                <p class="lp-cookie-list-manage">
                    <?php esc_html_e( 'You can change or withdraw your consent at any time:', 'legal-pages' ); ?>
                    <?php echo do_shortcode( '[legal_pages_manage_consent]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the shortcode. ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
