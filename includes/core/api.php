<?php

namespace q\consent\core;

use q\consent\core\helper as helper;
use q\consent\theme\template as template;

/**
 * Class API
 * @package q\consent
 */

// load it up ##
//  \q\consent\core\api::run();

class api extends plugin {

    /**
     * Instatiate Class
     *
     * @since       0.1.0
     * @return      void
     */
    public static function run()
    {

    }


    /**
     * Get privacy content via REST API request to greenheart.org
     * Cache result in WP Transients for 1 day
     *
     * @access      public
     * @since       0.1.0
     * @return      string
     */
    public static function privacy()
    {

        // uncomment this to defeat cache ##
        // \delete_site_transient( 'q_consent_privacy' );

        // check if we have a match in the cache first and return that ##
        if ( false === ( $string = \get_site_transient( 'q_consent_privacy' ) ) ) {

            // try to fetch privacy content from API ##
            // The API on greenheart.org is extended with a new "page" end-point to accept "privacy" parameter and uses get_page_by_path() from there ## 
            // http://v2.wp-api.org/reference/pages/
            // https://greenheart.org/api/v2/page/get/privacy

            // if API request fails or timesout, display default message ##
            $default =  \sprintf(
                'Sorry, we could not fetch the Privacy Policy right now, please view <a href="%s" target="_blank">Privacy Policy</a> or try again later.'
                , 'https://greenheart.org/privacy/'
            );

            // use local when local ##
            $url =
                Helper::is_localhost() ?
                'https://ghorg.qlocal.com/api/v2/page/get/privacy' : 
                'https://greenheart.org/api/v2/page/get/privacy' ;

            global $wp_version;
            $args = array(
                'timeout'     => 5,
                'redirection' => 5,
                'httpversion' => '1.0',
                'user-agent'  => 'WordPress/' . $wp_version . '; ' . \home_url(),
                'blocking'    => true,
                'headers'     => array(),
                'cookies'     => array(),
                'body'        => null,
                'compress'    => false,
                'decompress'  => true,
                'sslverify'   => Helper::is_localhost() ? false : true , // no SSL locally ##
                'stream'      => false,
                'filename'    => null
            ); 

            // login user via a GET request to API v2 ##
            $response = \wp_remote_get( $url, $args ); 

            // default ##
            $string = $default; 
            
            // array returned ##
            if ( is_array( $response ) ){
                
                // body is JSON encoded ##
                $body = json_decode( $response['body'] ) ;

                // helper::log( 'wp_remote_get said: ' );
                helper::log( $body->data->content );

                // check we have the content we need and filter it accordingly ##
                $string = $body->data->content ? \wpautop( $body->data->content ) : $default ;

            }

            // store it ##
            \set_site_transient( 'q_consent_privacy', $string, 1 * DAY_IN_SECONDS );

        }

        // kitck it back ##
        return $string;

    }



}