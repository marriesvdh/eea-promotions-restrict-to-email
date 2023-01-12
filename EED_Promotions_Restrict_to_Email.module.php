<?php

if (! defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}
/**
 * Class EED_Promotions_Restrict_to_Email
 *
 * Provides the hooks setup and execution, which contains the main functionality of the addon
 *
 * @package         eea-promotions-restrict-to-email
 * @author          Marries van de Hoef
 *
 * ------------------------------------------------------------------------
 */
class EED_Promotions_Restrict_to_Email extends EED_Module
{
    public const META_KEY_PROMOTION_RESTRICT_TO_EMAIL_ADDRESSES = 'ee_promotion_restrict_to_email_addresses';

    /**
     * Reference to the Promotions admin page object. Used as storage in between callbacks.
     *
     * @var Promotions_Admin_Page $_admin_page
     */
    protected static $_admin_page;


    /**
     * @return EED_Promotions_Restrict_to_Email
     */
    public static function instance()
    {
        return parent::get_instance(__CLASS__);
    }

     /**
      *     set_hooks - for hooking into EE Core, other modules, etc
      *
      *  @access    public
      *  @return    void
      */
    public static function set_hooks()
    {
    }

     /**
      *     set_hooks_admin - for hooking into EE Admin Core, other modules, etc
      *
      *  @access    public
      *  @return    void
      */
    public static function set_hooks_admin()
    {
        // Add metabox to the edit/new promotion admin page
        add_filter(
            'FHEE__Promotions_Admin_Page__page_setup__page_config',
            array('EED_Promotions_Restrict_to_Email', 'extend_admin_page_config'),
            1,
            2
        );

        // Save updates on the edit/new promotion admin page
        add_action(
            'AHEE__Promotions_Admin_Page___insert_update_promotion__after',
            array('EED_Promotions_Restrict_to_Email','save_promotion_extras'),
            1,
            2
        );

        /*
         * Check emailaddress when the promotion is applied.
         * Note: this has to be in the admin hooks function because an ajax request is considered an admin request.
         */
        add_filter(
            'FHEE__EED_Promotions__get_applicable_items__applicable_items',
            array('EED_Promotions_Restrict_to_Email','restrict_application_to_email_addresses'),
            1,
            2
        );
    }

    /**
     * @return EE_Promotions_Restrict_to_Email_Config
     */
    public function config()
    {
        return EE_Registry::instance()->addons->EE_Promotions_Restrict_to_Email->config();
    }

     /**
      *    run - initial module setup
      *
      * @access    public
      * @param  WP $WP
      * @return    void
      */
    public function run($WP)
    {
    }



    /*************** ADMIN HOOKS ****************/
    /**
     * Adds callback function for creating the metabox
     *
     * @param array $page_config
     * @param Promotions_Admin_Page $_admin_page
     *
     * @return array
     */
    public static function extend_admin_page_config(array $page_config, Promotions_Admin_Page $_admin_page)
    {
        // Save this object for later use
        self::$_admin_page = $_admin_page;

        // Add the configuration metabox to the admin page
        $restrict_to_email_metabox_function =
            array('EED_Promotions_Restrict_to_Email', 'add_restrict_to_email_metabox');
        $page_config['edit']['metaboxes'][] = $restrict_to_email_metabox_function;
        $page_config['create_new']['metaboxes'][] = $restrict_to_email_metabox_function;
        return $page_config;
    }

    /**
     * Adds the metabox to the add/edit promotions admin page
     *
     * @return void
     */
    public static function add_restrict_to_email_metabox()
    {
        add_meta_box(
            'promotion-restrict-to-email-mbox',
            esc_html__('Restrict promotion to email', 'eea-promotions-restrict-to-email'),
            array('EED_Promotions_Restrict_to_Email', 'restrict_to_email_metabox_content'),
            self::$_admin_page->wp_page_slug(),
            'normal',
            'default'
        );
    }

    /**
     * Create the metabox using the template
     *
     * @return void
     */
    public static function restrict_to_email_metabox_content()
    {
        // The promotion object is protected in the admin page, so re-retrieve it.
        $promotion = !empty(self::$_admin_page->get_request_data()['PRO_ID'])
            ? EEM_Promotion::instance()->get_one_by_ID(self::$_admin_page->get_request_data()['PRO_ID'])
            : null;

        // Retrieve the stored addresses, if available
        $email_addresses = $promotion !== null
            ? $promotion->get_extra_meta(self::META_KEY_PROMOTION_RESTRICT_TO_EMAIL_ADDRESSES, true)
            : '';

        // Load the template
        $form_args = array(
                'restrict_to_email_addresses' => $email_addresses,
            );
        EEH_Template::display_template(
            EE_PROMOTIONS_RESTRICT_TO_EMAIL_PATH . 'templates/promotion_restrict_to_email_form.template.php',
            $form_args
        );
    }

    /**
     * Sanitizes and saves the metabox user input
     *
     * @param EE_Promotion $promotion
     * @param array $req_data
     *
     * @return void
     */
    public static function save_promotion_extras(EE_Promotion $promotion, array $req_data)
    {
        $emailrestrict_form_data = $req_data['PRX_emailrestrict'];

        // Check input
        $email_addresses = explode(',', $emailrestrict_form_data);
        $email_validator = new EE_Email_Validation_Strategy();
        $valid_email_addresses = array();
        foreach ($email_addresses as $email) {
            $email = trim($email);
            try {
                $email_validator->validate($email);
                $valid_email_addresses[] = $email;
            } catch (EE_Validation_Error $e) {
                EE_Error::add_error(
                    sprintf(
                        esc_html__('Invalid email address: %1$s', 'eea-promotions-restrict-to-email'),
                        esc_html($email)
                    ),
                    '', // empty to allow for multiple errors to be printed
                    '',
                    ''
                );
            }
        }

        // Save sanitized input
        $promotion->update_extra_meta(
            self::META_KEY_PROMOTION_RESTRICT_TO_EMAIL_ADDRESSES,
            implode(',', $valid_email_addresses)
        );
    }

    /*************** FRONT-END HOOKS ****************/
    /**
     * Adds additional check if the promotion can be applied. If the promotion is restricted to one or more email
     * addresses, it checks if any of the email addresses is listed as attendee.
     *
     * @param EE_Line_Item[] $applicable_items
     * @param EE_Promotion $promotion
     *
     * @return EE_Line_Item[]
     */
    public static function restrict_application_to_email_addresses(array $applicable_items, EE_Promotion $promotion)
    {
        if (empty($applicable_items)) {
            return $applicable_items;
        }

        // Retrieve allowed email addresses
        $allowed_email_addresses = $promotion->get_extra_meta(
            self::META_KEY_PROMOTION_RESTRICT_TO_EMAIL_ADDRESSES,
            true
        );

        // Check if there are no restrictions
        if ($allowed_email_addresses === null || $allowed_email_addresses === "") {
            return $applicable_items;
        }


        // Retrieve attendees of the current transaction
        $transaction_id = $applicable_items[0]->TXN_ID();
        $EEM_Attendee = EE_Registry::instance()->load_model('Attendee');

        // Get primary attendee
        $attendees = $EEM_Attendee->get_all(
            array(
                array(
                    'Registration.Transaction.TXN_ID' => $transaction_id,
                    'Registration.REG_count' => 1 // TODO: make this limitation on primary registrants an option
                ),
            )
        );

        // Fetch the attendee's email addresses
        $attendees_email = array();
        foreach ($attendees as $attendee) {
            $attendees_email[] = strtolower($attendee->email());
        }

        // Find any matches between the attendee email addresses and the allowed email addresses
        $matches = array_intersect(explode(',', strtolower($allowed_email_addresses)), $attendees_email);

        if (!empty($matches)) {
            return $applicable_items; // The promotion is allowed!
        }

        // No matches found. Promotion is rejected for this transaction.
        return array();
    }
}
