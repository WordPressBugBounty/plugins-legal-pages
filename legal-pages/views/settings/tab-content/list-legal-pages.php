<?php
// Get legal pages safely
$adl_lps = ! empty( $args->posts ) ? (array) $args->posts : array();
?>

<div class="wplp-container-fluid wplp-ml-10 wplp-pr-30" id="legalPageContainer">

    <h1 class="wplp-page-title">
        <?php esc_html_e( 'All Legal Pages', 'legal-pages' ); ?>

        <a href="<?php echo esc_url( admin_url( 'admin.php?page=createLegalPage' ) ); ?>"
           class="wplp-btn-blue-royal wplp-ml-15">
            <?php esc_html_e( 'Add New Legal Page', 'legal-pages' ); ?>
        </a>
    </h1>

    <div class="wplp-page-wrapper">
        <table class="wplp-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Page ID', 'legal-pages' ); ?></th>
                    <th><?php esc_html_e( 'Title', 'legal-pages' ); ?></th>
                    <th><?php esc_html_e( 'Author', 'legal-pages' ); ?></th>
                    <th><?php esc_html_e( 'Created At', 'legal-pages' ); ?></th>
                    <th>
                        <?php esc_html_e( 'Shortcode', 'legal-pages' ); ?>
                        <span class="wplp-tooltip" data-label="<?php esc_attr_e( 'You can use the shortcodes anywhere!', 'legal-pages' ); ?>">
                            <i class="glyphicon glyphicon-info-sign"></i>
                        </span>
                    </th>
                    <th style="width: 320px"><?php esc_html_e( 'Actions', 'legal-pages' ); ?></th>
                </tr>
            </thead>

            <tbody>
            <?php if ( ! empty( $adl_lps ) ) : ?>

                <?php foreach ( $adl_lps as $adl_lp ) : ?>
                    <tr id="id-<?php echo esc_attr( $adl_lp->ID ); ?>"
                        data-id="<?php echo esc_attr( $adl_lp->ID ); ?>">

                        <td><?php echo esc_html( $adl_lp->ID ); ?></td>

                        <td>
                            <a href="<?php echo esc_url( get_edit_post_link( $adl_lp->ID ) ); ?>"
                               title="<?php esc_attr_e( 'Edit this page', 'legal-pages' ); ?>">
                                <?php echo esc_html( $adl_lp->post_title ); ?>
                            </a>
                        </td>

                        <td>
                            <?php
                            echo wp_kses_post(
                                $ADL_LP->author_name_and_post_link(
                                    $adl_lp->post_author,
                                    'page'
                                )
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo esc_html( $ADL_LP->humanDate( $adl_lp->ID ) ); ?>
                        </td>

                        <td class="lp-selectable">
                            <span class="wplp-shortcode-text">
                                [wpwax_legal_page id="<?php echo esc_attr( $adl_lp->ID ); ?>"]
                            </span>

                            <span class="wplp-copy-shortcode" title="<?php esc_attr_e( 'Copy shortcode', 'text-domain' ); ?>">
                                <img 
                                    src="https://img.icons8.com/?size=100&id=86216&format=png&color=000000" 
                                    alt="<?php esc_attr_e( 'Copy', 'text-domain' ); ?>" 
                                    style="width:18px; height:18px; cursor:pointer;"
                                >
                            </span>
                        </td>



                        <td class="text-center">
                            <a class="wplp-btn wplp-btn-blue-royal"
                               href="<?php echo esc_url( get_permalink( $adl_lp->ID ) ); ?>"
                               target="_blank"
                               title="<?php esc_attr_e( 'View page', 'legal-pages' ); ?>">
                                <span class="glyphicon glyphicon-eye-open"></span>
                                <?php esc_html_e( 'View', 'legal-pages' ); ?>
                            </a>


                            <a class="wplp-btn wplp-btn-blue-royal"
                               href="<?php echo esc_url( get_edit_post_link( $adl_lp->ID ) ); ?>"
                               title="<?php esc_attr_e( 'Edit page', 'legal-pages' ); ?>">
                                <span class="glyphicon glyphicon-edit"></span>
                                <?php esc_html_e( 'Edit', 'legal-pages' ); ?>
                            </a>

                            <a href="#"
                               class="wplp-btn wplp-btn-danger moveToTrash"
                               data-id="<?php echo esc_attr( $adl_lp->ID ); ?>"
                               data-nonce="<?php echo esc_attr( wp_create_nonce( 'adl_LP_nonce_action' ) ); ?>"
                               title="<?php esc_attr_e( 'Move to Trash', 'legal-pages' ); ?>">
                                <span class="glyphicon glyphicon-trash"></span>
                                <?php esc_html_e( 'Trash', 'legal-pages' ); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else : ?>

                <tr>
                    <td colspan="6" class="text-center">
                        <p><?php esc_html_e( 'Looks like you have not created any legal pages yet.', 'legal-pages' ); ?></p>
                    </td>
                </tr>

            <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>
