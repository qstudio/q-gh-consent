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
                'ajax_nonce'    => \wp_create_nonce( 'q_consent' )
            ,   'ajax_url'      => \get_home_url( '', 'wp-admin/admin-ajax.php' )
            ,   'saved'         => __( "Saved!", 'q-consent' )
            ,   'disabled'      => __( "Functional Cookies cannot be disabled", 'q-consent' )
        );
        \wp_localize_script( 'q-consent-js', 'q_consent', $translation_array );

        // enqueue the script ##
        \wp_enqueue_script( 'q-consent-js' );

        // @todo - add styles ##
        // wp_register_style( 'q-consent-css', Q_CONSENT_URL.'scss/index.css', '', plugin::$version );
        // wp_enqueue_style( 'q-consent-css' );

        //TESTING ONLY! #HACK @todo - Benny, is this hack required, please explain ? ##
        \wp_enqueue_style('bs_hack', Q_CONSENT_URL.'deletes/rootstrap.css', array(), '4.0', false );
        #\wp_enqueue_style( 'bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css' );

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

            helper::log( 'Consent already given, so do not display bar' );

            return false;

        }

        // check if the user is in the EU contient, for GDPR compliance
        if ( 
            ! helper::is_localhost()
            && ! geotarget::is_eu() ) {

            helper::log( 'User is outside the EU, so we do not need to show the bar' );

            return false;

        }

?>
<div class="q-bsg">
    <div class="q-consent-bar card text-white">
        <i class="cross d-none d-md-block"></i>
        <div class="row card-body">
            <div class="content col-6 col-md-8 ">
                This website uses cookies for basic functionality, analytics, and marketing. Visit our <a 
                    href="<?php echo \get_permalink(); ?>#/modal/consent/tab/privacy/" 
                    class="modal-trigger"
                    data-tab-trigger="privacy">Privacy Policy</a> page to find out more.
            </div>
            
            <div class="col-6 col-md-4 cta float-right">
                <div class="d-inline-block float-right">
                    <a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/settings/" class="modal-trigger" data-tab-trigger="settings">
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

            <div class="q-bsg">
                <ul class="q-tab-triggers nav nav-tabs" role="tablist">
                    <li class="nav-item"><a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/settings" class="q-tab-trigger nav-link active" data-tab-trigger="settings">Settings</a></li>
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
                                                'value'     => 1, // no opt-out ##
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
                            </div>
                        </div>
                    </div> 
            <div class="row d-block">
                <div class="col-6 col-md-4 cta float-right">
                    <div class="d-inline-block float-right">
                        <a href="<?php echo \get_permalink(); ?>#/modal/consent/tab/settings/" class="modal-trigger" data-tab-trigger="settings">
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
                    <label>off/on</label>
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
body .q-bsg label {
    display: block;
    font-size: xx-small;
    color: #888;
    margin-bottom: .5rem;
}

.q-consent-option > .disabled { cursor: not-allowed; }

.q-tab-target .row .col-12 {
    padding: 0;
}

/* Top Banner */
body .q-bsg .q-consent-bar.card {
    background: #00A8DC; 
    color: #fff;
    border-radius: 0;
    border: 0;
    line-height:38px;   
    /* background-color: #00a9e0; @TODO - Why is our Snackbar Azure color different than our Zeplin Styleguide...they are too similar to not match */
    border-radius: 0;
}
            
/* generic damn */
.q-hidden { display: none; }

/* tabs */


/* tab triggers */
.tab-trigger { display: inline; }
.tab-trigger.active { font-weight: bold; }

/* tab targets */
.tab-target { display: none; }
.tab-target.active { display: block; }

body .q-tab-target .col-12.options {
    padding-left: 0;
    padding-right: 0;
}
body .q-tab-target .container, 
body .q-tag-target .description {
    padding-left: 0;
    padding-right: 0;
}
.tab-targets {
    margin-bottom: 40px;
}

body .q-bsg .q-consent-bar.card a {
    display: unset;
    display: inline;
    color: white;
    text-decoration: underline !important;
}
body .q-bsg.featherlight-inner a {
    color: #8ac53f;
}
body .q-bsg.featherlight-inner a:hover {
    color: #7cb138;
}
.q-consent-bar .card-body,
.q-consent-bar .card-body div,
.q-consent-bar .card-body p {
    color: white;
    font-size: 11pt;
    line-height:38px;   
}



/* Typography Reset */
body .q-bsg h1, body .q-bsg h2,body .q-bsg h3, body .q-bsg h4, body .q-bsg h5 {
    font-family: "Sanchez", Georgia, serif;
}
body .q-bsg h1, body .q-bsg h2, body .q-bsg h3 {
font-weight: 300;
}
body .q-bsg {
    font-family: "Lato", Georgia, serif;
}
/* Button Reset */
body .q-bsg button.btn-primary {
    margin: 0 10px;
    background-color: #8ac53f;
    border-color: #7cb138;
}
body .q-bsg button.btn-primary:hover {
    background-color: #7cb138;
    border-color: #8ac53f;
    box-shadow: none;
}
body .q-bsg button.btn-primary:disabled {
    background-color: #777;
}

/* Modal panel */
body .featherlight-content .q-bsg > .q-tab-triggers  {
    margin-left: 0;
    margin-bottom: 40px;
}
body .q-bsg .q-consent-wrapper{
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    width: 100px;
    height: 100px;
    -ms-flex-wrap: wrap;
        flex-wrap: wrap;
    padding-top: 6px;

}

body .q-bsg [type="checkbox"]:not(:checked), body .q-bsg [type="checkbox"]:checked, body .q-bsg [type="radio"]:not(:checked), body .q-bsg [type="radio"]:checked {
    position: relative;
    left: 0;
}
/* PLUGIN Toggle Switch Credit: */
body .q-bsg .q-consent-option > .q-switch-box:last-of-type {
    opacity: 0;
    height: 0px;
    width: 0px;
}
body .q-bsg .q-consent-option {
    position: absolute;
    right: 15px;
}
body .q-bsg .q-consent-padding {
    margin-bottom:30px;
}
body .q-bsg span.toggle_legend {
    float: right;
    display: block;
    text-align: right;
    width: 100%;
    opacity: .4;
    font-size: 60%;
    padding-right: 15px;
}
body .q-bsg .q-switch_box {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    max-width: 50px;
    min-width: 50px;
    height: 50px;
    -webkit-box-pack: center;
        -ms-flex-pack: center;
            justify-content: center;
    -webkit-box-align: center;
        -ms-flex-align: center;
            align-items: center;
    -webkit-box-flex: 1;
        -ms-flex: 1;
            flex: 1;
}

/* Switch 1 Specific Styles Start */

body .q-bsg .box_1{
    background: transparent;
}

body .q-bsg input[type="checkbox"].switch_1{
    font-size: 15px;
    -webkit-appearance: none;
       -moz-appearance: none;
            appearance: none;
    width: 2em;
    height: 1em;
    background: #ddd;
    border-radius: 2em;
    position: relative;
    cursor: pointer;
    outline: none;
    -webkit-transition: all .2s ease-in-out;
    transition: all .2s ease-in-out;
  }
  
body .q-bsg input[type="checkbox"].switch_1:checked{
    background: #8ac53f;
  }
  
body .q-bsg input[type="checkbox"].switch_1:after{
    position: absolute;
    content: "";
    width: 1em;
    height: 1em;
    border-radius: 50%;
    background: #fff;
    -webkit-box-shadow: 0 0 .25em rgba(0,0,0,.3);
            box-shadow: 0 0 .25em rgba(0,0,0,.3);
    -webkit-transform: scale(.7);
            transform: scale(.7);
    left: 0;
    -webkit-transition: all .2s ease-in-out;
    transition: all .2s ease-in-out;
  }
  
body .q-bsg input[type="checkbox"].switch_1:checked:after{
    left: calc(100% - 1em);
  }


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