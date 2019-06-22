<?php

namespace q\consent\core;

use q\consent\core\helper as helper;
use q\consent\theme\template as template;

/**
 * Class API
 * @package q\consent
 */

// load it up ##
\q\consent\core\cookie::run();

class cookie extends plugin {

    /**
     * Instatiate Class
     *
     * @since       0.1.0
     * @return      void
     */
    public static function run()
    {

        // set defalt cookie, if not present ##
        \add_action( 'init', [ get_class(), 'init' ], 1, 0 );

    }


     /**
     * Set default cookie, if none found 
     * 
     * @since       0.1.0
     * @return      void
     */
    public static function init()
    {

        if ( self::$cookie ) {

            // helper::log( 'No need to run this twice..' );

            return self::$cookie;

        }

        // check for cookie, bulk if found ##
        $cookie = self::get();
        if ( $cookie ) {

            // assign to property ##
            self::$cookie = $cookie;

            // nothing to do ##
            return self::$cookie;

        }

        // helper::log( 'Running set default cookie....' );

        // set default ##
        self::set( self::$defaults ); 

        // assign defaults to static property - returns an array ##
        self::$cookie = self::$defaults;

        // return cookie values ##
        return self::$cookie;      

    }



    public static function is_active( $check = null ) {

        // sanity ##
        if ( is_null( $check ) ) {

            helper::log( 'No cookie value passed...' );

            return false;

        }

        // helper::log( 'Cookie check: '.$check );
        // helper::log( self::$cookie );

        // check if cookie set and correct ##
        if (
            ! self::$cookie
            || ! is_array( self::$cookie )
            || ! isset( self::$cookie[ $check ] )
        ) {

            helper::log( 'Error finding requested cookie value: '.$check );
            // helper::log( self::$cookie );

            return false;

        }

        // kick it back ##
        return ( 1 == self::$cookie[ $check ] ) ? true : false ;

    }


    /**
     * Get plugin cookie
     * 
     * @return      Mixed   Array if cookie set | boolean false
     * @since       0.1.0
     */
    public static function get()
    {

        if ( 
            isset( $_COOKIE[plugin::$slug] ) 
            && $_COOKIE[plugin::$slug] 
            // && is_array( $_COOKIE[plugin::$slug] )
        ) {

            // get ##
            $cookie = $_COOKIE[plugin::$slug];
            // helper::log( $cookie );

            // cookie values are serialized when stored ##
            if ( 
                is_string( $cookie )
                // || \is_serialized( $cookie )
            ) {

                // as cookie format has changed, we need to check for old format and drop cookie.. or convert to new format ##
                if ( strpos( $cookie, '#' ) ) {

                    // helper::log( $cookie );

                    // old format ##
                    // helper::log( 'Cookie data stored in old format' );

                    // string replace "#" to "__" ##
                    $cookie = str_replace( '_', '__', $cookie ) ;

                    // string replace "#" to "__" ##
                    $cookie = str_replace( '#', '_', $cookie ) ;

                    // test ##
                    // helper::log( $cookie );

                    // reasign cookie ##
                    $_COOKIE[plugin::$slug] = $cookie;

                }

                // helper::log( 'Cookie in string format, unpick...' );

                $explode = explode( '__', $cookie );
                helper::log( $explode );

                // new array ##
                $array = [];

                foreach ( $explode as $row ) {

                    // split row into parts ##
                    $item = explode( '_', $row );

                    $array[$item[0]] = $item[1];

                }

                // re-assign ##
                $cookie = $array;

            }

            // it should now be an array ##
            if ( ! is_array( $cookie ) ) {

                helper::log( 'WTF...' );

                return false;

            }

            // helper::log( 'Cookie already set and returned' );
            // helper::log( $cookie );

            return $cookie;

        }

        // helper::log( 'Cookie not set...' );

        // set default ##
        // self::set_cookie( self::$defaults );  

        // returning default cookie ##
        return false;

    }



    /**
     * Create cookie
     *
     * @since       0.1
     * @return      Boolean
     */
    public static function set( $array = null )
    {

        // sanity check ##
        if ( 
            is_null ( $array ) 
            || ! is_array($array )
        ) {

            // nothng to do ##
            helper::log( 'Error in passed args' );

            return false ;

        }

        // helper::log( $array );

        // we need to convert our named array into something nice to store in the cookie ##
        // consent_1_marketing_0_analytics_1 ##

        $string = '';
        foreach( $array as $key => $value ) {

            $string .= $key.'_'.$value.'__';

        }

        // trim last "_" ##
        $string = trim( $string, '__' );

        // check it out ##
        // helper::log( $string );

        // set the cookie ##
        \setcookie( plugin::$slug, $string, \time() + 62208000, '/' );
        
        // set the cookie value in the global scope ##
        $_COOKIE[plugin::$slug] = $string; 

        // what happened ##
        // helper::log( 'Set cookie::' );
        // helper::log( $array );

        // kick back feedback ##
        return true ;

    }



    /**
     * Check if the user has taken an action and given consent to non-functional cookies
     * 
     * @since       0.1.0
     * @return      Boolean
     */
    public static function consent()
    {

        helper::log( 'Checking if consent has been given..' );
        helper::log( self::$cookie ) ;

        // check for active consent ##
        if ( 
            ! is_array( self::$cookie )
            || ! array_key_exists( 'consent', self::$cookie )
            || false === self::$cookie['consent']
            || 0 == self::$cookie['consent'] 
            || ! self::$cookie['consent'] 
        ) {

            helper::log( 'We cannot 100% confirm consent given, so show the bar again..' );

            // if there is any error with the data, we presume no consent has been given ##
            return false;

        }

        helper::log('The user has actively given their consent.. no need to show the bar..');

        return true;

    }


}