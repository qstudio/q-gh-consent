<?php

// namespace ##
namespace Q_Meet_Our_Students\Cron;

use Q_Meet_Our_Students\Core\Helper as Helper;
use Q_Meet_Our_Students\Core\Plugin as Plugin;

/**
 * Grab student list from the GO web service
 *
 * @package   Q_Meet_Our_Students
 */
class Fetch_Students extends Plugin {

    /**
     * Construct
     *
     * @since       0.2
     * @return      void
     */
    public function __construct()
    {

        // hook our "soap" method to the added cron schedule 'q_mos_fetch_students' ##
        add_action( 'q_mos_fetch_students', array( $this, 'fetch' ) );

        // manually run action ##
        #add_action( 'init', array( $this, 'fetch' ), 5 );

    }


    /**
     * Hook into our added cron schedule point "q_mos_fetch_students"
     *
     * @since 0.2
     * @return void
     */
    public function fetch()
    {

        Helper::log( 'Cron running: Fetch Students...' );

        // give things a bit of time ##
        ini_set( "max_execution_time", 600 );
        ini_set( "default_socket_timeout", 600 );

        // try some SOAP ##
        try {

            $client = new \SoapClient (
                $this->wsdl_url // Web Service URL ##
                , array(
                        "soap_version"          => "SOAP_1_2" // SOAP Version ##
                    ,   "trace"                 => true // Trace ##
                    ,   'connection_timeout'    => 500000
                    ,   'cache_wsdl'            => WSDL_CACHE_BOTH
                    ,   'keep_alive'            => false,
                )
            );

            $result = $client->participantList();
            $students = $result->return;

            // we got results ##
            if ( $students && is_array( $students ) ) {

                Helper::log( 'SOAP Results grabbed' );

                // store students in a wp_options row ##
                \update_site_option( 'q_mos_students', \maybe_serialize( $students ) );

            } else {

                Helper::log( 'No SOAP Results' );

            }


        } catch ( \Exception $e ) {

            Helper::log( 'SOAP Failed: Fetch Students: '.$e->getMessage() );

        }

        Helper::log( 'Cron finished: Fetch Students...' );

    }

}

