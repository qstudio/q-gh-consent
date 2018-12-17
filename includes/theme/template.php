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

        //TESTING ONLY! #HACK 
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
                            </div>
                        </div>
                    </div> 
            <div class="row d-block">
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
            <!-- <?php echo api::privacy(); ?> TODO - Reinstate dynamic Privacy fetching -->



<h3>Privacy Policy</h3>
<p>Thank you for visiting our website. This privacy policy tells you how Greenheart Exchange Online uses personally identifying information (PII) collected at this site. Please read this privacy policy before using the site or submitting any personal information. By using the site, you are accepting the practices described in this privacy policy. These practices may be changed, but any changes will be posted and changes will only apply to activities and information on a going forward, not retroactive basis. You are encouraged to review the privacy policy whenever you visit the site to make sure that you understand how any personal information you provide will be used.</p>
<p>Note that the privacy practices set forth in this privacy policy are for this website only. If you link to other websites, please review the privacy policies posted at those sites.</p>
<p><strong>Collection of Personally Identifying Information</strong><br>
PII includes names, postal addresses, email addresses, etc., when voluntarily submitted by our visitors. The information you provide is used to fulfill your specific request. This information is only used to fulfill your specific request, unless you give us permission to use it in another manner, for example to add you to one of our mailing lists. If you identify yourself to us by sending us an email with questions or comments. We may, at our sole discretion, either file your comments for future reference or discard the information after we have received it.</p>
<p>We exercise great care in providing secure transmissions of your PII from your PC to our servers. Our secure server software encrypts information, ensuring that Internet transactions remain private (unless sent by an unsecured means such as email). Neither Greenheart Exchange nor any telephone network nor service providers we utilize are responsible for incorrect or inaccurate transcriptions of information or for any human error, technical malfunctions, lost-delayed data transmission, omission, interruption, deletion, defect, line failures or any telephone network, computer equipment, software, inability to access any website or on-line service or any other error or malfunction or misdirected entries.</p>
<p><strong>Cookie/Tracking Technology</strong><br>
The site may use cookie and tracking technology to collect non-personal identifying information depending on the features offered. Non-PII might include the browser you use, the type of computer operating system you use, the Internet service providers and other similar information. Our system also automatically gathers information about the areas you visit on our sites and about the links you may select from within our site to the other areas of the World Wide Web or elsewhere online. We use such information in the aggregate to understand how our users as a group use the services and resources provided on our site. This way we know which areas of our sites are favorites of our users, which areas need improvement and what technologies are being used so that we may continually improve our sites. Cookie and tracking technology are useful for gathering information such as identifying a website user’s browser type and operating system, tracking the number of visitors to the site, and understanding how visitors use the site. Cookies can also help customize the site for visitors. Personal information cannot be collected via cookies and other tracking technology, however, if you previously provided personally identifiable information, cookies may be tied to such information. Aggregate cookie and tracking information may be shared with third parties but that aggregate information does not identify individual website users. Our web servers do not record email addresses of the visitors unless information is submitted by users. We may determine what technology is available through your browser in order to provide you with the most appropriate version of a web page.</p>
<p><strong>Distribution of Information</strong><br>
We may share information gathered by us from our website with governmental agencies or other companies assisting us in fraud prevention or investigation. We may do so when: (1) permitted or required by law; or, (2) trying to protect against or prevent actual or potential fraud or unauthorized transactions; or, (3) investigating fraud which has already taken place. The information is not provided to these companies for marketing purposes.</p>
<p>Third parties who provide hosting services or other day-to-day services that make possible the operation of this website may have access to information that you provide us to the extent those third parties require access to our databases to service the website.</p>
<p>We reserve the right to transfer information we have secured from you in connection with the sale or transfer of all or part of our assets. We are not responsible for any breach of security or for any actions of any third parties that receive information from us.</p>
<p>This website is operated in the United States of America. If you are located outside of the United States, please be aware that any information you provide will be transferred to the United States. By using this website, you consent to this transfer.</p>
<p><strong>Commitment to Data Security</strong><br>
Your personally identifiable information is kept secure. All emails and newsletters from this site allow you to opt out of further mailings.</p>
<p><strong>Privacy Contact information</strong><br>
If you have any questions, concerns, or comments about our privacy policy you may contact us using the information below:<br>
By email: support@greenheart.org<br>
By phone: 312-944-2544</p>
<p>We reserve the right to make changes to this policy at any time and you should check this policy periodically for updates. Any changes to this policy will be posted here.</p>














            <!-- END PRIVACY POLICY HACK -->
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