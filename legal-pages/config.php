<?php
/* Define All Constants */
global $wpdb;
if ( !defined('ABSPATH') ) { die('You do not have right to access this file directly'); } // prevents direct access
if ( !defined('ADL_LP_VERSION') ) { define('ADL_LP_VERSION', '1.2.1'); }
if ( !defined('ADL_LP_DIR') ) { define('ADL_LP_DIR', plugin_dir_path(__FILE__)); }
if ( !defined('ADL_LP_URL') ) { define('ADL_LP_URL', plugin_dir_url(__FILE__)); }
if ( !defined('ADL_LP_CLASS_DIR') ) { define('ADL_LP_CLASS_DIR', ADL_LP_DIR.'includes/classes/'); }
if ( !defined('ADL_LP_VIEWS_DIR') ) { define('ADL_LP_VIEWS_DIR', ADL_LP_DIR.'views/'); }
if ( !defined('ADL_LP_TEMPLATES_DIR') ) { define('ADL_LP_TEMPLATES_DIR', ADL_LP_DIR.'templates/'); }
if ( !defined('ADL_LP_ADMIN_ASSETS') ) { define('ADL_LP_ADMIN_ASSETS', ADL_LP_URL.'admin/assets/'); }
if ( !defined('ADL_LP_TEXTDOMAIN') ) { define('ADL_LP_TEXTDOMAIN', 'legal-pages'); }
if ( !defined('ADL_LP_LANG_DIR') ) { define('ADL_LP_LANG_DIR', dirname(plugin_basename( __FILE__ ) ) . '/languages'); }
if ( !defined('ADL_LP_PLUGIN_NAME') ) { define('ADL_LP_PLUGIN_NAME', 'Legal Pages'); }
if ( !defined('ADL_LP_TBL_Name') ) { define('ADL_LP_TBL_Name', $wpdb->prefix.'adl_lp_templates'); }





