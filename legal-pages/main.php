<?php
if ( ! defined('ABSPATH') ) { die( 'Cheating? Direct access is not allowed !!!' ); } // die if the page is accessed directly

if ( ! class_exists('Adl_Legal_Pages') ) :
    final class Adl_Legal_Pages {

        private $req_wp_version = '4.0';
        public $objects = array();
        public $template_table_name;

        /**
         * Load all classes and instantiate them and flush rewrite rules
         */
        public function __construct( ){
            global $wpdb;
            // Don't let the class/plugin instantiate outside of WordPress
            if ( ! defined('ABSPATH') ) { die( 'Cheating? Direct access is not allowed !!!' ); }
            $this->template_table_name = $wpdb->prefix .'adl_lp_templates';
            // load all classes and its object
            $this->load_classes(ADL_LP_CLASS_DIR);
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes') );
            add_shortcode( 'wpwax_legal_page', array( $this, 'wpwax_legal_page' ) );
            if( empty( get_option('wplp_legal_page_discount') ) ) {
                add_action( 'admin_notices', array( $this, 'admin_notices') );
            } 
            // Initialize appsero tracking
            $this->init_appsero();
        }

        public function admin_notices() {
            global $pagenow, $ADL_LP;
            if ( 'index.php' == $pagenow || 'plugins.php' == $pagenow || 'adl-legal-pages' == $pagenow || 'all-legal-Pages' == $pagenow ) {
                $ADL_LP->loadView('notice');
            }
        }

        public function wpwax_legal_page( $atts, $content = null ) {
            ob_start();

            extract( shortcode_atts(array(
                        'id' => "",
                    ), $atts
                    )
                );
            if ( empty( $id ) ) {
                return;
            }
            $description = get_post_field( 'post_content', $id );
            echo $description;
            $true =  ob_get_clean();
		    return $true;
        }

        /**
         * Initialize appsero tracking.
         *
         * @see https://github.com/Appsero/client
         *
         * @return void
         */
        public function init_appsero() {
            if ( ! class_exists( '\Appsero\Client' ) ) {
                require_once (dirname(__FILE__) . '/includes/appsero/src/Client.php');
            }

            $client = new Appsero\Client( '122f8d75-c145-4d00-b71c-bbcf9089b987', 'Legal Pages', __FILE__ );

            // Active insights
            $client->insights()->init();
        }

        public function add_meta_boxes() {
            $legal_page = get_post_meta( get_the_ID(), 'is_adl_legal_page', true );

            if( ! empty( $legal_page ) ) {
                add_meta_box( 'wpwax_lp', __( 'Shortcode', 'legal-pages' ), array( $this, 'metabox_shortcode' ), 'page', 'side' );
            }
        }

        public function metabox_shortcode() {
            ?>
            <div>
                <div>[wpwax_legal_page id="<?php echo get_the_ID(); ?>"]</div>
            </div>
        <?php        

        }

        public function remove_plugin_data(  ) {
            global $wpdb;
            if ( get_option('adl_lp_misc')['delete_adl_lp_data'] ) {
                delete_option('adl_lp_excludePage');
                delete_option('adl_lp_general');
                delete_option('adl_lp_social');
                delete_option('adl_lp_accept_term');
                $wpdb->query("DROP TABLE IF EXISTS $this->template_table_name");
                delete_option('adl_demo_inserted');
                delete_option('adl_lp_misc');
            }
        }


        /**
         * Prepare plugin to work by creating custom table to store plugin data and set some default options
         */
        public function prepare_plugin() {
            global $wpdb;
            //get all template content
            $terms = file_get_contents(dirname(__FILE__) . '/templates/term-new.html');
            $privacy = file_get_contents(dirname(__FILE__) . '/templates/privacy.html');
            $dmca = file_get_contents(dirname(__FILE__) . '/templates/dmca.html');
            $pcp = file_get_contents(dirname(__FILE__) . '/templates/privacy-cookie-policy.html');
            $ccpa = file_get_contents(dirname(__FILE__) . '/templates/ccpa.html');

            // set default plugin's options
            add_option('adl_lp_excludePage', 'true');
            add_option('adl_lp_general', array());
            add_option('adl_lp_social', array());
            add_option('adl_lp_accept_term', 0); // show first time warning to the users to accept terms and set this value to 1 later
            $adl_lp_misc = array(
                'hide_lp_in_search' =>  0,
                'delete_adl_lp_data' =>  0,

            );
            add_option('adl_lp_misc', $adl_lp_misc);

            // prepare sql statements for creating legal page template tables.
            $charset_collate = $wpdb->get_charset_collate();
            $wp_adl_lp_template = "CREATE TABLE $this->template_table_name (
                          id int(11) unsigned NOT NULL AUTO_INCREMENT,
                          name text COLLATE utf8mb4_unicode_ci NOT NULL,
                          content longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                          type varchar(50) DEFAULT '',
                          PRIMARY KEY  (id)
                        ) ENGINE=InnoDB $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // include upgrade.php to access dbDelta()
            dbDelta( $wp_adl_lp_template ); // create wp_adl_lp_templates table
            if ( !get_option('adl_demo_inserted') ) { // if it is the first time user has installed the plugin then insert the demo template once only
                $wpdb->insert($this->template_table_name, array('name'=>'Terms of Use','content'=>$terms,'type'=>'1a2b3c4d5e6f7g8h9i'), array('%s','%s','%s'));
                $wpdb->insert($this->template_table_name, array('name'=>'Privacy Policy','content'=>$privacy,'type'=>'1a2b3c4d5e6f7g8h9i'), array('%s','%s','%s'));
                $wpdb->insert($this->template_table_name, array('name'=>'Cookie Privacy Policy','content'=>$pcp,'type'=>'1a2b3c4d5e6f7g8h9i'), array('%s','%s','%s'));
                $wpdb->insert($this->template_table_name, array('name'=>'DMCA','content'=>$dmca,'type'=>'10j'), array('%s','%s','%s'));
                update_option('adl_demo_inserted', true); // set this true to prevent users from inserting the save data again if user activate the plugin again.
                
            }
            if ( ! get_option('adl_ccpa_demo_inserted') ) {
                $wpdb->insert($this->template_table_name, array('name'=>'California Consumer Privacy Act (CCPA)','content'=>$ccpa,'type'=>'1a2b3c4d5e6f7g8h9i'), array('%s','%s','%s'));
                update_option('adl_ccpa_demo_inserted', true);
            }
        }

        /**
         * Load all classes from a given directory and store objects to the $this->$objects property
         * @param $dir Name of the directory where all classes resides
         * @return void
         */
        public function load_classes($dir){
            if (!file_exists($dir)) return;
            $objects = array();
            foreach (scandir($dir) as $file) {
                // if any file(eg.class files) found in the given dir then require it once and then create an object and add it to the objects array.
                if( preg_match( "/.php$/i" , $file ) ) {
                    require_once( $dir . $file );
                    $singleClass = str_replace( ".php", "", $file );
                    $objects[] = new $singleClass; // File name must match Class names in order to dynamically instantiate the class
                }
            }
            if($objects){
                foreach( $objects as $object )
                    $this->objects[] = $object;
            }
        }

        /**
         * Dynamically calls a method from this class if it is not public or from a subclass
         * @param   String  $name The Name of the Method to invoke on this class or subclass
         * @param   Mixed $args Dynamic list of arguments that will be passed to the method when it is called.
         *
         * @return mixed|void
         */
        public function __call( $name, $args ){
            if( !is_array($this->objects) ) return;
            foreach($this->objects as $object){
                if(method_exists($object, $name)){
                    return call_user_func_array(array($object, $name), $args);
                }
            }
        }

        /**
         * Initialize the plugin by hooking all actions and filters
         */
        public function init() {
            add_action('admin_init', array($this, 'warn_if_unsupported_wp'));
            // admin hooks and filter
            if ( is_admin() ) {
                add_action('plugins_loaded', array($this, 'load_textdomain' ) );
                add_filter( 'plugin_action_links_' . ADL_LP_BASE, array($this, 'add_plugin_action_link') );
            }
            // Enables shortcode for Widget
            add_filter('widget_text', 'do_shortcode');
        }

        /**
         * It loads html view
         * @param $name Name of the view to be loaded
         * @param array $args The array of arguments to be passed to the view
         * @return mixed
         */
        public function loadView( $name, $args = array() ) {
            global $ADL_LP, $post;
            include(ADL_LP_VIEWS_DIR.$name.'.php');
        }

        /**
         * It includes any files from the themes directory.
         * @param string $name  Name of the file from the Themes directory eg. 'style1/index'
         * @param array $args   Optional Values passed to the views to be used there.
         */
        public function loadTheme( $name, $args = array() ) {
            $name = "themes/{$name}";
            $this->loadView($name, $args);
        }

        /**
         * It adds links to the plugin activation page
         * @param $links The array of all default links of a plugin
         *
         * @return array The modified array of all links of a plugin
         */
        public function add_plugin_action_link($links) {
            unset($links['edit']); // protect editing the plugin
            $links[] = sprintf( '<a href="%s" title="%s">%s</a>', 'admin.php?page=adl-legal-pages', 'Add New Legal Pages', __( 'Add New', ADL_LP_TEXTDOMAIN ) );
            $links[] = sprintf( '<a href="%s" title="%s">%s</a>', 'admin.php?page=adl-legal-pages&tab=allPages', 'View All Legal Pages', __( 'View All', ADL_LP_TEXTDOMAIN ) );
            $links[] = sprintf( '<a href="%s" title="%s">%s</a>', 'https://wpwax.com/product/legal-pages-pro/', 'Upgrade to Pro', __( 'Upgrade', ADL_LP_TEXTDOMAIN ) );
            return $links;
        }

        /**
         *  It loads the text domain of the plugin
         * @return void
         */
        public function load_textdomain( ){
            load_plugin_textdomain(ADL_LP_TEXTDOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . '/languages/');
        }

        /**
         * It shows a warning to the user if they use older WordPress Version.
         * @return mixed
         */
        public function warn_if_unsupported_wp() {
            if ( $this->check_minimum_required_wp_version() ) {
                $wp_ver = ! empty( $GLOBALS['wp_version'] ) ? $GLOBALS['wp_version'] : '(undefined)';
                ?>
                <div class="error notice is-dismissible">
                    <p>
                        <?php
                            printf( __( ADL_LP_PLUGIN_NAME. 'requires WordPress version %1$s or newer. It appears that you are running %2$s. The plugin may not work properly.', ADL_LP_TEXTDOMAIN ),
                            $this->req_wp_version,
                            esc_html( $wp_ver )
                        );
                        echo '<br>';
                        printf( __( 'Please upgrade your WordPress installation or download latest version from <a href="%s" target="_blank" title="Download Latest WordPress">here</a>.', ADL_LP_TEXTDOMAIN ),
                            'https://wordpress.org/download/'
                        );
                        ?>
                    </p>
                </div>
                <?php return;
            }
        }

        /**
         * It checks minimum required version of WordPress we defined in $this->req_wp_version
         * @return mixed
         */
        private function check_minimum_required_wp_version() {
            include( ABSPATH . WPINC . '/version.php' ); // get an unmodified $wp_version
            return ( version_compare( $wp_version, $this->req_wp_version, '<' ) );
        }


    }


endif;

