<?php

namespace Q_GH_Consent\Admin;

use Q_GH_Consent\Core\Plugin as Plugin;

class Menu {

    public function __construct()
    {

        \add_action( 'admin_menu', array( $this, 'admin_menu' ) );

    }

    public function admin_menu()
    {

        \add_options_page( 'Consent System', 'Consent System', 'manage_options', Plugin::$name, function() {

            // these settings should be filtered into the global Q settings ##
            // @todo - Ray to build API from Q to allow new menu items to be added to settings page and saved to db

            // validate
            if ( 
                $_POST 
                && isset($_POST['action']) 
                && Plugin::$name === $_POST['action'] ) {
                
                // sanitize ##
                $settings['consent'] = intval( $_POST['settings']['consent'] );

                // save ##
                if ( \update_option( Plugin::$name, $settings ) ) {
                    
                    print '<div class="updated"><p><strong>Settings saved.</strong></p></div>';

                }
            }

            // get setting from db ##
            $settings = \get_option( Plugin::$name );

?>
            <h1>Consent System</h1>

            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th>
                            Show Promo Bar
                        </th>
                        <td>
                            Off
                            <input type="radio" name="settings[consent]" value="0" checked />
                            On
                            <input type="radio" name="settings[consent]" value="1" <?php \checked( $settings['promo'], 1 ); ?> />
                        </td>
                    </tr>
                </table>

                <input name="nonce" type="hidden" value="<?php echo \esc_attr( \wp_create_nonce( Plugin::$name ) ); ?>" />
                <input name="action" type="hidden" value="<?php echo \esc_attr( Plugin::$name ); ?>" />
                <input type="submit" class="button-primary" value="Save" />
            </form>
<?php

        });
    }
}