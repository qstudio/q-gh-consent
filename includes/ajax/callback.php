<?php

// namespace ##
namespace Q_Meet_Our_Students\AJAX;

use Q_Meet_Our_Students\Core\Plugin as Plugin;
use Q_Meet_Our_Students\Theme\Template as Template;

/**
 * AJAX callbacks
 *
 * @package   Q_Meet_Our_Students
 */
class Callback extends Template {

	/**
     * Construct
     *
     * @since       0.2
     * @return      void
     */
    public function __construct()
    {

    	// AJAX callback methods ##
        add_action( 'wp_ajax_save_student', array( $this, 'ajax_save_student' ) ); // ajax for logged in users
        add_action( 'wp_ajax_nopriv_save_student', array( $this, 'ajax_save_student' ) ); // ajax for not logged in users

        add_action( 'wp_ajax_update_form', array( $this, 'ajax_update_form' ) ); // ajax for logged in users
        add_action( 'wp_ajax_nopriv_update_form', array( $this, 'ajax_update_form' ) ); // ajax for not logged in users

        // clear cookies ##
        add_action( 'wp_ajax_delete_cookie', array( $this, 'ajax_delete_cookie' ) ); // ajax for logged in users
        add_action( 'wp_ajax_nopriv_delete_cookie', array( $this, 'ajax_delete_cookie' ) ); // ajax for not logged in users

    }

    /**
     * Delete stored cookie
     *
     * @since       0.1
     * @return      Boolean
     */
    public function ajax_delete_cookie()
    {

        // Check if a cookie has been set
        if ( isset( $_COOKIE["q_mos_saved"] ) && $_COOKIE["q_mos_saved"] ) {

            unset( $_COOKIE['q_mos_saved'] );
            setcookie( 'q_mos_saved', null, -1, '/' );

            $return = 1;

        } else {

            $return = 0;

        }

        // set headers ##
        header( "Content-type: application/json" );

        // return it ##
        echo json_encode( $return );

        // all AJAX calls must die!! ##
        die();

    }



    /**
     * Save student to cookie from button click
     *
     * @since       0.1
     * @return      Boolean
     */
    public function ajax_save_student( $id = null )
    {

        // check nonce ##
        check_ajax_referer( 'q_mos_nonce', 'nonce' );

        // let's see if the user ID was passed ##
        if ( isset( $_POST['id'] ) && $_POST['id'] ) {

            // add post data to variables ##
            $id = \Q::sanitize( $_POST['id'] );

            // check for stored cookie ##
            if ( $this->get_cookie() ) {

                // save student ##
                if ( true === $this->set_cookie( $id ) ) {

                    // return the number of saved students + 1, as cookie needs reload ##
                    #$return = count( $this->get_cookie() ) + 1;

					// return the number of saved students ##
                    $return = $this->count_cookie();

                } else {

                    // return the number of saved students ##
                    $return = 0;

                }

                #pr( $this->get_cookie(), 'updated cookie..' );

            } else {

                // save student ##
                $this->set_cookie( $id );

                // return 1 ##
                $return = 1;

            }

        } else {

            // return 0 ##
            $return = 0;

        }

        // set headers ##
        header("Content-type: application/json");

        // return it ##
        echo json_encode($return);

        // all AJAX calls must die!! ##
        die();

    }


    /**
     * Method to update stored values in Gravity Form
     * Grabs all students stored in cookie and present as radio buttons in the form
     *
     * @since       0.1
     * @return      Mixed   Boolean on empty cookie OR Array of student names
     */
    public function ajax_update_form()
    {

        // check nonce ##
        check_ajax_referer( 'q_mos_nonce', 'nonce' );

        // check for stored cookie ##
        if ( $cookie = $this->get_cookie() ) {

			#wp_die( pr( $cookie, 'Cookie' ) );

            $return = array();

            foreach( $cookie as $student ) {

                #pr( $student );

                // student gender ##
                #$gender = get_field( 'mos_gender', intval( $student ) );
                $gender = wp_get_post_terms( intval( $student ), 'mos_gender' ); // this is a taxonomy - first item returned ##
                $src_gender = $gender ? $gender[0] : false;
                $src_gender = $src_gender->name == 'Male' ? 'male' : 'female' ;
                #pr( $src_gender );
                $src_gender = q_locate_template( "images/students/profile-{$src_gender}.png", false );


                // get the student flag ##
                #$country = get_field( 'mos_country', intval( $student ) );
                $country = wp_get_post_terms( intval( $student ), 'mos_country' ); // this is a taxonomy - returns a single term - slug = two letter code "en", name = "England" ##
                $country = $country ? $country[0] : false;

                $src_country = get_field( 'country_shortcode', $country ) ? strtolower( get_field( 'country_shortcode', $country ) ) : 'en' ;
                $src_country =
                    q_locate_template( "images/students/flag-{$src_country}.png", false ) ?
                    q_locate_template( "images/students/flag-{$src_country}.png", false ) :
                    q_locate_template( "images/students/flag-en.png", false ) ; // backup to EN flag for now ##

                $return[] = array(
                        'name'      => get_the_title( intval( $student ) )
                    ,   'unique_id' => get_post_meta( intval( $student ), 'mos_unique_id', true )
                    ,   'country'   => $src_country
                    ,   'gender'    => $src_gender
                );

            }

            // we need to return a string ##
            #$cookie = implode( ',', $cookie );
            #pr( $return );

            // return the number of saved students ##
            #$return = $cookie;

        } else {

            // return 0 ##
            $return = false;

        }

        // set headers ##
        header( "Content-type: application/json" );

        // return it ##
        echo json_encode( $return );

        // all AJAX calls must die!! ##
        die();

    }

}