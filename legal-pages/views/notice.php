
<?php
if( ! empty( $_GET['wplp-dismiss-notice'] ) && 'true' == $_GET['wplp-dismiss-notice'] ) {
    update_option( 'wplp_legal_page_discount', true );
}

if( ! isset( $_GET['wplp-dismiss-notice'] ) ) { ?>

    <div class="wpcu-dashboard-notice">
        <a class="wpcu-dashboard-notice__dismiss wpcu-dashboard-notice__close" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wplp-dismiss-notice', 'true' ) ) ); ?>"><?php esc_html_e( 'x', 'woocommerce' ); ?></a>
        <img src="<?php echo esc_url( 'https://s12.gifyu.com/images/SuUGf.gif' ); ?>" alt="">
        <div class="wpcu-dashboard-notice__content">
            <h5><?php esc_html_e( 'EXCLUSIVE OFFER FOR LEGAL PAGES!', 'legal-pages' ); ?></h5>
            <p>
                <?php
                    $offer = '<strong>' . esc_html__( "Save 35% this summer with Legal Pages! ", "legal-pages" ) . '</strong>';
                    $text = esc_html__( "Secure your website's legal compliance and generate essential legal policies effortlessly, ensuring peace of mind and protecting your online presence. Don't miss this limited-time offer.", "legal-pages" );

                    echo $offer . $text;
                ?>
            </p>
            <a class="wpcu-dashboard-notice__dismiss wpcu-dashboard-notice__btn" target="_blank" href="<?php echo esc_url( 'https://wpwax.com/product/legal-pages-pro/#single-plugin-pricing-plan' ); ?>"><?php esc_html_e( 'Get Now!', 'legal-pages' ); ?></a>
        </div>
    </div>

<?php } ?>
