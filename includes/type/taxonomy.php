<?php

// namespace ##
namespace Q_Meet_Our_Students\Type;

/**
 * Register required post types
 *
 * @package   Q_Meet_Our_Students
 */

// class ##
class Taxonomy {

	public static function create( $single = null, $plural = null, $slug = null, $post_type = null ) {

		// sanity check ##
		if (
			is_null( $single )
			|| is_null( $post_type )
		) {
			return false;
		}

		// make plural ##
		if ( is_null( $plural ) ) {

			$plural = $single.'s';

		}

		// lowercase variants ##
		$single_lowercase = strtolower( $single );
		$plural_lowercase = strtolower( $plural );

		// make slug ##
		if ( is_null( $slug ) ) {

			$slug = $single_lowercase;

		}


		register_taxonomy (
                $slug // taxonomy slug
            ,   array ( $post_type ) // array of post types ##
            ,   array ( // labels ##
                'labels'                               => array(
                        'name'                         => _x( $single, 'taxonomy general name' )
                    ,   'singular_name'                => _x( $single, 'taxonomy singular name' )
                    ,   'q_search_name'                => _x( 'Choose '.$single, 'taxonomy singular name' )
                    ,   'q_search_label'               => _x( $single, 'taxonomy singular name' )
                    ,   'search_items'                 => __( 'Search '.$single, 'q-meet-our-students' )
                    ,   'popular_items'                => __( 'Popular '.$single, 'q-meet-our-students' )
                    ,   'all_items'                    => __( 'All '.$plural, 'q-meet-our-students' )
                    ,   'parent_item'                  => null
                    ,   'parent_item_colon'            => null
                    ,   'edit_item'                    => __( 'Edit '.$single, 'q-meet-our-students' )
                    ,   'update_item'                  => __( 'Update '.$single, 'q-meet-our-students' )
                    ,   'add_new_item'                 => __( 'Add New '.$single, 'q-meet-our-students' )
                    ,   'new_item_name'                => __( 'New '.$single.' Name', 'q-meet-our-students' )
                    ,   'separate_items_with_commas'   => __( 'Separate '.$plural.' with commas', 'q-meet-our-students' )
                    ,   'add_or_remove_items'          => __( 'Add or remove '.$plural, 'q-meet-our-students' )
                    ,   'choose_from_most_used'        => __( 'Choose from the most used '.$plural, 'q-meet-our-students' )
                    ,   'not_found'                    => __( 'No '.$plural.' found.', 'q-meet-our-students' )
                    ,   'menu_name'                    => __( $plural, 'q-meet-our-students' )
                ),/*
                'rewrite'           => array(
                    'slug'          => 'format',
                    'hierarchical'  => true
                ),*/
                'rewrite'           => false,
                'hierarchical'      => true,
                'query_var'         => false,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => false,
                'public'            => false
            )

        );

	}

}