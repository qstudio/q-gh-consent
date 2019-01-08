<?php

namespace q\consent\core;

use q\consent\core\helper as helper;
use q\consent\theme\template as template;

/**
 * Class API
 * @package q\consent
 */

// load it up ##
\q\consent\core\geotarget::run();

class geotarget extends plugin {

    /**
     * Instatiate Class
     *
     * @since       0.1.0
     * @return      void
     */
    public static function run()
    {

        \add_action( 'init', [ get_class(), 'get' ], 10 );

    }


    /**
     * Get the data from the geotarget plugin
     *
     * @access      public
     * @since       0.1.0
     * @return      string
     */
    public static function get()
    {

        // get ##
        // $city = getenv( 'HTTP_GEOIP_CITY' );
        $country = getenv('HTTP_GEOIP_COUNTRY_CODE');
        // $region = getenv( 'HTTP_GEOIP_REGION' );

        // log ##
        helper::log( $country );

        // if nothing cooking, bale ##
        if (
            ! $country
            || false === $country
        ) {

            helper::log( 'HTTP_GEOIP_COUNTRY_CODE empty' );

        }

        // get continent ##
        // $contient = self::continent( $country );

        // assign data to static property ##
        self::$geotarget = [
            'continent' => self::continent( $country ),
            'country'   => $country,
            // 'region'    => '',
            // 'city'      => ''
        ];

        // kitck it back ##
        return self::$geotarget;

    }



    /**
	 * Get Continent
	 *
	 * @since 1.1.0
	 * @param  string $country Two-letter country code.
	 * @return string          Two-letter continent code, e.g. EU for Europe
	 */
	public static function continent( $country = null ) {

        if ( 
            is_null( $country )
            || ! isset( $country['continent'] )
        ){

            helper::log( 'No country code passed or corrupt.' );

            return false;

        }
        
        helper::log( 'Contient: '.$country['continent'] );

        // kick it back ##
        return $country['continent'];
        
	}



    /**
     * Check if the current user is within the EU
     * 
     * @return      Boolean
     */
    public static function is_eu()
    {

        // we need to check if we have a continent, and if it == 'EU' ##

        // faked ##
        return 
        (
            isset( self::$geotarget['continent'] )
            && self::$geotarget['continent']
            && 'EU' == self::$geotarget['continent']
        ) ?
        true : 
        false ;

    }


}