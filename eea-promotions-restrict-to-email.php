<?php
/*
  Plugin Name: Promotions Restrict to Email for Event Espresso
  Description: Allows the Event Espresso Promotions Addon to be limited to specific e-mail addresses.
  Version: 1.0
  Author: Marries van de Hoef
  Author URI:
  License: GPL2
  Requires at least: 4.6
  TextDomain: eea-promotions-restrict-to-email


  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
 *
 * ------------------------------------------------------------------------
 */
/*
 * This file was auto-generated from the Event Espresso New Addon template.
 */
// define versions and this file
define('EE_PROMOTIONS_RESTRICT_TO_EMAIL_CORE_VERSION_REQUIRED', '4.8.0.rc.0000');
define('EE_PROMOTIONS_RESTRICT_TO_EMAIL_VERSION', '1.0.0');
define('EE_PROMOTIONS_RESTRICT_TO_EMAIL_PLUGIN_FILE', __FILE__);



/**
 *    captures plugin activation errors for debugging
 */
function espresso_promotions_restrict_to_email_plugin_activation_errors()
{

    if (WP_DEBUG) {
        $activation_errors = ob_get_contents();
        file_put_contents(
            EVENT_ESPRESSO_UPLOAD_DIR . 'logs/espresso_promotions_restrict_to_email_plugin_activation_errors.html',
            $activation_errors
        );
    }
}
add_action('activated_plugin', 'espresso_promotions_restrict_to_email_plugin_activation_errors');



/**
 *    registers addon with EE core
 */
function load_espresso_promotions_restrict_to_email()
{
    if (class_exists('EE_Addon')) {
        // promotions_restrict_to_email version
        require_once(plugin_dir_path(__FILE__) . 'EE_Promotions_Restrict_to_Email.class.php');
        EE_Promotions_Restrict_to_Email::register_addon();
    } else {
      add_action('admin_notices', 'espresso_promotions_restrict_to_email_activation_error');
    }
}
add_action('AHEE__EE_System__load_espresso_addons', 'load_espresso_promotions_restrict_to_email');



/**
 *    verifies that addon was activated
 */
function espresso_promotions_restrict_to_email_activation_check()
{
    if (! did_action('AHEE__EE_System__load_espresso_addons')) {
      add_action('admin_notices', 'espresso_promotions_restrict_to_email_activation_error');
    }
}
add_action('init', 'espresso_promotions_restrict_to_email_activation_check', 1);



/**
 *    displays activation error admin notice
 */
function espresso_promotions_restrict_to_email_activation_error()
{
  unset($_GET['activate']);
  unset($_REQUEST['activate']);
    if (! function_exists('deactivate_plugins')) {
      require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
  deactivate_plugins(plugin_basename(EE_PROMOTIONS_RESTRICT_TO_EMAIL_PLUGIN_FILE));
    ?>
  <div class="error">
    <p><?php
        //phpcs:disable Generic.Files.LineLength.TooLong
        printf(esc_html__('Event Espresso Promotions Restrict to Email could not be activated. Please ensure that Event Espresso version %1$s or higher is running', 'eea-promotions-restrict-to-email'), EE_PROMOTIONS_RESTRICT_TO_EMAIL_CORE_VERSION_REQUIRED);
        //phpcs:enable
    ?></p>
  </div>
<?php
}
