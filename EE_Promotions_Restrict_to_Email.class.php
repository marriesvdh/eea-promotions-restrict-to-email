<?php

if (! defined('EVENT_ESPRESSO_VERSION')) {
    exit();
}

use EventEspresso\core\domain\entities\notifications\PersistentAdminNotice;

// define the plugin directory path and URL
define('EE_PROMOTIONS_RESTRICT_TO_EMAIL_BASENAME', plugin_basename(EE_PROMOTIONS_RESTRICT_TO_EMAIL_PLUGIN_FILE));
define('EE_PROMOTIONS_RESTRICT_TO_EMAIL_PATH', plugin_dir_path(__FILE__));
define('EE_PROMOTIONS_RESTRICT_TO_EMAIL_URL', plugin_dir_url(__FILE__));

/**
 * Class  EE_Promotions_Restrict_to_Email
 *
 * @package     eea-promotions-restrict-to-email
 * @author      Marries van de Hoef
 */
class EE_Promotions_Restrict_to_Email extends EE_Addon
{
    /**
     * @throws \EE_Error
     */
    public static function register_addon()
    {
        // register addon via Plugin API
        EE_Register_Addon::register(
            'Promotions_Restrict_to_Email',
            array(
                'version'               => EE_PROMOTIONS_RESTRICT_TO_EMAIL_VERSION,
                'plugin_slug'           => 'espresso_promotions_restrict_to_email',
                'min_core_version'      => EE_PROMOTIONS_RESTRICT_TO_EMAIL_CORE_VERSION_REQUIRED,
                'main_file_path'        => EE_PROMOTIONS_RESTRICT_TO_EMAIL_PLUGIN_FILE,
                'module_paths'          => array( EE_PROMOTIONS_RESTRICT_TO_EMAIL_PATH .
                    'EED_Promotions_Restrict_to_Email.module.php' ),
            )
        );
    }


    /**
     * @see EE_Addon::after_registration
     * Runs after all EE Addons have been loaded
     *
     * @since 4.9.26
     * @return void
     */
    public function after_registration()
    {
        $this->check_if_promotions_is_active();
    }


    /**
     * Checks if the promotions addon is active since this addon extends it.
     * Deactivates the plugin if the promotion addon could not be found.
     *
     * @return void
     */
    public function check_if_promotions_is_active()
    {
        if (
            !class_exists('EE_Promotions')
             || !isset(EE_Registry::instance()->addons->EE_Promotions)
             || !EE_Registry::instance()->addons->EE_Promotions instanceof EE_Promotions
        ) {
            if (! function_exists('deactivate_plugins')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            deactivate_plugins(EE_PROMOTIONS_RESTRICT_TO_EMAIL_BASENAME);

            //phpcs:disable Generic.Files.LineLength.TooLong
            new PersistentAdminNotice(
                'auto-deactivated-promotions-restrict-to-email',
                esc_html__('The "Event Espresso - Promotions Restrict to Email" plugin has been deactivated automatically because the required "Event Espresso - Promotions" was not activated.', 'ee-promotion-restrict-to-email'),
                true
            );
            //phpcs:enable
        }
    }
}
