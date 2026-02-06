<?php
global $ADL_LP, $wpdb;

// Determine if editing or creating a new template
$is_edit = !empty($_GET['action']) && $_GET['action'] === 'edit';
$template_id = $is_edit ? absint($_GET['id']) : 0;
$template = null;

if ($is_edit) {
    $sql = $wpdb->prepare("SELECT * FROM {$ADL_LP->template_table_name} WHERE id=%d LIMIT 1", $template_id);
    $template = $wpdb->get_row($sql);
}

// List of available shortcodes
$shortcodes = [
    '[siteUrl]', '[siteName]', '[businessNiche]', '[phoneNumber]', '[emailAddress]',
    '[streetName]', '[countryName]', '[cityName]', '[stateName]', '[zipCode]',
    '[mailingAddress]', '[facebookUrl]', '[googlePlusUrl]', '[linkedInUrl]', '[twitterUrl]'
];
?>

<div class="wplp-container-lg wplp-ml-10">
    <h1 class="wplp-page-title">
        <?= $is_edit ? 'Edit Legal Page Template' : 'Create New Page Template'; ?>
    </h1>

    <div class="wplp-page-wrapper">
        <div class="wplp-page-wrapper__content">
            <form action="" method="post" id="<?= $is_edit ? 'editLegalTemplate' : 'addNewLegalTemplate'; ?>" 
                <?= $is_edit ? "data-id='{$template->id}' data-type='{$template->type}'" : ''; ?>>

                <div class="form-group">
                    <input type="text" name="lp_title" class="form-control" id="lp_title" 
                        placeholder="Enter a template name"
                        value="<?= $is_edit ? esc_attr($template->name) : ''; ?>">
                </div>

                <?php
                $content = $is_edit 
                    ? $template->content 
                    : (isset($_POST['adl_lp_template']) ? wp_kses_post($_POST['adl_lp_template']) : 'You can use shortcodes in the content like [siteUrl] etc.');

                wp_editor($content, 'adl_lp_template', ['editor_height' => 400]);
                ?>

                <button type="submit" class="wplp-btn-primary wplp-mt-30">
                    <?= $is_edit ? 'Update Template' : 'Save Template'; ?>
                </button>

                <?php wp_nonce_field($ADL_LP->nonceAction(), $ADL_LP->nonceName()); ?>
            </form>
        </div>

        <div class="wplp-page-wrapper__sidebar__content edit-legal-postbox">
            <h4 class="wplp-page-wrapper__sidebar__title">Shortcodes that you may use:</h4>
            <ul class="wplp-shortcode-list">
                <?php foreach ($shortcodes as $code): ?>
                    <li class="wplp-shortcode-item">
                        <span class="wplp-shortcode-text"><?= $code; ?></span>
                        <button class="wplp-copy-shortcode" title="Copy shortcode">📋</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

