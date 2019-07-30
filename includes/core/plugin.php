<?php

namespace q\consent\core;

use q\consent\core\helper as helper;
use q\consent\theme\template as template;

/**
 * Class Plugin
 * @package q\consent\plugin
 */

class plugin {

	// Settings ##
    const version = '1.3.5';
    // static $device; // current device handle ( 'desktop || handheld' ) ##
    public static $debug = true;
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
     * Instatiate Class
     *
     * @since       0.2
     * @return      void
     */
    public function __construct()
    {

        // check for dependencies, required for UI components - admin will still run ##
        if ( ! self::has_dependencies() ) {

            return false;

        }

        // check debug settings ##
        add_action( 'plugins_loaded', array( get_class(), 'debug' ), 11 );

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

        // helper::log( 'Q classes loaded....' );

        // ok ##
        return true;

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



    /**
     * Hook into WP actions and filters
     *
     * @access public
     * @since 0.5
     * @return void
     */
    public function run_hooks()
    {

        // set text domain ##
        \add_action( 'init', array( $this, 'load_plugin_textdomain' ), 1 );

    }


	/**
	 * Fired when the plugin is activated.
	 */
	public function activate() {

	}


	/**
	 * Fired when the plugin is deactivated.
	 */
	public function deactivate() {

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
        $domain = 'q-consent';

        // The "plugin_locale" filter is also used in load_plugin_textdomain()
        $locale = \apply_filters( 'plugin_locale', \get_locale(), $domain );

        // try from global WP location first ##
        \load_textdomain( $domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo' );

        // try from plugin last ##
        \load_plugin_textdomain( $domain, FALSE, \plugin_dir_path( __FILE__ ).'languages/' );

    }

}