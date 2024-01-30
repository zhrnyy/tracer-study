<?php

namespace FormVibes;

use  FormVibes\API\FV_JWT_Auth ;
use  FormVibes\Classes\Forms ;
use  FormVibes\Classes\Utils ;
use  FormVibes\Classes\Export ;
use  FormVibes\Classes\DbTables ;
use  FormVibes\Classes\Settings ;
use  FormVibes\Integrations\Cf7 ;
use  FormVibes\Classes\Capabilities ;
use  FormVibes\Classes\Permissions ;
use  FormVibes\Integrations\Caldera ;
use  FormVibes\Integrations\WpForms ;
use  FormVibes\Integrations\Elementor ;
use  FormVibes\Integrations\NinjaForms ;
use  FormVibes\Integrations\GravityForms ;
use  FormVibes\Integrations\BeaverBuilder ;
use  FormVibes\Integrations\Bricks ;
use  FormVibes\Integrations\WsForm ;
use  WP_Query ;
/**
 * The class for bootstrapping the plugin
 */

if ( !class_exists( 'FormVibes\\Plugin' ) ) {
    class Plugin
    {
        /**
         * The instance of the class.
         * @var null|object $instance
         *
         */
        private static  $instance = null ;
        /**
         * Forms
         * @var array $_forms
         *
         */
        private static  $_forms = null ;
        /**
         * Current tab
         * @var null|object $instance
         *
         */
        private  $current_tab = '' ;
        /**
         * If notice needed to show
         * @var null|object $instance
         *
         */
        private static  $show_notice = true ;
        /**
         * Capabilities of user
         * @var null|object $instance
         *
         */
        public static  $capabilities = null ;
        /**
         * The instaciator of the class.
         *
         * @access public
         * @since 1.4.4
         * @return @var $instance
         */
        public static function instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        /**
         * The constructor of the class.
         *
         * @access private
         * @since 1.4.4
         * @return void
         */
        private function __construct()
        {
            if ( file_exists( WPV_FV__PATH . 'inc/pro/bootstrap.php' ) ) {
                // pro
                require_once WPV_FV__PATH . 'inc/pro/bootstrap.php';
            }
            add_action(
                'admin_enqueue_scripts',
                [ $this, 'admin_scripts' ],
                10,
                1
            );
            // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
            // add_action( 'rest_api_init', [ $this, 'init_rest_api' ] );
            // add_action( 'wp_loaded', [ 'FormVibes\Classes\DbTables', 'fv_plugin_activated' ] );
            // add_action( 'plugins_loaded', [ 'FormVibes\Classes\DbTables', 'fv_plugin_activated' ] );
            add_filter( 'plugin_action_links_' . plugin_basename( WPV_FV__PATH . 'form-vibes.php' ), [ $this, 'settings_link' ], 10 );
            if ( !function_exists( 'is_plugin_active' ) ) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            if ( is_plugin_active( 'caldera-forms/caldera-core.php' ) ) {
                new Caldera();
            }
            if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
                new Cf7();
            }
            if ( is_plugin_active( 'elementor-pro/elementor-pro.php' ) || is_plugin_active( 'pro-elements/pro-elements.php' ) ) {
                new Elementor();
            }
            if ( is_plugin_active( 'bb-plugin/fl-builder.php' ) ) {
                new BeaverBuilder();
            }
            $theme = wp_get_theme();
            if ( $theme->name === 'Bricks' || $theme->name === 'bricks' || $theme->template === 'Bricks' || $theme->template === 'bricks' ) {
                new Bricks();
            }
            // check if ninja forms is activated.
            if ( is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
                new NinjaForms();
            }
            // check if wp forms is activated.
            if ( is_plugin_active( 'wpforms-lite/wpforms.php' ) || is_plugin_active( 'wpforms/wpforms.php' ) ) {
                new WpForms();
            }
            // check if gravity forms is activated.
            if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
                new GravityForms();
            }
            // check if ws forms is activated.
            if ( is_plugin_active( 'ws-form/ws-form.php' ) || is_plugin_active( 'ws-form-pro/ws-form.php' ) ) {
                new WsForm();
            }
            Settings::instance();
            add_action( 'admin_menu', [ $this, 'admin_menu' ], 9 );
            add_action( 'admin_menu', [ $this, 'admin_menu_after_pro' ] );
            add_filter(
                'plugin_row_meta',
                [ $this, 'plugin_row_meta' ],
                10,
                2
            );
            add_action( 'init', [ $this, 'fv_db_update' ] );
            $this->fv_title = apply_filters( 'formvibes/fv_title', 'Form Vibes' );
            self::$_forms = Forms::instance();
            new Export( '' );
            $this->load_modules();
            self::$capabilities = Capabilities::instance();
            add_filter( 'formvibes/global/settings', [ $this, 'set_table_size_limits' ] );
        }
        
        /**
         * Set the frontend table size limits.
         *
         * Fired by the 'formvibes/global/settings' filter.
         *
         * @access public
         * @param array $settings The settings array.
         * @since 1.4.4
         * @return array $settings
         */
        public function set_table_size_limits( $settings )
        {
            $settings['table_size_limits'] = Utils::get_table_size_limits();
            return $settings;
        }
        
        /**
         * Autoload the classes.
         *
         *
         * @access public
         * @param $class
         * @since 1.4.4
         * @return array $settings
         */
        public function autoload( $class )
        {
            if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
                return;
            }
            
            if ( !class_exists( $class ) ) {
                $filename = strtolower( preg_replace( [
                    '/^' . __NAMESPACE__ . '\\\\/',
                    '/([a-z])([A-Z])/',
                    '/_/',
                    '/\\\\/'
                ], [
                    '',
                    '$1-$2',
                    '-',
                    DIRECTORY_SEPARATOR
                ], $class ) );
                $filename = WPV_FV__PATH . '/inc/' . $filename . '.php';
                if ( is_readable( $filename ) ) {
                    include $filename;
                }
            }
        
        }
        
        /**
         * Register the script for the admin area.
         *
         * Fired by `admin_enqueue_scripts` action.
         *
         * @access public
         * @return void
         */
        public function admin_scripts()
        {
            $screen = get_current_screen();
            wp_enqueue_style(
                'fv-style-css',
                WPV_FV__URL . 'assets/css/styles.css',
                [],
                WPV_FV__VERSION
            );
            wp_enqueue_script(
                'fv-js',
                WPV_FV__URL . 'assets/script/index.js',
                [],
                WPV_FV__VERSION,
                true
            );
            wp_localize_script( 'fv-js', 'fvGlobalVar', Utils::get_global_settings() );
            wp_enqueue_style( 'wp-components' );
            if ( 'form-vibes_page_fv-db-settings' === $screen->id ) {
                $this->load_settings_scripts();
            }
        }
        
        /**
         * Load the settings scripts.
         *
         * @access private
         * @return void
         */
        private function load_settings_scripts()
        {
            wp_enqueue_script(
                'setting-js',
                WPV_FV__URL . 'assets/dist/settings.js',
                [ 'wp-components' ],
                WPV_FV__VERSION,
                true
            );
            wp_enqueue_style(
                'setting-css',
                WPV_FV__URL . 'assets/dist/settings.css',
                '',
                WPV_FV__VERSION
            );
        }
        
        /**
         * Get plugin row meta
         *
         * Fired by `plugin_row_meta` action.
         *
         * @access public
         * @return array
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file )
        {
            
            if ( WPV_FV_PLUGIN_BASE === $plugin_file ) {
                $row_meta = [
                    'docs'    => '<a href="https://wpvibes.link/go/fv-all-docs-pp/" aria-label="' . esc_attr( __( 'View Documentation', 'wpv-fv' ) ) . '" target="_blank">' . __( 'Read Docs', 'wpv-fv' ) . '</a>',
                    'support' => '<a href="https://wpvibes.link/go/form-vibes-support/" aria-label="' . esc_attr( __( 'Support', 'wpv-fv' ) ) . '" target="_blank">' . __( 'Need Support', 'wpv-fv' ) . '</a>',
                ];
                $plugin_meta = array_merge( $plugin_meta, $row_meta );
            }
            
            return $plugin_meta;
        }
        
        public function admin_menu()
        {
        }
        
        /**
         * Menus after pro.
         *
         * Fired by `admin_menu` action.
         *
         * @access public
         * @return void
         */
        public function admin_menu_after_pro()
        {
            $caps = self::$capabilities->get_caps();
            $this->cap_fv_view_logs = apply_filters( 'formvibes/cap/view_fv_logs', 'publish_posts' );
            add_submenu_page(
                'fv-leads',
                'Form Vibes Settings',
                'Settings',
                'manage_options',
                'fv-db-settings',
                [ $this, 'fv_db_settings' ],
                5
            );
            // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
            // add_submenu_page( 'fv-leads', 'Form Vibes Settings', 'Settings', 'manage_options', 'fv-db-settings', [ $this, 'fv_db_settings' ], 5 );
        }
        
        /**
         * Upgrade to pro notice
         *
         * Fired by `plugin_row_meta` action.
         *
         * @access public
         * @return array
         */
        public function update_pro_notice()
        {
            ?>
			<div class="fv-plugin-error error">
				<p>
					You are using an older version of <b>Form Vibes Pro.</b>
					Kindly <a href="plugins.php">update</a> to latest version.
				</p>
			</div>
			<?php 
        }
        
        /**
         * Render the root element for settings
         *
         * @access public
         * @return void
         */
        public function fv_db_settings()
        {
            ?>
			<div id="fv-settings-general"></div>
			<?php 
        }
        
        /**
         * Set the pro later transient
         *
         * @access public
         * @return void
         */
        public function fv_pro_later()
        {
            set_transient( 'fv_pro_remind_later', 'show again', MONTH_IN_SECONDS );
        }
        
        /**
         * Set the pro done
         *
         * @access public
         * @return void
         */
        public function fv_pro_done()
        {
            $review = get_option( 'fv_pro_purchase' );
            $review['status'] = 'done';
            $review['purchased'] = current_time( 'yy/m/d' );
            update_option( 'fv_pro_purchase', $review, false );
        }
        
        /**
         * Get setting links
         *
         * @access public
         * @return array
         */
        public function settings_link( $links )
        {
            $url = admin_url( 'admin.php' ) . '?page=fv-db-settings';
            $settings_link = '<a class="fv-go-pro-menu" href=' . $url . '>Settings</a>';
            array_unshift( $links, $settings_link );
            if ( !function_exists( 'is_plugin_active' ) ) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $is_pro_activated = is_plugin_active( 'form-vibes-pro/form-vibes-pro.php' );
            
            if ( !$is_pro_activated ) {
                $mylinks = [ '<a class="fv-go-pro-menu" style="font-weight: bold; color : #93003c; text-shadow:1px 1px 1px #eee;" target="_blank" href="https://wpvibes.link/go/form-vibes-pro">Go Pro</a>' ];
                $links = array_merge( $links, $mylinks );
            }
            
            return $links;
        }
        
        /**
         * Update the database
         *
         * @access public
         * @return void
         */
        public function fv_db_update()
        {
            if ( isset( $_GET['fv_nonce'] ) && !wp_verify_nonce( $_GET['fv_nonce'], 'wp_rest' ) ) {
                die( 'Sorry, your nonce did not verify!' );
            }
            if ( isset( $_GET['fv_db_update'] ) ) {
                DbTables::create_db_table();
            }
        }
        
        /**
         * Load the plugin modules
         *
         * @access public
         * @return void
         */
        public function load_modules()
        {
            $modules = [
                'dashboard-widgets' => __( 'Dashboard Widgets', 'wpv-fv' ),
                'submissions'       => __( 'Submissions', 'wpv-fv' ),
                'analytics'         => __( 'Analytics', 'wpv-fv' ),
                'logs'              => __( 'Logs', 'wpv-fv' ),
            ];
            if ( Permissions::is_admin() ) {
                $modules['notices'] = __( 'Notices', 'wpv-fv' );
            }
            foreach ( $modules as $key => $val ) {
                $class_name = str_replace( '-', ' ', $key );
                $class_name = str_replace( ' ', '', ucwords( $class_name ) );
                $class_name = 'FormVibes\\Modules\\' . $class_name . '\\Module';
                $this->modules[$key] = $class_name::instance();
            }
        }
    
    }
    Plugin::instance();
} else {
}
