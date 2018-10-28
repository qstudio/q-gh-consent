<?php

namespace Q_GH_Consent\Admin;
use Q_GH_Consent\Core\Plugin as Plugin;

class Menu {
    public function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function admin_menu()
    {
        add_options_page( 'Branding Bar', 'Branding Bar', 'manage_options', Plugin::$name, function() {

            // validate
            if ($_POST && isset($_POST['action']) && Plugin::$name === $_POST['action'] ) {
                // sanitize
                $settings['promo'] = intval($_POST['settings']['promo']);

                // save
                if ( update_option(Plugin::$name, $settings) ) {
                    print '<div class="updated"><p><strong>Settings saved.</strong></p></div>';
                }
            }

            $settings = get_option(Plugin::$name);
            ?>
            <h1>Branding Bar Settings</h1>

            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th>
                            Show Promo Bar
                        </th>
                        <td>
                            Off
                            <input type="radio" name="settings[promo]" value="0" checked />
                            On
                            <input type="radio" name="settings[promo]" value="1" <?php checked( $settings['promo'], 1 ); ?> />
                        </td>
                    </tr>
                </table>

                <input name="nonce" type="hidden" value="<?php echo esc_attr( wp_create_nonce( Plugin::$name ) ); ?>" />
                <input name="action" type="hidden" value="<?php echo esc_attr(Plugin::$name); ?>" />
                <input type="submit" class="button-primary" value="Save" />
            </form>
            <?php
        });
    }
}