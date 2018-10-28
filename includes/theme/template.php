<?php

// namespace ##
namespace Q_GH_Consent\Theme;

use Q_GH_Consent\Core\Plugin as Plugin;
use Q_GH_Consent\Core\Helper as Helper;

/**
 * Template level UI changes
 *
 * @package   Q_GH_Consent
 */
class Template extends Plugin {

	/**
     * Instatiate Class
     *
     * @since       0.2
     * @return      void
     */
    public function __construct()
    {

    	// styles and scripts ##
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 1 );

        // add body class identfier ##
        // add_filter( 'body_class', array( $this, 'body_class' ), 1, 1 );

        // add in brand bar ##
        add_action( 'q_action_body_open', array ( $this, 'render' ), 3 );

    }


    /**
     * WP Enqueue Scripts - on the front-end of the site
     *
     * @since       0.1
     * @return      void
     */
    public function wp_enqueue_scripts()
    {

        // only add these scripts on the correct page template ##
        #if ( ! is_page_template( 'template-meet-our-students.php' ) ) { return false; }

        // Register the script ##
        #wp_register_script( 'multiselect-js', Q_GH_CONSENT_URL.'javascript/jquery.multiselect.js', array( 'jquery' ), $this->version, true );
        #wp_enqueue_script( 'multiselect-js' );

        // Register the script ##
        \wp_register_script( 'q-gh-consent-js', Q_GH_CONSENT_URL.'javascript/q-gh-consent.js', array( 'jquery' ), Plugin::$version, true );

        // // Now we can localize the script with our data.
        // $translation_array = array(
        //         'ajax_nonce'    => wp_create_nonce( 'q_mos_nonce' )
        //     ,   'ajax_url'      => get_home_url( '', 'wp-admin/admin-ajax.php' )
        //     ,   'saved'         => __( "Saved!", 'q-gh-consent' )
		// 	,   'input_saved'   => __( "Saved", 'q-gh-consent' )
		// 	,   'input_max'		=> __( "Maximum Saved", 'q-gh-consent' ) // text to indicate that max number of students reached ##
        //     ,   'student'       => __( "Student saved", 'q-gh-consent' )
        //     ,   'students'      => __( "Students saved", 'q-gh-consent' )
        //     ,   'error'         => __( "Error", 'q-gh-consent' )
		// 	,   'count_cookie'  => $this->count_cookie() // send cookie count to JS ##
		// 	,   'max_students'  => $this->max_students // max number of students that can be saved ##
        //     ,   'form_id'       => $this->form_id // Gravity Forms ID ##
        // );
        // wp_localize_script( 'q-gh-consent-js', 'q_mos', $translation_array );

        // enqueue the script ##
        \wp_enqueue_script( 'q-gh-consent-js' );

        // @todo - this needs to be removed from the BB plugin and moved into Q when it is hosted on github ##
        // wp_register_style( 'q-gh-main-css', Q_GH_CONSENT_URL.'scss/index.css', '', Plugin::$version);
        // wp_enqueue_style( 'q-gh-main-css' );

        // wp_register_style('google-fonts', '//fonts.googleapis.com/css?family=Open+Sans:400,700|Lato:400,700');
        // wp_enqueue_style( 'google-fonts' );

    }


    /*
    Add body class to allow each install to be identified uniquely

    @since      0.2
    @return     Array      array of classes passed to method, with any additions
    */
    public function body_class( $classes )
    {

        // let's grab and prepare our site URL ##
        $identifier = strtolower( get_bloginfo( 'name' ) );

        // add our class ##
        $classes[] = 'install-'.str_replace( array( '.', ' '), '-', $identifier );

        // return to filter ##
        return $classes;

    }


	/**
     * Render Brand Bar - called from widget added to theme template
     *
     * @since       0.1
     * @return      HTML
     */
    public static function render()
    {

        // @todo viktor - later, this needs to be set-up differently ##
        // render() should call a method for each UI featuer being rendered and they should have checks internally if the features are active ##
        if ( ! \get_option( Plugin::$name)['consent'] ) {
            
            // log ##
            Helper::log( 'Consent UI not active' );

            // kick out ##
            return false;

        }

?>
        <div class="q-bb q-bb-promo q-bsg">
            <i class="cross d-none d-md-block"></i>

            <div class="row">
                
                <div class="content col-8 col-md-6">
                    Generic short text about GDPR, Consent and <a href="/#/modal/consent/privacy/">Privacy Policy</a>
                </div>
                
                <div class="col-2 cta d-block d-md-none"><button class="btn settings"><a href="/#/modal/consent/settings/">SETTINGS</a></button></div>
                
                <div class="col-2 cta d-block d-md-none"><button class="btn accept">ACCEPT</button></div>

            </div>
        </div>
<?php

    }



    public static function modal()
    {

        // @todo - check if site already has modal markup rendered ##
        
        // if modal markup missing, add required placeholder markup ## 

    }



    /**
     * Render Consent Settings in Modal 
     * 
     * @todo    tie into core method to save cookie
     * @since   0.1.0
     */
    public static function settings()
    {

?>
        <div class="settings">
            Settings...
        </div>
<?php

    }



    /**
     * Render Privacy Policy content
     * Tried to get privacy Policy via API on greenheart.org
     * Adds a link to open the settings
     * 
     * @todo    make sure privacy policy exists
     * @since   0.1.0
     */
    public static function privacy()
    {

        // if API request fails or timesout, display default message ##
        $default = 'Sorry, we could not fetch the Privacy Policy right now, please view <a href="%s" target="_blank">Privacy Policy</a> or try again later.';

        // try to fetch privacy content from API ##

        // build link to replace content with settings in same modal ##
        $settings = '<button class="btn settings"><a href="/#/modal/consent/settings/">Edit your Settings</a></button>';

        // compile ##
        $string = \sprintf(
            'Sorry, we could not fetch the Privacy Policy right now, please view <a href="%s" target="_blank">Privacy Policy</a> or try again later.'
            , 'https://greenheart.org/privacy/'
        );

    }

}