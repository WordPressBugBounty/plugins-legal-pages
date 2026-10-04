<?php
namespace LegalPage\Controllers\Admin;

use LegalPage\Traits\Hook;

defined( 'ABSPATH' ) || exit;

/**
 * Legal Pages Pro before 2.1.0 ships its own cookie banner, so the free cookie
 * bar, scanner and routes stay off while it's active (see
 * legal_pages_cookie_bar_in_legacy_pro()). Ask the admin to update Pro.
 *
 * WordPress notices are hidden on the plugin's own screens, but the
 * `legal-pages-notice` class keeps this one visible (assets/admin/css/admin.css).
 */
class ProUpdateNotice {
    use Hook;

    public function __construct() {
        $this->action( 'admin_notices', [ $this, 'render' ] );
    }

    public function render() {
        if ( ! legal_pages_cookie_bar_in_legacy_pro() || ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        $version = defined( 'LEGAL_PAGES_PRO_VERSION' ) ? LEGAL_PAGES_PRO_VERSION : '';
        ?>
        <div class="notice notice-warning legal-pages-notice">
            <p>
                <strong><?php esc_html_e( 'Legal Pages', 'legal-pages' ); ?>:</strong>
                <?php
                printf(
                    /* translators: %s: installed Legal Pages Pro version */
                    esc_html__( 'Please update Legal Pages Pro (you have %s) to 2.1.0 or newer to use the cookie scanner and the new cookie settings. Your current cookie banner keeps working until then.', 'legal-pages' ),
                    esc_html( $version ? $version : __( 'an older version', 'legal-pages' ) )
                );
                ?>
            </p>
            <p>
                <a href="<?php echo esc_url( legal_pages_pro_update_url() ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Update Legal Pages Pro', 'legal-pages' ); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
