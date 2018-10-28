<?php

// namespace ##
namespace Q_GH_Consent\Plugin;

/**
 * Q Search modifications
 *
 * @package   Q_GH_Consent
 */
class Q_Search {

	/**
     * Construct
     *
     * @since       0.2
     * @return      void
     */
    public function __construct()
    {

    	// filter q-search defaults ##
        add_filter( 'q_search_post_type', function( $value ){ return 'student'; });
        add_filter( 'q_search_taxonomies', function( $value ){ return 'mos_gender,mos_age,mos_interests'; });
        add_filter( 'q_search_posts_per_page', function( $value ){ return 20; });
        add_filter( 'q_search_template', function( $value ){ return 'q-meet-our-students.php'; });
        add_filter( 'q_search_order', function( $value ){ return 'ASC'; });
        add_filter( 'q_search_order_by', function( $value ){ return 'id'; });

    }

}