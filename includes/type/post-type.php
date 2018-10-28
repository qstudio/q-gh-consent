<?php

// namespace ##
namespace Q_Meet_Our_Students\Type;

/**
 * Register required post types
 *
 * @package   Q_Meet_Our_Students
 */
class Post_Type {

	/**
     * Construct
     *
     * @since       0.2
     * @return      void
     */
    public function __construct( $single = null, $plural = null )
    {

    	#$this->create( $single, $plural );

    }

	public function create( $single = null, $plural = null ) {

		// sanity check ##
		if (
			is_null( $single )
		) {
			return false;
		}

		// make plural ##
		if ( is_null( $plural ) ) {

			$plural = $single.'s';

		}

		#wp_die( $plural );

		// lowercase variants ##
		$single_lowercase = strtolower( $single );
		$plural_lowercase = strtolower( $plural );

		$labels = array(
	        'name'                   => __( $single, 'q-meet-our-students' )
	        ,'singular_name'         => __( $single, 'q-meet-our-students' )
	        ,'add_new'               => __('Add '.$single, 'q-meet-our-students' )
	        ,'add_new_item'          => __('Add '.$single, 'q-meet-our-students' )
	        ,'edit_item'             => __('Edit '.$single, 'q-meet-our-students' )
	        ,'new_item'              => __('Add '.$single, 'q-meet-our-students' )
	        ,'all_items'             => __('All '.$plural, 'q-meet-our-students' )
	        ,'view_item'             => __('View '.$single, 'q-meet-our-students' )
	        ,'search_items'          => __('Search '.$plural, 'q-meet-our-students' )
	        ,'not_found'             => sprintf (
	                                        '<p style="margin: 10px;">%s <a href="%s" class="add-new-h2 q_client_inline">%s</a></p>'
	                                        ,   __('No Students found', 'q-meet-our-students' )
	                                        ,   esc_url( admin_url('post-new.php?post_type='.$single_lowercase) )
	                                        ,   __('Add '.$single, 'q-meet-our-students' )
	                                    )
	        ,'not_found_in_trash'    => __('No '.$plural.' found in Trash', 'q-meet-our-students' )
	        ,'parent_item_colon'     => ''
	        ,'menu_name'             => __( $plural, 'q-meet-our-students' )
	    );

	    $args = array(
	            'public'            => true
	        ,   'labels'            => $labels
	        ,   'hierarchical'      => false // post style ##
	        ,   'supports'          => array (
	                                    'title', 'editor', 'author', 'excerpt', 'custom-fields'
	                                )
	        ,   'rewrite'           => false
	        #,   'rewrite'           => array(
	        #                            'slug'          => '/',
	        #                            'with_front'    => false
	        #                        )
	        ,   'menu_position'     => 20 // below comments
	        ,   'feeds'             => false
	        ,   'has_archive'       => false
	    );

	    register_post_type( $single_lowercase, $args );

	}

}
