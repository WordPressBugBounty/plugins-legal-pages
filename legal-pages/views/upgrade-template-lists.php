<?php
// Pro templates list
$pro_templates = [
    'Privacy Policy',
    'EU Privacy Policy',
    'Refund Policy',
    'Digital Goods Refund Policy',
    'Linking Policy',
    'External Links Policy',
    'Cookie Privacy Policy',
    'Facebook Policy',
    'COPPA - Children’s Online Privacy Policy',
    'Advertising Disclosures',
    'Confidentiality Disclosure',
    'Testimonials Disclosure',
    'Affiliate Disclosure',
    'Disclaimer',
    'Earnings Disclaimer',
    'Medical Disclaimer',
    'Terms of Use',
    'Forced Agreement to the Terms',
    'Affiliate Agreement',
    'Amazon Affiliate',
    'DMCA',
    'California Consumer Privacy Act (CCPA)',
    'Antispam',
    'Double Dart Cookie',
    'About Us',
    'California Privacy Rights',
    'End-user License Agreement',
    'GDPR Cookie Policy',
    'GDPR Privacy Policy',
];

$pro_url   = 'https://wpwax.com/product/legal-pages-pro/';
$check_img = esc_url( WPLP_URL . '/admin/assets/img/check.png' );
$promo_img = esc_url( WPLP_URL . '/admin/assets/img/premium.png' );
?>

<!-- Template for showing premium legal page template lists -->
<div class="adl-lp-upgrade-notice postbox">

    <div class="upgrade-notice-img">
        <img src="<?php echo $promo_img; ?>" alt="<?php esc_attr_e( 'Premium Templates', 'text-domain' ); ?>">
    </div>

    <h3>
        <?php esc_html_e( 'Following Templates are Available in', 'text-domain' ); ?>
        <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e( 'Pro Version', 'text-domain' ); ?>
        </a>
    </h3>

    <ul>
        <?php foreach ( $pro_templates as $template ) : ?>
            <li>
                <span>
                    <img src="<?php echo $check_img; ?>" alt="">
                </span>
                <?php echo esc_html( $template ); ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <a class="wplp-btn-primary wplp-mt-30"
       href="<?php echo esc_url( $pro_url ); ?>"
       target="_blank"
       rel="noopener noreferrer">
        <?php esc_html_e( 'And check out more features »', 'text-domain' ); ?>
    </a>

</div>
<!-- ends .adl-lp-upgrade-notice -->
