<?php

// namespace ##
namespace q\consent\theme;

use q\consent\core\plugin as plugin;
use q\consent\core\helper as helper;
use q\consent\core\api as api;
use q\consent\core\geotarget as geotarget;
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
        if (
        ! \get_option( plugin::$slug )['consent']
        ) {

            // log ##
            // helper::log( 'Consent UI not active' );

            // kick out ##
            return false;

        }

        // styles and scripts ##
        \add_action( 'wp_enqueue_scripts', [ get_class(), 'wp_enqueue_scripts' ], 99 );

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
            'ajax_nonce'    => \wp_create_nonce( 'q_consent' )
        ,   'ajax_url'      => \get_home_url( '', 'wp-admin/admin-ajax.php' )
        ,   'saved'         => __( "Saved!", 'q-consent' )
        ,   'disabled'      => __( "Functional Cookies cannot be disabled", 'q-consent' )
        );
        \wp_localize_script( 'q-consent-js', 'q_consent', $translation_array );

        // enqueue the script ##
        \wp_enqueue_script( 'q-consent-js' );

        wp_register_style( 'q-consent-css', Q_CONSENT_URL.'scss/index.css', '', plugin::$version );
        wp_enqueue_style( 'q-consent-css' );
    }



    /**
     * Render Consent UI
     *
     * @since       0.1.0
     * @return      HTML
     */
    public static function render()
    {
        // render consent bar ##
        self::bar();

        // add modal content ##
        self::modal();
    }



    public static function bar()
    {

        // check if the user has already given active consent - if not, we continue to push them to take an action ##
        if ( cookie::consent() ) {

            // helper::log( 'Consent already given, so do not display bar' );

            return false;

        }

        // check if the user is in the EU contient, for GDPR compliance
        if ( ! geotarget::is_eu() ) {

            // helper::log( 'User is outside the EU, so we do not need to show the bar' );

            return false;

        }

?>
        <div class="q-bsg q-consent">
            <div class="q-consent-bar">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-xl-9 col-lg-8 col-md-7 col-12 content">
                            This website uses cookies for basic functionality, analytics, and marketing. Visit our <a
                            href="<?php echo \get_permalink(); ?>#/modal/consent/tab/privacy/"
                            class="modal-trigger"
                            data-tab-trigger="privacy">Privacy Policy</a> page to find out more.
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-5 col-12 cta">
                            <a class="btn btn-border" href="<?php echo \get_permalink(); ?>#/modal/consent/tab/settings/" class="modal-trigger" data-tab-trigger="settings">
                                SETTINGS
                            </a>

                            <button type="button" class="btn btn-light accept q-consent-set" data-q-consent-marketing="1" data-q-consent-analytics="1">
                                ACCEPT
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php

    }



    public static function modal()
    {

?>
        <div class="q-tab hidden modal-data" data-modal-key="consent">

            <div class="q-bsg q-consent">
                <div class="q-consent-modal">
                    <ul class="q-tab-triggers nav nav-tabs" role="tablist">
                        <li class="nav-item"><a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/settings" class="q-tab-trigger nav-link" data-tab-trigger="settings">Settings</a></li>
                        <li class="nav-item"><a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/privacy" class="q-tab-trigger nav-link" data-tab-trigger="privacy">Privacy</a></li>

                    </ul>

                    <div class="tab-targets">
                        <?php

                        // load up settings tab ##
                        self::settings();

                        // load up privacy tab ##
                        self::privacy();

                        ?>
                    </div>
                </div>
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
        <div class="q-tab-target" data-tab-target="settings">
            <h3>Cookie Consent Settings</h3>
            <p>Greenheart uses cookies to let you interact with our services, and for marketing and advertising purposes. Some of these cookies are strictly necessary for our sites to function and by using this site you agree that you have read and understand our use of cookies.</p>
            <p>Our marketing and advertising cookies are non-essential and you can opt out of using them with this tool. Blocking cookies may impact your experience on our website.</p>

            <div class="settings">
                <div class="setting">
                    <div class="row">
                        <div class="col-sm-3 col-12">
                            <h5>Functional Cookies</h5>
                        </div>

                        <div class="col-sm-7 col-12">
                            <p>These cookies are necessary for our sites to function properly. These cookies secure our forms, support login sessions and remember user dialogue. Because the site does not function without these cookies, opt-out is not available. They are not used for marketing or analytics.</p>
                        </div>

                        <div class="col-sm-2 col-12">
                            <div class="q-consent-wrapper">
                                <?php echo self::option([
                                    'field'     => 'functional',
                                    'value'     => 1, // no opt-out ##
                                    'disabled'  => true
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="setting">
                    <div class="row">
                        <div class="col-sm-3 col-12">
                            <h5>Marketing Cookies</h5>
                        </div>

                        <div class="col-sm-7 col-12">
                            <p>These cookies are used to enhance the relevance of our advertising on social media and to tailor messages relevant to your interests.</p>
                        </div>

                        <div class="col-sm-2 col-12">
                            <div class="q-consent-wrapper">
                                <?php echo self::option([
                                    'field'     => 'marketing',
                                    'value'     => self::$cookie['marketing'],
                                    'disabled'  => false
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="setting">
                    <div class="row">
                        <div class="col-sm-3 col-12">
                            <h5>Analytical Cookies</h5>
                        </div>

                        <div class="col-sm-7 col-12">
                            <p>These cookies collect anonymous data on how visitors use our site and how our pages perform. We use this information to make the best site possible for our users.</p>
                        </div>

                        <div class="col-sm-2 col-12">
                            <div class="q-consent-wrapper">
                                <?php echo self::option([
                                    'field'     => 'analytics',
                                    'value'     => self::$cookie['analytics'],
                                    'disabled'  => false
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="text-right">
                <button type="button" class="btn btn-dark reset q-consent-reset">RESET</button>
                <a
                        href="<?php echo \get_permalink(); ?>#/modal/consent/tab/settings/"
                        data-tab-trigger="settings"
                        class="btn btn-success modal-trigger accept q-consent-set"
                        data-q-consent-marketing="<?php echo self::$cookie['marketing']; ?>"
                        data-q-consent-analytics="<?php echo self::$cookie['analytics']; ?>"
                >SAVE</a>
            </div>
        </div>


<?php

    }



    /**
     * Render Privacy Policy content
     * Tries to get privacy Policy via API on greenheart.org
     * Adds a link to open the settings
     *
     * @todo    make sure privacy policy exists on greenheatrorg
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

?>
        <div class="q-consent-option" data-q-consent-field="<?php echo $args["field"]; ?>">
            <label class="switch">
                <input type="checkbox" value="1" <?php echo $args['disabled'] ? 'disabled' : '' ?> <?php echo $args['value'] == '1' ? 'checked' : '' ?>>
                <span class="slider round"></span>
            </label>
        </div>
<?php

    }
}