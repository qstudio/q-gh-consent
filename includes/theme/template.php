<?php

// namespace ##
namespace q\consent\theme;

use q\consent\core\plugin as plugin;
use q\consent\core\helper as helper;
use q\consent\core\api as api;
use q\consent\core\cookie as cookie;

/**
 * Template level UI changes
 *
 * @package   q_consent
 */
// load it up ##
\q\consent\theme\template::run();

class template extends plugin {

	/**
     * Instatiate Class
     *
     * @since       0.2
     * @return      void
     */
    public static function run()
    {

        // check if the feature has been activated in the admin ##
        if ( ! \get_option( plugin::$slug )['consent'] ) {
            
            // log ##
            helper::log( 'Consent UI not active' );

            // kick out ##
            return false;

        }

    	// styles and scripts ##
        \add_action( 'wp_enqueue_scripts', [ get_class(), 'wp_enqueue_scripts' ], 1 );

        // render consent bar markup - after brand bar at 3 ##
        \add_action( 'q_action_body_open', [ get_class(), 'render' ], 4 );

    }


    /**
     * WP Enqueue Scripts - on the front-end of the site
     *
     * @since       0.1
     * @return      void
     */
    public static function wp_enqueue_scripts()
    {

        // Register the script ##
        \wp_register_script( 'q-consent-js', Q_CONSENT_URL.'javascript/q-consent.js', array( 'jquery' ), plugin::$version, true );

        // Now we can localize the script with our data.
        $translation_array = array(
                'ajax_nonce'    => wp_create_nonce( 'q_consent' )
            ,   'ajax_url'      => get_home_url( '', 'wp-admin/admin-ajax.php' )
            ,   'saved'         => __( "Saved!", 'q-consent' )
            ,   'disabled'         => __( "Functional Cookies cannot be disabled", 'q-consent' )
        );
        wp_localize_script( 'q-consent-js', 'q_consent', $translation_array );

        // enqueue the script ##
        \wp_enqueue_script( 'q-consent-js' );

        // @todo - add styles ##
        // wp_register_style( 'q-consent-css', Q_CONSENT_URL.'scss/index.css', '', Plugin::$version );
        // wp_enqueue_style( 'q-consent-css' );

    }



	/**
     * Render Consent UI
     *
     * @since       0.1.0
     * @return      HTML
     */
    public static function render()
    {

        // @todo - Viktor - this is basic css to make the prototype work, this needs to be improved and moved into external asset css file ##
        self::css();

        // render consent bar ##
        self::bar();

        // add modal content ##
        self::modal();

    }



    public static function bar()
    {

        // check if the user has already given active consent - if not, we continue to push them to take an action ##
        if ( cookie::consent() ) {

            return false;

        }

?>
        <div class="q-consent-bar q-bsg">
            <i class="cross d-none d-md-block"></i>

            <div class="row">
                
                <div class="content col-8 col-md-8">
                    Generic short text about GDPR, Consent and <a 
                        href="<?php echo \get_permalink(); ?>#/modal/consent/tab/privacy/" 
                        class="modal-trigger"
                        data-tab-trigger="privacy">
                        Privacy Policy
                    </a>
                </div>
                
                <div class="col-2 cta d-block">
                    <button class="btn">
                        <a 
                            href="<?php echo \get_permalink(); ?>#/modal/consent/tab/settings/" 
                            class="modal-trigger"
                            data-tab-trigger="settings">
                            SETTINGS
                        </a>
                    </button>
                </div>
                
                <div class="col-2 cta d-block">
                    <button 
                        class="btn accept q-consent-set"
                        data-q-consent-marketing="1"
                        data-q-consent-analytics="1">
                        ACCEPT
                    </button>
                </div>
                
            </div>
        </div>
<?php

    }



    public static function modal()
    {

?>
        <div class="q-tab hidden modal-data" data-modal-key="consent">

            <div class="q-tab-triggers">
                <a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/settings" class="q-tab-trigger" data-tab-trigger="settings">Settings</a>
                <a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/privacy" class="q-tab-trigger" data-tab-trigger="privacy">Privacy</a>
            </div>

            <div class="tab-targets">
<?php

                // load up settings tab ##
                self::settings();
            
                // load up privacy tab ##
                self::privacy();

?>
            </div>

        </div>
<?php

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
        <div class="q-tab-target col-12 col-md-12" data-tab-target="settings">
            
            <div class="row">
                <h2>Consent Settings</h2>
                <p>blah blah blah..</p>

                <div class="options">

                    <div class="row">
                        <div class="description col-10 col-md-10">
                            <h5>Functional Cookies</h5>
                            <p>Text about this settings...</p>
                        </div>
                        <div class="col-1 col-md-1">
                            <?php echo self::option([
                                'field'     => 'functional',
                                'value'     => 1, // no opt-out
                                'disabled'  => true
                            ]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="description col-10 col-md-10">
                            <h5>Marketing Cookies</h5>
                            <p>Text about this settings...</p>
                        </div>
                        <div class="col-1 col-md-1">
                            <?php echo self::option([
                                'field'     => 'marketing',
                                'value'     => self::$cookie['marketing'],
                                'disabled'  => false
                            ]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="description col-10 col-md-10">
                            <h5>Analytical Cookies</h5>
                            <p>Text about this settings...</p>
                        </div>
                        <div class="col-1 col-md-1">
                            <?php echo self::option([
                                'field'     => 'analytics',
                                'value'     => self::$cookie['analytics'],
                                'disabled'  => false
                            ]); ?>
                        </div>
                    </div>

                </div>

            </div>

            <div class="row">
                
                <div class="col-2 cta d-block d-md-none">
                    <button 
                        class="btn accept q-consent-set"
                        data-q-consent-marketing="<?php echo self::$cookie['marketing']; ?>" 
                        data-q-consent-analytics="<?php echo self::$cookie['analytics']; ?>"
                        disabled
                    >
                        SAVE
                    </button>
                </div>

                <div class="col-2 cta d-block d-md-none">
                    <button 
                        class="btn reset q-consent-reset">
                        RESET
                    </button>
                </div>

            </div>

        </div>
<?php

    }



    /**
     * Render Privacy Policy content
     * Tries to get privacy Policy via API on greenheart.org
     * Adds a link to open the settings
     * 
     * @todo    make sure privacy policy exists
     * @since   0.1.0
     */
    public static function privacy()
    {

?>
        <div class="q-tab-target" data-tab-target="privacy">
            <?php echo api::privacy(); ?>
        </div>
<?php

    }



    public static function option( $args = null )
    {

        // return false;

        // sanity check ##
        if ( is_null( $args ) ) {

            helper::log( 'Error in passed args' );

            return false;

        }

        // array map of options ##
        $array = [
            'off'   => [
                'value'     => '0',
                'disabled'  => $args['disabled'] ? 'disabled' : '',
                'class'    => ( '0' == $args['value'] ) ? 'off' : null
            ],
            'on'    => [
                'value'     => '1',
                'disabled'  => $args['disabled'] ? 'disabled' : '',
                'class'    => ( '1' == $args['value'] ) ? 'on' : null
            ]
        ];

?>
        <div 
            class="q-consent-option"
            data-q-consent-field="<?php echo $args["field"]; ?>"
        >
<?php

        // loop out the same element twice, giving different classes ##
        foreach( $array as $key ) {


?>
            <div 
                class="slide <?php echo $key['class']; ?> <?php echo $key['disabled']; ?>" 
                data-q-consent-value="<?php echo $key['value']; ?>">
            </div>
<?php

        // loop ##
        }

?>
        </div>
<?php

    }


    /**
     * @Viktor to move to asset / front-end framework 
     * 
     */
    public static function css()
    {

?>
        <style>
            
        /* generic */
        .q-hidden { display: none; }

        /* tabs */
        .q-tab {  }

        /* tab triggers */
        .tab-trigger { display: inline; }
        .tab-trigger.active { font-weight: bold; }

        /* tab targets */
        .tab-target { display: none; }
        .tab-target.active { display: block; }

        /* options */
        .q-consent-option{ background-color: #f2f2f2; width: 60px; height: 30px; border: 1px solid #ddd; }
        .q-consent-option > .slide { width: 50%; height: 28px; float: left; cursor: pointer; }
        .q-consent-option > .off { background-color: red; }
        .q-consent-option > .on { background-color: green; float: right; }
        .q-consent-option > .disabled { cursor: not-allowed; }

        </style>
<?php

    }



}