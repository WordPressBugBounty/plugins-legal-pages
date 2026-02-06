<?php
$pro_features = [
    '26 Legal Page Templates.',
    'Ability to show a beautiful and customizable EU cookie agreement notice button. You can decide how the cookie agreement is shown to users.',
    'Ability to display the cookie agreement bar anywhere on the page.',
    'Option to show the cookie notice as a popup.',
    'Full customization of cookie styles and text.',
    'Ability to show different types of popups with legal messages.',
    'Ability to lock down any content, page, or post and allow access only after users accept your terms and conditions.',
    'And many more.',
];

$check_img = esc_url( WPLP_URL . '/admin/assets/img/check.png' );
$promo_img = esc_url( WPLP_URL . '/admin/assets/img/premium.png' );
?>

<div class="adl-lp-upgrade-notice postbox">

    <div class="upgrade-notice-img">
        <img src="<?php echo $promo_img; ?>" alt="<?php esc_attr_e( 'Pro Features', 'text-domain' ); ?>">
    </div>

    <h3><?php esc_html_e( 'Main Features of the Pro Version', 'text-domain' ); ?></h3>

    <ul>
        <?php foreach ( $pro_features as $feature ) : ?>
            <li>
                <span>
                    <img src="<?php echo $check_img; ?>" alt="">
                </span>
                <?php echo esc_html( $feature ); ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="wplp-mt-30">
        <?php
        echo wp_kses_post(
            $ADL_LP->upgrade_to_pro_link(
                'adl-legal-pages-pro',
                __( 'Get the Pro Version Now!', 'text-domain' )
            )
        );
        ?>
    </div>

</div>
