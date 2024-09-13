<?php
/*
Plugin Name: Petes Simple Contact Form with reCAPTCHA v3
Description: A simple contact form plugin with configurable Google reCAPTCHA v3 keys and editable success message.
Version: 1.14
Author: Pete
*/

class Simple_Contact_Form_With_Captcha_v3 {

    const NONCE_ACTION = 'scf_submit_nonce';
    const NONCE_NAME = 'scf_nonce';

    public function __construct() {
        // Create admin menu
        add_action('admin_menu', array($this, 'create_admin_menu'));

        // Save settings
        add_action('admin_init', array($this, 'save_settings'));

        // Append contact form to the selected page
        add_filter('the_content', array($this, 'append_contact_form'));

        // Handle form submission via AJAX
        add_action('wp_ajax_nopriv_scf_form_submit', array($this, 'process_form_submission_ajax'));
        add_action('wp_ajax_scf_form_submit', array($this, 'process_form_submission_ajax'));

        // Conditionally enqueue necessary scripts and styles for the form
        add_action('wp_enqueue_scripts', array($this, 'conditionally_enqueue_scripts'));
    }

    /**
     * Create menu in admin.
     */
    public function create_admin_menu() {
        add_menu_page(
            esc_html__('Contact Form Settings', 'simple-contact-form'),
            esc_html__('Contact Form Settings', 'simple-contact-form'),
            'manage_options',
            'simple_contact_form',
            array($this, 'settings_page')
        );
    }

    /**
     * The settings page in admin.
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Simple Contact Form Settings', 'simple-contact-form'); ?></h1>
            <p><a href="https://www.google.com/recaptcha/admin/create" target="_blank"><?php esc_html_e('Click here to generate Google reCAPTCHA keys', 'simple-contact-form'); ?></a></p>
            <form method="post" action="options.php">
                <?php
                settings_fields('simple_contact_form_settings');
                do_settings_sections('simple_contact_form');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register settings.
     */
    public function save_settings() {
        register_setting('simple_contact_form_settings', 'simple_contact_form_page_id', array('sanitize_callback' => 'absint'));
        register_setting('simple_contact_form_settings', 'simple_contact_form_email', array('sanitize_callback' => 'sanitize_email'));
        register_setting('simple_contact_form_settings', 'simple_contact_form_site_key', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('simple_contact_form_settings', 'simple_contact_form_secret_key', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('simple_contact_form_settings', 'simple_contact_form_success_message', array('sanitize_callback' => 'sanitize_textarea_field'));

        add_settings_section('simple_contact_form_section', '', null, 'simple_contact_form');

        $this->add_settings_field('simple_contact_form_page', 'Which page to add this form?', 'pages_dropdown', 'simple_contact_form_section');
        $this->add_settings_field('simple_contact_form_email', 'Where do we send the forms?', 'email_input_field', 'simple_contact_form_section');
        $this->add_settings_field('simple_contact_form_site_key', 'Google reCAPTCHA Site Key', 'site_key_input_field', 'simple_contact_form_section');
        $this->add_settings_field('simple_contact_form_secret_key', 'Google reCAPTCHA Secret Key', 'secret_key_input_field', 'simple_contact_form_section');
        $this->add_settings_field('simple_contact_form_success_message', 'Success Message', 'success_message_input_field', 'simple_contact_form_section');
    }

    /**
     * Settings field method.
     * Translators: %s is the title of the settings field
     */
    private function add_settings_field($id, $title, $callback, $section) {
        add_settings_field(
            $id,
            sprintf(esc_html__('Settings field title: %s', 'simple-contact-form'), $title),
            array($this, $callback),
            'simple_contact_form',
            $section
        );
    }

    /**
     * Select page with dropdown
     */
    public function pages_dropdown() {
        $pages = get_pages();
        $selected_page = get_option('simple_contact_form_page_id');
        echo '<select name="simple_contact_form_page_id">';
        foreach ($pages as $page) {
            $selected = selected($page->ID, $selected_page, false);
            echo '<option value="' . esc_attr($page->ID) . '" ' . esc_html($selected) . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Email input field for the admin settings.
     */
    public function email_input_field() {
        $email = get_option('simple_contact_form_email');
        echo '<input type="email" name="simple_contact_form_email" value="' . esc_attr($email) . '" required />';
    }

    /**
     * Site key input field for Google reCAPTCHA.
     */
    public function site_key_input_field() {
        $site_key = get_option('simple_contact_form_site_key');
        echo '<input type="text" name="simple_contact_form_site_key" value="' . esc_attr($site_key) . '" required />';
    }

    /**
     * Secret key input field for Google reCAPTCHA.
     */
    public function secret_key_input_field() {
        $secret_key = get_option('simple_contact_form_secret_key');
        echo '<input type="text" name="simple_contact_form_secret_key" value="' . esc_attr($secret_key) . '" required />';
    }

    /**
     * Success message input field.
     */
    public function success_message_input_field() {
        $success_message = get_option('simple_contact_form_success_message', 'Thank you for your message!');
        echo '<textarea name="simple_contact_form_success_message" rows="3" cols="50" required>' . esc_textarea($success_message) . '</textarea>';
    }

    /**
     * Check if form is displayed on the current page
     */
    public function is_form_displayed() {
        $page_id = get_option('simple_contact_form_page_id');
        return is_page($page_id);
    }

    /**
     * Conditionally enqueue scripts (only on the selected page).
     */
    public function conditionally_enqueue_scripts() {
        if ($this->is_form_displayed()) {
            wp_enqueue_style('scf_styles', plugin_dir_url(__FILE__) . 'style.css', array(), '1.0.0');
            wp_enqueue_script('scf_contact_form_script', plugin_dir_url(__FILE__) . 'js/contact-form.js', array('jquery'), '1.0.0', true);

            wp_localize_script('scf_contact_form_script', 'scf_ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce(self::NONCE_ACTION)
            ));

            // Enqueue Google reCAPTCHA script only when the form is displayed
            $site_key = esc_attr(get_option('simple_contact_form_site_key'));
            wp_enqueue_script('google-recaptcha', "https://www.google.com/recaptcha/api.js?render=$site_key", array(), 'false', false);
        }
    }

    /**
     * Append contact form to selected page.
     */
    public function append_contact_form($content) {
        if ($this->is_form_displayed()) {
            $form = $this->get_contact_form();
            return $content . $form;
        }
        return $content;
    }

    /**
     * Generate the HTML contact form with reCAPTCHA v3.
     * If editing the form start here.  Dont forget to adjust the process_form_submission_ajax function.
     */
    public function get_contact_form() {
        ob_start();
        ?>
        <form id="scf_contact_form" action="" method="post" style="clear: both;">
            <p id="scf_error_message" style="color:red;"></p>
            <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>
            <p>
                <label for="scf_name"><?php esc_html_e('Name:', 'simple-contact-form'); ?></label><br>
                <input type="text" id="scf_name" name="scf_name" required style="border-radius: 5px;">
            </p>
            <p>
                <label for="scf_email"><?php esc_html_e('Email:', 'simple-contact-form'); ?></label><br>
                <input type="email" id="scf_email" name="scf_email" required style="border-radius: 5px;">
            </p>
            <p>
                <label for="scf_message"><?php esc_html_e('Message:', 'simple-contact-form'); ?></label><br>
                <textarea id="scf_message" name="scf_message" rows="4" required style="border-radius: 5px;"></textarea>
            </p>
            <p>
                <input type="submit" name="scf_submit" value="<?php esc_attr_e('Send', 'simple-contact-form'); ?>" style="background-color: #5bc0de; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
            </p>
        </form>

        <div id="scf_success_message" style="display:none;">
            <p><?php echo esc_html(get_option('simple_contact_form_success_message', 'Thank you for your message!')); ?></p>
        </div>

        <script>
            grecaptcha.ready(function() {
                grecaptcha.execute('<?php echo esc_attr(get_option('simple_contact_form_site_key')); ?>', {action: 'submit'}).then(function(token) {
                    var form = document.getElementById('scf_contact_form');
                    var input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('name', 'g-recaptcha-response');
                    input.setAttribute('value', token);
                    form.appendChild(input);
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle form submission via AJAX.
     * If you've added fields to the form process them here.
     */
    public function process_form_submission_ajax() {
        // Check if the nonce is set and valid
        if (!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            $this->ajax_error(__('Nonce verification failed. Please refresh the page.', 'simple-contact-form'));
        }

        $name = sanitize_text_field(wp_unslash($_POST['scf_name'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['scf_email'] ?? ''));
        $message = sanitize_textarea_field(wp_unslash($_POST['scf_message'] ?? ''));

        // Verify reCAPTCHA v3
        if (!$this->validate_recaptcha()) {
            $this->ajax_error(__('reCAPTCHA verification failed.', 'simple-contact-form'));
        }

        // Validate email
        if (!is_email($email)) {
            $this->ajax_error(__('Please provide a valid email address.', 'simple-contact-form'));
        }

        // Prepare the email
        $to = get_option('simple_contact_form_email');
        $subject = 'New Contact Form Submission from ' . $name;
        $body = "Name: $name\nEmail: $email\nMessage:\n$message";
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        // Send the email
        if (wp_mail($to, $subject, $body, $headers)) {
            wp_send_json_success(esc_html(get_option('simple_contact_form_success_message', 'Thank you for your message!')));
        } else {
            $this->ajax_error(__('There was an error sending the email. Please try again later.', 'simple-contact-form'));
        }
    }

    /**
     * Validate reCAPTCHA v3.
     * No nonce as the form is already validated in the process_form_submission_ajax function.
     */
    private function validate_recaptcha() {
        $recaptcha_secret = get_option('simple_contact_form_secret_key');
        $recaptcha_response = sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'] ?? ''));
        $remote_ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));

        $response = wp_remote_post("https://www.google.com/recaptcha/api/siteverify", array(
            'body' => array(
                'secret' => $recaptcha_secret,
                'response' => $recaptcha_response,
                'remoteip' => $remote_ip
            )
        ));

        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body);

        return $result->success && $result->score >= 0.5;
    }

    /**
     * Handle AJAX errors.
     */
    private function ajax_error($message) {
        wp_send_json_error(esc_html($message));
    }
}

// Initialize the plugin
new Simple_Contact_Form_With_Captcha_v3();
