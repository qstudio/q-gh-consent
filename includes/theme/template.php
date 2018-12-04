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


        <!-- DEPRECATED GET RID OF SOON
            
            <div class="content col-10 col-md-8">
                Generic short text about GDPR, Consent and <a href="https://greenheartorg.staging.wpengine.com/#/modal/consent/tab/privacy/" class="modal-trigger" data-tab-trigger="privacy">
                    Privacy Policy
                </a>
            </div>
            
            <div class="col-2 col-md-4 cta float-right">
              <div class="d-inline-block float-right">
                  <a href="https://greenheartorg.staging.wpengine.com/#/modal/consent/tab/settings/" class="modal-trigger" data-tab-trigger="settings">
                    <button type="button" class="btn btn-primary">
                          SETTINGS
                    </button>
                  </a>
              </div>
              <div class="d-inline-block float-right">
                  <button type="button" class="btn btn-primary accept q-consent-set" data-q-consent-marketing="1" data-q-consent-analytics="1">
                      ACCEPT
                  </button>
              </div>
                
            </div>
            
        </div>
    </div>
</div>


-->




<div class="bs4">
    <div class="q-consent-bar q-bsg card text-white">
        <i class="cross d-none d-md-block"></i>
        <div class="row card-body">
            <div class="content col-6 col-md-8 ">
                This website uses cookies for basic functionality, analytics, and marketing. Visit our <a 
                    href="<?php echo \get_permalink(); ?>#/modal/consent/tab/privacy/" 
                    class="modal-trigger"
                    data-tab-trigger="privacy">
                    Privacy Policy
                </a> page to find out more.
            </div>
            
            <div class="col-6 col-md-4 cta float-right">
                <div class="d-inline-block float-right">
                    <a href="https://greenheartorg.staging.wpengine.com/#/modal/consent/tab/settings/" class="modal-trigger" data-tab-trigger="settings">
                        <button type="button" class="btn btn-primary">
                              SETTINGS
                        </button>
                    </a>
                </div>
                <div class="d-inline-block float-right">
                    <button type="button" class="btn btn-primary accept q-consent-set" data-q-consent-marketing="1" data-q-consent-analytics="1">
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

            <div class="q-tab-triggers btn-group">
                <button type="button" class="btn btn-primary"><a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/settings" class="q-tab-trigger" data-tab-trigger="settings">Settings</a></button>
                <button type="button" class="btn btn-primary"><a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/privacy" class="q-tab-trigger" data-tab-trigger="privacy">Privacy</a></button>
                
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
        <div class="q-tab-target col-12 " data-tab-target="settings"> 
                    <div class="row">
                        <h2 class="text-center">Cookie Consent Settings</h2>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p>Greenheart uses cookies to let you interact with our services, and for marketing and advertising purposes. Some of these cookies are strictly necessary for our sites to function and by using this site you agree that you have read and understand our use of cookies. <br /><br />
                            Our marketing and advertising cookies are non-essential and you can opt out of using them with this tool. Blocking cookies may impact your experience on our website.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 options">
                            <div class="container">
                                <div class="row">
                                    <div class="description col-8 col-md-10">
                                        <h5>Functional Cookies</h5>
                                        <p>These cookies are necessary for our sites to function properly. These cookies secure our forms, support login sessions and remember user dialogue. Because the site does not function without these cookies, opt-out is not available. They are not used for marketing or analytics.</p>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <div class="q-consent-wrapper">
                                            <?php echo self::option([
                                                'field'     => 'functional',
                                                'value'     => 1, // no opt-out
                                                'disabled'  => true
                                            ]); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="description col-8 col-md-10">
                                        <h5>Marketing Cookies</h5>
                                        <p>These cookies are used to enhance the relevance of our advertising on social media and to tailor messages relevant to your interests.</p>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <div class="q-consent-wrapper">
                                            <?php echo self::option([
                                                'field'     => 'marketing',
                                                'value'     => self::$cookie['marketing'],
                                                'disabled'  => false
                                            ]); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="description col-8 col-md-10">
                                        <h5>Analytical Cookies</h5>
                                        <p>These cookies collect anonymous data on how visitors use our site and how our pages perform. We use this information to make the best site possible for our users.</p>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <div class="q-consent-wrapper">
                                            <?php echo self::option([
                                                'field'     => 'analytics',
                                                'value'     => self::$cookie['analytics'],
                                                'disabled'  => false
                                            ]); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="description col-10 col-md-10"> 
                                        <p>Concerned about privacy? Read about <a href="https://www.pcmag.com/feature/359951/how-to-prevent-facebook-from-sharing-your-personal-data" target="_blank">how to stop Facebook from sharing your personal data</a> or click here to <a href="https://tools.google.com/dipage/gaoptout">opt out of Google Analytics tracking</a>, or visit our <a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/privacy" class="q-tab-trigger" data-tab-trigger="privacy">Privacy Page</a> for more details.
                                        </p>   
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
            <div class="row">
                <div class="col-6 col-md-4 cta float-right">
                    <div class="d-inline-block float-right">
                        <a href="https://greenheartorg.staging.wpengine.com/#/modal/consent/tab/settings/" class="modal-trigger" data-tab-trigger="settings">
                            <button 
                            class="btn accept q-consent-set btn-primary"
                            data-q-consent-marketing="<?php echo self::$cookie['marketing']; ?>" 
                            data-q-consent-analytics="<?php echo self::$cookie['analytics']; ?>"
                            disabled
                            >
                            SAVE
                            </button>
                        </a>
                    </div>
                    <div class="d-inline-block float-right">
                        <button type="button" class="btn reset q-consent-reset">
                            RESET
                        </button>
                    </div>
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

                class="q-switch-box box_1 <?php echo $key['class']; ?> <?php echo $key['disabled']; ?>" 
                data-q-consent-value="<?php echo $key['value']; ?>">
                        <input type="checkbox" class="switch_1" checked>
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

        .tab-targets { margin-bottom: 40px; }

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

        /* following along with the hacks */
        .q-consent-bar.card {
            background: #00A8DC; /* system dialogue color */
            color: #fff;
            border-radius: 0;
            border: 0;
            line-height:38px;
        }
        .q-consent-bar .row.card-body button {
            margin:0 10px;
        }
        .q-consent-bar .btn-primary {   
            background-color: #8ac53f; /* button against system dialogue color */
            border-color: lightblue;
        }
        .q-consent-bar .btn-primary:hover {
            background-color: #7cb138;
            border-color: darkcyan;
        }

        </style>
<?php

    }



}