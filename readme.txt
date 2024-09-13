=== Petes Simple Contact Form with reCAPTCHA v3 ===
Contributors: peter.co.za
Tags: contact form, ajax, reCAPTCHA, google reCAPTCHA, wp_mail, lightweight
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 1.14
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The most lightweight AJAX contact form with Google reCAPTCHA v3 integration. Minimal bloat and easily customizable.

== Description ==

Petes Simple Contact Form with reCAPTCHA v3 is designed to be a lightweight, minimal contact form solution for WordPress, integrated with Google reCAPTCHA v3 for spam protection.

**Features:**
* Uses AJAX for smooth form submission without page reloads.
* Integrated with Google reCAPTCHA v3 (note: this form only works with reCAPTCHA v3).
* Built on `wp_mail()` for sending emails.
* Minimal design to reduce bloat compared to more comprehensive form plugins.
* Easily customizable for adding new fields (make sure to process additional fields in the `process_form_submission_ajax` function).

If you're experiencing issues with email delivery, we recommend using a mail logging plugin for troubleshooting. If necessary, you can configure an SMTP plugin to handle email sending.

This plugin serves as a lightweight base for forms on websites, helping to minimize unnecessary code overhead.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/petes-simple-contact-form/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure your Google reCAPTCHA v3 keys in the settings page located under "Contact Form Settings" in the WordPress admin.
4. Select the page where you want the contact form to appear.

== Frequently Asked Questions ==

= Why is reCAPTCHA not working? =

Make sure you are using the correct Google reCAPTCHA v3 keys in the settings. Check your browser’s console for errors or verify the form functionality with a mail logging plugin. For further troubleshooting, try setting up an SMTP plugin if emails are not being sent.

= How can I customize the form? =

To add or modify fields, edit the form in the `get_contact_form()` method. Remember to process any new fields you add in the `process_form_submission_ajax()` function to ensure proper handling of the data.

= I’m having issues with email delivery, what can I do? =

If emails are not sending, install a mail logging plugin to help identify the issue. You may also want to use an SMTP plugin to manage email sending.

== Screenshots ==

1. **Form Example:** The lightweight AJAX contact form as it appears on a webpage.
2. **Settings Page:** The settings page where you configure reCAPTCHA and form settings.

== Changelog ==

= 1.14 =
* Initial release of the plugin.
* Added Google reCAPTCHA v3 support.
* AJAX-based form submission.
* Simple and lightweight contact form functionality.

== Upgrade Notice ==

= 1.14 =
Initial release. Ensure you have set up Google reCAPTCHA v3 keys in the settings before using the form.

== License ==
This plugin is licensed under the GPLv2 or later. More information can be found at [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).

