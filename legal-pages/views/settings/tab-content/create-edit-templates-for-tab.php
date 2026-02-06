<!-- Tab: Create or Edit Page Template -->
<?php
$adl_lp_templates = !empty($args) ? $args : null; // data passed to the page via $args
?>
<div class="wplp-container-lg wplp-ml-10 wplp-pr-30" id="adl_legal_template_container">
    <h1 class="wplp-page-title">
        Legal Page Templates
        <a href="<?= get_admin_url() . 'admin.php?page=legal-page-template&action=new-template'; ?>" 
           id="createLPTemplateBtn" 
           class="wplp-btn-blue-royal wplp-ml-15">
           Add New Legal Page Template
        </a>
    </h1>

    <div class="wplp-page-wrapper">
        <table class="wplp-table">
            <thead>
                <tr>
                    <th>Template ID</th>
                    <th>Template Name</th>
                    <th style="width: 320px;">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php if (!empty($adl_lp_templates)) : ?>
                <?php foreach ($adl_lp_templates as $adl_lp) : ?>
                    <tr id="id-<?= esc_attr($adl_lp->id); ?>" data-id="<?= esc_attr($adl_lp->id); ?>">
                        <td><?= esc_html($adl_lp->id); ?></td>
                        <td>
                            <a href="<?= esc_url($ADL_LP->adl_lp_action_link($adl_lp->id)); ?>" title="Edit this">
                                <?= esc_html($adl_lp->name); ?>
                            </a>
                        </td>
                        <td style="width: 320px;">
                            <a class="wplp-btn wplp-btn-blue-royal" 
                               href="<?= esc_url($ADL_LP->adl_lp_action_link($adl_lp->id)); ?>" 
                               title="Edit Template">
                                <span class="glyphicon glyphicon-edit"></span> Edit
                            </a>

                            <a class="wplp-btn wplp-btn-danger deleteLegalTemplate" 
                               href="<?= esc_url($ADL_LP->adl_lp_action_link($adl_lp->id, 'delete')); ?>" 
                               data-id="<?= esc_attr($adl_lp->id); ?>" 
                               data-nonce="<?= wp_create_nonce('adl_LP_nonce_action'); ?>" 
                               title="Delete it permanently">
                                <span class="glyphicon glyphicon-trash"></span> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3">
                        <p>Looks like you have not created any legal page templates yet.</p>
                        <button class="btn button-primary" id="CreateALegalPage">
                            Create a New Legal Page
                        </button>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div> <!-- ends .wplp-page-wrapper -->
</div> <!-- ends .wplp-container-lg -->
