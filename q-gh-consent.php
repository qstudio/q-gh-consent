<?php

/**
 * Build a shared consent, privacy and cookie system
 *
 * @package   Q_GH_Consent
 * @author    Q Studio <social@qstudio.us>
 * @license   GPL-2.0+
 * @link      http://qstudio.us/
 * @copyright 2016 Q Studio
 *
 * @wordpress-plugin
 * Plugin Name:     Consent
 * Plugin URI:      http://qstudio.us/
 * Description:     Build a shared consent, privacy and cookie system
 * Version:         0.1.0
 * Author:          Q Studio
 * Author URI:      http://qstudio.us
 * License:         GPL2
 * Class:           Q_GH_Consent
 * Text Domain:     q-gh-consent
 * Domain Path:     languages/
 * GitHub Plugin URI: qstudio/q-gh-consent
 */

namespace Q_GH_Consent;

use Q_GH_Consent\Admin\Menu;
use Q_GH_Consent\Core\Plugin as Plugin;
use Q_GH_Consent\Theme\Template as Template;

// stop direct access ##
defined( 'ABSPATH' ) OR exit;

// Define our constants ##
( ! defined( 'QGHBB_PATH' ) ) && define( 'QGHBB_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
( ! defined( 'QGHBB_URL' ) ) && define( 'QGHBB_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Load Dependencies
 *
 * @since       0.5
 */
$autoload = array(
        'core/plugin'
    ,   'core/helper'
    ,   'admin/menu'
    #,   'type/taxonomy'
    #,   'type/post-type'
    ,   'theme/template'
    #,   'plugin/gravity-forms'
    #,   'plugin/q-search'
    #,   'admin/ui'
    #,   'widget/brand-bar'
    #,   'ajax/callback'
    #,   'cron/schedule'
    #,   'cron/fetch_students'
    #,   'cron/update_students'
);
foreach ( $autoload as $load ) {
    if ( file_exists( QGHBB_PATH.'includes/'.$load.'.php' ) ) require_once( QGHBB_PATH.'includes/'.$load.'.php' );
}

/**
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, __NAMESPACE__.'\\activation' );
function activation() {

    $instance = new Plugin();
    $instance->activate();

}

register_deactivation_hook( __FILE__, __NAMESPACE__.'\\deactivation' );
function deactivation() {

    $instance = new Plugin();
    $instance->deactivate();

}


/**
 * Instatiate class and run hooks
 */
add_action( 'plugins_loaded', __NAMESPACE__.'\\hook', 5 );

function hook() {

    // new class instance ##
    $instance = new Plugin();
    new Menu();

    // load theme hooks ##
    $instance->run_hooks();

}

/*
API call to render brand bar
*/
function render() {

    Template::render();

}
