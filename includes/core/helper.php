<?php

// namespace ##
namespace Q_GH_Consent\Core;

use Q_GH_Consent\Core\Plugin as Plugin;

/**
 * Helper Functions
 *
 * @package   Q_GH_Consent\Core
 */
class Helper extends Plugin {


    /**
	 * Detect if this is a development site running on a private/loopback IP
	 *
	 * @return bool
	 */
	public static function is_localhost() {
        
        $loopbacks = array( '127.0.0.1', '::1' );
        
        if ( in_array( $_SERVER['REMOTE_ADDR'], $loopbacks ) ) {

            return true;
            
		}

		if ( ! filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE ) ) {

            return true;
            
		}

        return false;

	}


    /**
     * Write to WP Error Log
     *
     * @since       1.5.0
     * @return      void
     */
    static function log( $log )
    {

        if ( self::$debug && true === WP_DEBUG ) {

            $trace = debug_backtrace();
            $caller = $trace[1];

            $suffix = sprintf(
                __( ' - %s%s() %s:%d', 'Q_Scrape_Wordpress' )
                ,   isset($caller['class']) ? $caller['class'].'::' : ''
                ,   $caller['function']
                ,   isset( $caller['file'] ) ? $caller['file'] : 'n'
                ,   isset( $caller['line'] ) ? $caller['line'] : 'x'
            );

            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ).$suffix );
            } else {
                error_log( $log.$suffix );
            }

        }

    }


    /**
     * Pretty print_r / var_dump
     *
     * @since       0.1
     * @param       Mixed       $var        PHP variable name to dump
     * @param       string      $title      Optional title for the dump
     * @return      String      HTML output
     */
    static function pr( $var, $title = null )
    {

        if ( $title ) $title = '<h2>'.$title.'</h2>';
        print '<pre class="var_dump">'; echo $title; var_dump($var); print '</pre>';

    }


    /**
        * Get current device type from "Device Theme Switcher"
        *
        * @since       0.1
        * @return      string      Device slug
        */
    public static function get_device()
    {

        // property already loaded ##
        if ( self::$device ) { return self::$device; }

        // check plugin is active ##
        if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( "device-theme-switcher/dts_controller.php" ) ) {

            return self::$device = 'desktop'; // defaults to desktop ##

        }

        // Access the device theme switcher object anywhere in themes or plugins
        // http://wordpress.org/plugins/device-theme-switcher/installation/
        global $dts;

        // device check ##
        if ( is_null ( $dts ) ) {

            $handle = 'desktop';

        } else {

            // theme overwrite approved ##
            if ( ! empty($dts->{$dts->theme_override . "_theme"})) {

                #pr('option 1');
                $handle = $dts->{$dts->theme_override . "_theme"}["stylesheet"];

            // device selected theme loading ##
            } elseif ( ! empty($dts->{$dts->device . "_theme"})) {

                #pr('option 2');
                $handle = $dts->{$dts->device . "_theme"}["stylesheet"];

            // fallback to active theme ##
            } else {

                #pr('option 3');
                $handle = $dts->active_theme["stylesheet"];

            }

        }

        #pr($dts);

        // trim client prefix "ccigh-" from device handle ##
        $handle = ( $handle && false !== strpos( $handle, 'desktop' ) ) ? 'desktop' : 'handheld' ;

        #self::log( 'handle: '.$handle );

        // set and return the property value ##
        return self::$device = $handle;

    }
}