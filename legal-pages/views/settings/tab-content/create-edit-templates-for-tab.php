<!--Tab: Create or edit page template-->
<?php

$adl_lp_templates = (!empty($args)) ? $args : null; // data are passed to pages wrapped in the $args variable.

?>
<div class="wplp-container-lg wplp-ml-10 wplp-pr-30" id="adl_legal_template_container">
    <h1 class="wplp-page-title">Legal Page Templates <a href="<?= get_admin_url() . 'admin.php?page=legal-page-template&action=new-template'; ?>" id="createLPTemplateBtn" class="wplp-btn-blue-royal wplp-ml-15">Add New Legal Page Template </a></h1>
    <div class="wplp-page-wrapper">

            <table class="wplp-table">
                <thead>

                <tr>
                    <th>Template id</th>
                    <th>Template Name</th>
                    <th style="width: 320px">Action</th>
                </tr>
                </thead>

                <?php
                if ( ! empty( $adl_lp_templates ) ) {


                    foreach( $adl_lp_templates as $adl_lp ) {
                    ?>
                        <tr id="id-<?= $adl_lp->id; ?>" data-id="<?= $adl_lp->id; ?>" >
                            <td><?= $adl_lp->id; ?></td>
                            <td><a href="<?php $ADL_LP->adl_lp_action_link($adl_lp->id); ?>" title="Edit this"> <?= $adl_lp->name; ?>  </a></td>
                            <td style="width: 320px"><a class='wplp-btn wplp-btn-blue-royal' href="<?php $ADL_LP->adl_lp_action_link($adl_lp->id); ?>" title="Edit Template"><span class="glyphicon glyphicon-edit"></span> Edit</a> <a href="<?php $ADL_LP->adl_lp_action_link($adl_lp->id, 'delete'); ?>" data-id="<?= $adl_lp->id; ?>" data-nonce ="<?php echo wp_create_nonce( 'adl_LP_nonce_action' ); ?>" class="wplp-btn wplp-btn-danger deleteLegalTemplate" title="Delete it permanently"><span class="glyphicon glyphicon-trash"></span> Delete</a></td>
                        </tr>

                    <?php  }
                }else {
                    echo '<tr> <td> <p> Looks like you have not created any legal pages yet.</p> <button class="btn button-primary" id="CreateALegalPage">Create a new Legal Page</button> </td> </tr>';
                }
                ?>
            </table> <!--ends .table table-striped-->

    </div> <!--ends .row-->
</div><!--ends .container-->






