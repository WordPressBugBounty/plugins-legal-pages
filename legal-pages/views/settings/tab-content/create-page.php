<!--Tab: Create page content-->
<div class="wplp-container-lg wplp-ml-10">
    <h1 class="wplp-page-title">Add New Legal Page</h1>
    <div class="wplp-page-wrapper">
        <div class="wplp-page-wrapper__content">
            <form action="" method="post" id="addNewLegalPage">
                <div class="form-group wplp-mb-30">
                    <input type="text" name="lp_title" class="form-control" id="lp_title" placeholder="Enter a title or Choose a Template from the right">
                </div>
                <?php
                    $content = ( isset( $_POST['adl_lp_content']  ) ) ? wp_kses_post( $_POST['adl_lp_content'] ): 'Enter some content or Choose a template from the right side to edit and publish. After saving the page, you can find it in the "All Legal Pages" tab or you can find it under WordPress page menu';
                    wp_editor($content, 'adl_lp_content', array('editor_height' => 400));
                ?>
                <button type="submit" class="wplp-btn-primary wplp-mt-30"> Save Page</button>
                <?php wp_nonce_field( $ADL_LP->nonceAction(), $ADL_LP->nonceName()); ?>
            </form>
        </div> <!--ends .col-md-8 left column-->

        <div class="wplp-page-wrapper__sidebar">
            <div id="ChooseTemplate" class="postbox">
                <h3><span class='dashicons dashicons-admin-generic'></span> Choose a Template</h3>
                <?php
                global $wpdb;
                $nonce = wp_create_nonce('adl_LP_nonce_action');
                // show all templates
                $sql = 'SELECT * from '.$ADL_LP->template_table_name .' LIMIT 30';
                $results = $wpdb->get_results($sql);
                $html ='';
                foreach ( $results as $result   ) {
                    $html .= "<p><span class='dashicons dashicons-arrow-right
'></span> <a href='#' id='id-{$result->id}' data-id='{$result->id}' data-nonce='{$nonce}'>{$result->name} </a></p>";
                }
                echo $html;
                ?>
            </div>
            <!--Show upgrade feature notice-->
            <?php $ADL_LP->loadView('upgrade-template-lists'); ?>
        </div>  <!--ends .col-md-4 right column-->

    </div>
</div>



