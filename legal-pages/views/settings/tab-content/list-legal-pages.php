<?php $adl_lps = ! empty( $args->posts ) ? $args->posts : null; // data are passed to pages wrapped in the $args variable.

?>
<div class="wplp-container-fluid wplp-ml-10 wplp-pr-30" id="legalPageContainer">
    <h1 class="wplp-page-title"><?php esc_html_e( 'All Legal Pages', 'legal-pages' ); ?> <a href="<?php echo get_admin_url() ."admin.php?page=createLegalPage"; ?>" class="wplp-btn-blue-royal wplp-ml-15"><?php esc_html_e( 'Add New Legal Page', 'legal-pages' ); ?></a></h1>
    <div class="wplp-page-wrapper">
        <table class="wplp-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Page ID', 'legal-pages' ); ?></th>
                    <th><?php esc_html_e( 'Title', 'legal-pages' ); ?></th>
                    <th><?php esc_html_e( 'Author', 'legal-pages' ); ?></th>
                    <th><?php esc_html_e( 'Created at', 'legal-pages' ); ?></th>
                    <th><?php esc_html_e( 'Shortcode', 'legal-pages' ); ?><span class="wplp-tooltip" data-label="You can use the shortcodes anywhere!"><i class="glyphicon glyphicon-info-sign"></i></span></th>
                    <th style="width: 320px"><?php esc_html_e( 'Action', 'legal-pages' ); ?></th>
                </tr>
            </thead>

            <?php
            $adl_lps = !empty($adl_lps) ? $adl_lps : array();
            if ( count($adl_lps) ) {
            foreach ($adl_lps as $adl_lp) {    ?>
                <tr id="id-<?php echo esc_attr( $adl_lp->ID ); ?>" data-id="<?php echo esc_attr( $adl_lp->ID ); ?>" >
                    <td><?php echo esc_html( $adl_lp->ID ); ?></td>
                    <td><a href="<?php echo esc_url( get_edit_post_link( $adl_lp->ID ) ); ?>" title="Edit this"> <?php echo esc_html( $adl_lp->post_title ); ?>  </a></td>
                    <td><?php echo wp_kses_post( $ADL_LP->author_name_and_post_link( $adl_lp->post_author, 'page' ) ); ?></td>
                    <td><?php echo  esc_html( $ADL_LP->humanDate( $adl_lp->ID ) ); ?></td>
                    <td class="lp-selectable">[wpwax_legal_page id="<?php echo esc_attr( $adl_lp->ID ); ?>"]</td>
                    <td class="text-center"><a class='wplp-btn wplp-btn-blue-royal' href="<?php  echo esc_url( $adl_lp->guid ); ?>" title="View page"><span class="glyphicon glyphicon-eye-open"></span> <?php esc_html_e( 'View', 'legal-pages' ); ?></a> <a class='wplp-btn wplp-btn-blue-royal' href="<?php echo get_edit_post_link( $adl_lp->ID ); ?>" title="Edit page"><span class="glyphicon glyphicon-edit"></span> <?php esc_html_e( 'Edit', 'legal-pages' ); ?></a> <a href="#" data-id="<?php echo esc_attr( $adl_lp->ID ); ?>" data-nonce="<?php echo wp_create_nonce( 'adl_LP_nonce_action' ); ?>" class="wplp-btn wplp-btn-danger moveToTrash" title="Move to Trash"><span class="glyphicon glyphicon-trash"></span> <?php esc_html_e( 'Trash', 'legal-pages' ); ?></a></td>
                </tr>
            <?php  }
            } else {
                echo '<tr> <td> <p> Looks like you have not created any legal pages yet.</p>  </td> </tr>';
            }
            ?>
        </table> <!--ends .table wplp-table-->
    </div> <!--ends .row-->
</div><!--ends .container-->
