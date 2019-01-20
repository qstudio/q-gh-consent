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

        // fake ##
        // $country = 'ES';

        // log ##
        // helper::log( $country );

        // log ##
        // helper::log( 'FAKE: '. $country );

        // if nothing cooking, bale ##
        if (
            ! $country
            || false === $country
            && ! helper::is_localhost()
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
            // || ! isset( $country['continent'] )
        ){

            // helper::log( 'No country code passed or corrupt.' );

            return false;

        }

        // we need to get a list of all the countries from the wpengine-geoid plugin ##
        if (  
            ! function_exists( 'geoip_country_list' )
            || ! geoip_country_list()
        ) {

            // helper::log( 'geoip_country_list function missing or returned empty results' );

            return false;
            
        }

        // now try to get continent based on defined country list - filterable ##
        $countries = \apply_filters( 'q_geoip_country_list', geoip_country_list() );

        // check if we have a match ##
        if (  
            ! isset( $countries[$country] )
            || ! isset( $countries[$country]['continent'] )
        ){

            // helper::log( 'No match in country list for: '.$country );

            return false;

        }

        // helper::log( 'Contient: '.$countries[$country]['continent'] );

        // kick it back ##
        return $countries[$country]['continent'];
        
	}



    /**
     * Check if the current user is within the EU
     * 
     * @return      Boolean
     */
    public static function is_eu()
    {

        // localhost override
        if ( helper::is_localhost() ) {

            helper::log( 'Localhost geotarget override' );

            return true;

        }

        // passed override
        if ( 
            isset( $_GET['geotarget'] )
            && 'EU' == $_GET['geotarget']
        ) {

            helper::log( '$_GET geotarget override' );

            return true;

        }

        // we need to check if we have a continent, and if it == 'EU' ##
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