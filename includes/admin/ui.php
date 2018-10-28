<?php

// namespace ##
namespace Q_GH_Consent\Admin;

#use Q_Meet_Our_Students\Type\Taxonomy as Taxonomy;
#use Q_Meet_Our_Students\Type\Post_Type as Post_Type;

/**
 * Admin UI changes
 *
 * @package   Q_Meet_Our_Students
 */
class UI {

	/**
     * Construct
     *
     * @since       0.2
     * @return      void
     */
    public function __construct()
    {

        // Register Taxonomy ##
        #add_action( 'init', array( $this, 'register_taxonomy' ), 2 );

        // Register CPT ##
        #add_action( 'init', array( $this, 'register_post_type' ), 3 );

        // widget ##
        add_action( 'init', array( $this, 'widgets_init' ), 21 );

    	// styles and scripts ##
        #add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        // remove meta boxes from edit student screen ##
        #add_action( 'admin_menu', array( $this, 'remove_meta_box' ) );

    }


    /**
     * Register Taxonomy for Post Type
     *
     * @since       0.1
     * @return      void
     */
    public function register_taxonomy()
    {

        $taxonomy = new Taxonomy;
        $taxonomy->create( 'Country', 'Countries', 'mos_country', 'student' );
        $taxonomy->create( 'Interest', 'Interests', 'mos_interest', 'student' );
        #$taxonomy->create( 'Age', 'Ages', 'mos_age', 'student' );
        $taxonomy->create( 'Gender', 'Genders', 'mos_gender', 'student' );

    }


    /**
     * Register Post Type
     *
     * @since       0.1
     * @return      void
     */
    public function register_post_type()
    {

        $post_type = new Post_Type;
        $post_type->create( 'Student', 'Students' );

    }



    /**
     * Shared Sidebars & Widgetized areas
     *
     * @since       0.1
     * @return      void
     */
    public function widgets_init()
    {

        // Posts widget ##
        register_sidebar( array(
            'name' => __( 'Brand Bar', 'q-gh-brand-bar' ),
            'id' => 'q-gh-brand-bar',
            'description' => __( 'Global BRanding Bar', 'q-gh-brand-bar' ),
        ));

    }


	/**
     * Admin Enqueue Scripts - on the back-end of the site
     *
     * @since       0.2
     * @return      void
     */
    public function admin_enqueue_scripts()
    {

        wp_register_style( 'q-meet-our-students-css', QMOS_URL.'css/q-meet-our-students.css' );
        wp_enqueue_style( 'q-meet-our-students-css' );

    }


    /**
     * Remove non-required taxonomy panels from edit student screen
     *
     * @since       0.2
     * @return      void
     */
    public function remove_meta_box()
    {

        remove_meta_box( 'tagsdiv-mos_gender', 'student', 'side' );
        remove_meta_box( 'tagsdiv-mos_age', 'student', 'side' );
        remove_meta_box( 'tagsdiv-mos_country', 'student', 'side' );
        remove_meta_box( 'tagsdiv-mos_interest', 'student', 'side' );

    }


}