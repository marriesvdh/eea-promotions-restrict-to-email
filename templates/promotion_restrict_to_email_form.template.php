<?php

/**
 * Template file for promotion restrict to email details form content
 *
 * @since 1.0.0
 *
 * @package     eea-promotions-restrict-to-email
 * @author      Marries van de Hoef
 *
 */

/**
 * The following template variables are available in this template:
 *
 * @type string     $restrict_to_email_addresses
 */

//phpcs:disable Generic.Files.LineLength.TooLong
?>
<table class="form-table" id="promotion-details-form-restrict-to-email">
    <tr>
        <th scope="row">
            <label for="PRX_emailrestrict"><?php esc_html_e('Restrict to email address(es)', 'ee-promotion-restrict-to-email'); ?></label>
        </th>
        <td class="field-column">
            <input type="text" class="regular-text" id="PRX_emailrestrict" name="PRX_emailrestrict" value="<?php echo $restrict_to_email_addresses; ?>">
            <p class="description"><?php esc_html_e('This promotion can only be used when any of the attendees use one of these e-mail addresses. Separate multiple e-mail addresses with commas.', 'ee-promotion-restrict-to-email'); ?></p>
        </td>
    </tr>
</table>
