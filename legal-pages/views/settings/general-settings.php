<?php
/* Example default data (commented)
$ars = array(
    'siteUrl' =>  'http://wordpress.stackexchange.com/',
    'siteName' =>  'Adl Plugin',
    'businessNiche' =>  'WordPress Plugin',
    'phoneNumber' =>  '017111111111',
    'emailAddress' =>  'my@gmail.com',
    'streetName' =>  'my address',
    'countryName' =>  'Bangladesh',
    'cityName' =>  'Sylhet Sadar',
    'stateName' =>  'Sylhet',
    'zipCode' =>  '3100',
    'mailingAddress' =>  'House no 55, Flat no 1A, New Lane, Dhaka.',
);
*/

// Fetch and sanitize saved data
$homeTabData = array_merge(
    get_option('adl_lp_general', array()),
    get_option('adl_lp_social', array()),
    get_option('adl_lp_popup', array()),
    get_option('adl_lp_misc', array()),
    get_option('adl_lp_cookie', array())
);

// General Info
$siteUrl        = !empty($homeTabData['siteUrl']) ? esc_url($homeTabData['siteUrl']) : get_site_url();
$siteName       = !empty($homeTabData['siteName']) ? esc_attr($homeTabData['siteName']) : get_option('blogname');
$businessNiche  = !empty($homeTabData['businessNiche']) ? esc_attr($homeTabData['businessNiche']) : '';
$phoneNumber    = !empty($homeTabData['phoneNumber']) ? esc_attr($homeTabData['phoneNumber']) : '';
$emailAddress   = !empty($homeTabData['emailAddress']) ? esc_attr($homeTabData['emailAddress']) : '';
$streetName     = !empty($homeTabData['streetName']) ? esc_attr($homeTabData['streetName']) : '';
$countryName    = !empty($homeTabData['countryName']) ? esc_attr($homeTabData['countryName']) : '';
$cityName       = !empty($homeTabData['cityName']) ? esc_attr($homeTabData['cityName']) : '';
$stateName      = !empty($homeTabData['stateName']) ? esc_attr($homeTabData['stateName']) : '';
$zipCode        = !empty($homeTabData['zipCode']) ? esc_attr($homeTabData['zipCode']) : '';
$mailingAddress = !empty($homeTabData['mailingAddress']) ? esc_attr($homeTabData['mailingAddress']) : '';

// Social Info
$facebookUrl   = !empty($homeTabData['facebookUrl']) ? esc_url($homeTabData['facebookUrl']) : '';
$googlePlusUrl = !empty($homeTabData['googlePlusUrl']) ? esc_url($homeTabData['googlePlusUrl']) : '';
$linkedInUrl   = !empty($homeTabData['linkedInUrl']) ? esc_url($homeTabData['linkedInUrl']) : '';
$twitterUrl    = !empty($homeTabData['twitterUrl']) ? esc_url($homeTabData['twitterUrl']) : '';

// Popup options
$disabled_pop_notice_title = isset($homeTabData['disabled_pop_notice_title']) ? absint($homeTabData['disabled_pop_notice_title']) : 0;
$popup_notice_title        = isset($homeTabData['popup_notice_title']) ? sanitize_text_field($homeTabData['popup_notice_title']) : '';
$agreement_text            = isset($homeTabData['agreement_text']) ? sanitize_text_field($homeTabData['agreement_text']) : '';
$accept_btn_text           = isset($homeTabData['accept_btn_text']) ? sanitize_text_field($homeTabData['accept_btn_text']) : '';
$popup_width               = isset($homeTabData['popup_width']) ? sanitize_text_field($homeTabData['popup_width']) : '90%';
$popup_height              = isset($homeTabData['popup_height']) ? sanitize_text_field($homeTabData['popup_height']) : '90vh';
$user_can_close_popup      = isset($homeTabData['user_can_close_popup']) ? absint($homeTabData['user_can_close_popup']) : 0;
$disabled_pop_js_css       = isset($homeTabData['disabled_pop_js_css']) ? absint($homeTabData['disabled_pop_js_css']) : 0;

// Miscellaneous options
$hide_delete       = get_option('adl_lp_misc');
$hide_lp_in_search = !empty($hide_delete['hide_lp_in_search']) ? absint($hide_delete['hide_lp_in_search']) : 0;
$delete_adl_lp_data= !empty($hide_delete['delete_adl_lp_data']) ? absint($hide_delete['delete_adl_lp_data']) : 0;

$general_fields = [
    'siteUrl'        => 'Your Site URL',
    'siteName'       => 'Site / Business Name',
    'businessNiche'  => 'Business Niche',
    'phoneNumber'    => 'Phone Number',
    'emailAddress'   => 'Email Address',
    'streetName'     => 'Street Name',
    'countryName'    => 'Country Name',
    'cityName'       => 'City',
    'stateName'      => 'State',
    'zipCode'        => 'Zip/Postal Code',
    'mailingAddress' => 'Complete Mailing Address'
];

$social_fields = [
    'facebookUrl'   => 'Facebook Page/Profile URL',
    'googlePlusUrl' => 'Google Plus URL',
    'linkedInUrl'   => 'LinkedIn Profile URL',
    'twitterUrl'    => 'Twitter URL'
];

?>

<div class="wplp-container-sm wplp-ml-10">
    <h1 class="wplp-page-title">Settings</h1>

    <div class="row">
        <div class="col-sm-12">

            <!-- Tab Menu -->
            <div class="wplp-top-tab-nav btn-pref btn-group btn-group-justified btn-group-lg" role="group">
                <div class="btn-group" role="group">
                    <button type="button" id="stars" class="btn btn-primary" href="#tab1" data-toggle="tab">
                        <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                        <div class="wplp-top-tab-nav__text hidden-xs">General Information</div>
                    </button>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" id="following" class="btn btn-default" href="#tab5" data-toggle="tab">
                        <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>
                        <div class="wplp-top-tab-nav__text hidden-xs">Miscellaneous Settings</div>
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="wplp-tab-content well">
                <div class="tab-content">

                    <!-- General Info Tab -->
                    <div class="tab-pane " id="tab1">
                        <div class="wplp-tab-content-inner">
                            <h2 class="wplp-subtitle wplp-pl-40">General Information</h2>
                            <form class="form-horizontal" method="post" id="adl_lp_gs_form">

                                <?php                                

                                foreach ($general_fields as $key => $label):
                                    $value = ${$key}; ?>
                                    <div class="form-group">
                                        <label for="<?= esc_attr( $key ); ?>" class="col-sm-3 control-label"><?= esc_html( $label ); ?></label>

                                        <div class="col-sm-7">
                                            <input 
                                                type="text" 
                                                name="<?= esc_attr( $key ); ?>" 
                                                class="form-control" 
                                                id="<?= esc_attr( $key ); ?>" 
                                                value="<?= esc_attr( $value ); ?>" 
                                                placeholder="<?= esc_attr( $label ); ?>"
                                            >
                                        </div>

                                        <div class="col-sm-2 control-label wplp-shortcode-wrapper">
                                            <span class="wplp-shortcode-text">[<?= esc_html( $key ); ?>]</span>
                                            <button type="button" class="wplp-copy-shortcode" title="<?php esc_attr_e( 'Copy shortcode', 'text-domain' ); ?>">
                                                <img 
                                                    src="https://img.icons8.com/?size=100&id=86216&format=png&color=000000" 
                                                    alt="<?php esc_attr_e( 'Copy', 'text-domain' ); ?>" 
                                                    style="width:18px; height:18px;"
                                                >
                                            </button>
                                        </div>
                                    </div>

                                <?php endforeach; ?>

                                <h2 class="wplp-subtitle wplp-mt-80 wplp-pl-40">Social Information</h2>

                                <?php
                                

                                foreach ($social_fields as $key => $label):
                                    $value = ${$key}; ?>
                                    <div class="form-group">
                                        <label for="<?= $key; ?>" class="col-sm-3 control-label"><?= $label; ?></label>
                                        <div class="col-sm-7">
                                            <input type="text" name="<?= $key; ?>" class="form-control" id="<?= $key; ?>" value="<?= $value; ?>" placeholder="<?= $label; ?>">
                                        </div>
                                        <div class="col-sm-2 control-label wplp-shortcode-wrapper">
                                            <span class="wplp-shortcode-text">[<?= $key; ?>]</span>
                                            <button type="button" class="wplp-copy-shortcode" title="Copy shortcode"><img 
                                                    src="https://img.icons8.com/?size=100&id=86216&format=png&color=000000" 
                                                    alt="<?php esc_attr_e( 'Copy', 'text-domain' ); ?>" 
                                                    style="width:18px; height:18px;"
                                                ></button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="wplp-tab-content-action wplp-mt-30">
                                    <button type="submit" id="adl_lp_g_settings" class="wplp-btn-primary btn btn-primary">Save</button>
                                    <button type="reset" id="adl_lp_g_settings_reset" class="wplp-btn wplp-btn-white btn btn-primary">Reset Fields</button>
                                    <button type="reset" id="adl_lp_g_settings_resetdb" class="wplp-btn wplp-btn-white btn btn-primary">Reset Saved Data</button>
                                </div>

                                <?php wp_nonce_field( $ADL_LP->nonceAction(), $ADL_LP->nonceName());?>
                            </form>
                        </div>
                    </div>

                    <!-- Miscellaneous Tab -->
                    <div class="tab-pane " id="tab5">
                        <div class="wplp-tab-content-inner">
                            <form class="form-horizontal" method="post" id="misc_form">

                                <div class="form-group">
                                    <label for="hide_lp_in_search" class="wplp-switch-label wplp-mb-20">
                                        <input type="checkbox" class="form-control wplp-switch" name="hide_lp_in_search" id="hide_lp_in_search" value="1" <?php checked( $hide_lp_in_search ); ?>>
                                        <span class="wplp-input-switch">
                                            <span class="wplp-input-switch__yes">Yes</span>
                                            <span class="wplp-input-switch__no">No</span>
                                        </span>
                                        <span class="wplp-ml-10 wplp-checkbox-text">Exclude legal pages in Search Result</span>
                                    </label>

                                    <label for="delete_adl_lp_data" class="wplp-switch-label">
                                        <input type="checkbox" class="form-control wplp-switch" name="delete_adl_lp_data" id="delete_adl_lp_data" value="1" <?php checked( $delete_adl_lp_data ); ?>>
                                        <span class="wplp-input-switch">
                                            <span class="wplp-input-switch__yes">Yes</span>
                                            <span class="wplp-input-switch__no">No</span>
                                        </span>
                                        <span class="wplp-ml-10 wplp-color-damger">Warning!! Check this to remove plugin's data on uninstall.</span>
                                    </label>
                                </div>

                                <div class="wplp-tab-content-action">
                                    <button type="submit" class="wplp-btn-primary btn btn-primary" id="misc_setting_submit">Save</button>
                                </div>
                                <?php wp_nonce_field( $ADL_LP->nonceAction(), $ADL_LP->nonceName() );?>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
