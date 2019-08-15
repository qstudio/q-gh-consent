<?php

/*
 * Plugin Name:     Q Consent
 * Plugin URI:      http://qstudio.us/
 * Description:     Build a shared consent, privacy and cookie system
 * Version:         1.5.0
 * Author:          Q Studio
 * Author URI:      http://qstudio.us
 * License:         GPL2
 * Class:           q_consent
 * Text Domain:     q-consent
 * Domain Path:     languages/
 * GitHub Plugin URI: qstudio/q-gh-consent
 */

use q\consent\core\helper as helper;

defined( 'ABSPATH' ) OR exit;

if ( ! class_exists( 'q_consent' ) ) {

    // instatiate plugin via WP plugins_loaded - init was too late for CPT ##
    add_action( 'plugins_loaded', array ( 'q_consent', 'get_instance' ), 5 );

    class q_consent {

        // Refers to a single instance of this class. ##
        private static $instance = null;

        // Plugin Settings
        const version = '1.5.0';
        static $debug = false;
        const text_domain = 'q-consent'; // for translation ##

        static $slug = 'q-consent';

        // will contain the geotarget variables - https://wpengine.com/support/developers-guide-geotarget/ ## 
        static $geotarget = [
            'continent' => '',
            'country'   => '',
            // 'region'    => '',
            // 'city'      => ''
        ];
        
        // default cookie values ##
        static $defaults = [
            'consent'       => 0, // tracking consent action ##
            'marketing'     => 1, // marketing permitted ##
            'analytics'     => 1, // analytics permitted ##
        ];

        static $cookie = false;


        /**
         * Creates or returns an instance of this class.
         *
         * @return  Foo     A single instance of this class.
         */
        public static function get_instance() 
        {

            if ( 
                null == self::$instance 
            ) {

                self::$instance = new self;

            }

            return self::$instance;

        }
        
        
        /**
         * Instatiate Class
         * 
         * @since       0.2
         * @return      void
         */
        private function __construct() 
        {
            
            // activation ##
            register_activation_hook( __FILE__, array ( $this, 'register_activation_hook' ) );

            // deactvation ##
            register_deactivation_hook( __FILE__, array ( $this, 'register_deactivation_hook' ) );

            // set text domain ##
            add_action( 'init', array( $this, 'load_plugin_textdomain' ), 1 );
            
            // load libraries ##
            self::load_libraries();

            // check debug settings ##
            add_action( 'plugins_loaded', array( get_class(), 'debug' ), 11 );

        }


        /**
         * We want the debugging to be controlled in global and local steps
         * If Q debug is true -- all debugging is true
         * else follow settings in Q, or this plugin $debug variable
         */
        public static function debug()
        {

            // define debug ##
            self::$debug = 
                ( 
                    class_exists( 'Q' )
                    && true === \Q::$debug
                ) ?
                true :
                self::$debug ;

            // test ##
            // helper::log( 'Q exists: '.json_encode( class_exists( 'Q' ) ) );
            // helper::log( 'Q debug: '.json_encode( \Q::$debug ) );
            // helper::log( json_encode( self::$debug ) );

            return self::$debug;

        }



        // the form for sites have to be 1-column-layout
        public function register_activation_hook() {

            #add_option( 'q_device_configured' );

            // flush rewrites ##
            #global $wp_rewrite;
            #$wp_rewrite->flush_rules();

        }


        public function register_deactivation_hook() {

            #delete_option( 'q_device_configured' );

        }


        
        /**
         * Load Text Domain for translations
         * 
         * @since       1.7.0
         * 
         */
        public function load_plugin_textdomain() 
        {
            
            // set text-domain ##
            $domain = self::text_domain;
            
            // The "plugin_locale" filter is also used in load_plugin_textdomain()
            $locale = apply_filters('plugin_locale', get_locale(), $domain);

            // try from global WP location first ##
            load_textdomain( $domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo' );
            
            // try from plugin last ##
            load_plugin_textdomain( $domain, FALSE, plugin_dir_path( __FILE__ ).'library/language/' );
            
        }
        
        
        
        /**
         * Get Plugin URL
         * 
         * @since       0.1
         * @param       string      $path   Path to plugin directory
         * @return      string      Absoulte URL to plugin directory
         */
        public static function get_plugin_url( $path = '' ) 
        {

            return plugins_url( $path, __FILE__ );

        }
        
        
        /**
         * Get Plugin Path
         * 
         * @since       0.1
         * @param       string      $path   Path to plugin directory
         * @return      string      Absoulte URL to plugin directory
         */
        public static function get_plugin_path( $path = '' ) 
        {

            return plugin_dir_path( __FILE__ ).$path;

        }


        /**
         * Check for required classes to build UI features
         * 
         * @return      Boolean 
         * @since       0.1.0
         */
        public static function has_dependencies()
        {

            // check for what's needed ##
            if (
                ! class_exists( 'Q' )
            ) {

                helper::log( 'Q classes are required, install required plugin.' );

                return false;

            }

            // ok ##
            return true;

        }



        /**
        * Load Libraries
        *
        * @since        2.0
        */
		private static function load_libraries()
        {

            // check for dependencies, required for UI components - admin will still run ##
            if ( ! self::has_dependencies() ) {

                return false;

            }

            // core ##
            require_once self::get_plugin_path( 'library/core/helper.php' );
            require_once self::get_plugin_path( 'library/core/api.php' );
            require_once self::get_plugin_path( 'library/core/geotarget.php' );
            require_once self::get_plugin_path( 'library/core/cookie.php' );

            // backend ##
            require_once self::get_plugin_path( 'library/ajax/callback.php' );
            
            // plugins ##
            // require_once self::get_plugin_path( 'library/plugin/controller.php' );

            // frontend ##
            require_once self::get_plugin_path( 'library/theme/template.php' );

            // tests -- @todo, show response from GEO location ##
            // require_once self::get_plugin_path( 'library/test/controller.php' );

        }


    }


}