<?php
/**
 * Plugin Name: Custom Letter Editor
 * Version: 0.92
 * Description: A plugin to generate custom letters using GPT API.
 */
// Enqueue necessary scripts and stylesheets
function custom_letter_editor_enqueue_scripts() {
    // Enqueue stylesheets
   wp_enqueue_style('custom-style', get_stylesheet_uri());

    // Enqueue custom JavaScript
    wp_enqueue_script('custom-script', get_template_directory_uri() . '/js/script.js', array(), '1.0', true);

    // Localize the AJAX URL
    wp_localize_script('custom-letter-editor-script', 'customLetterEditorAjax', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('custom-letter-editor-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'custom_letter_editor_enqueue_scripts');

// Add your JavaScript code here
jQuery(document).ready(function($) {
    // AJAX form submission
    $('form').on('submit', function(event) {
        event.preventDefault();

        var form = $(this);
        var formData = form.serialize();

        // Add the nonce value to the form data
        formData += '&custom_letter_editor_nonce=' + customLetterEditorAjax.nonce;

        $.ajax({
            type: 'POST',
            url: customLetterEditorAjax.ajaxUrl,
            data: formData,
            success: function(response) {
                // Handle the success response
            },
            error: function(xhr, status, error) {
                // Handle the error response
            }
        });
    });
});

// Add administration menu page
function custom_letter_editor_add_menu_page() {
    add_menu_page(
        'Custom Letter Editor Settings',
        'Custom Letter Editor',
        'manage_options',
        'custom-letter-editor',
        'custom_letter_editor_settings_page_content',
        'dashicons-email-alt'
    );
}
add_action('admin_menu', 'custom_letter_editor_add_menu_page');

// Add settings page
function custom_letter_editor_settings_page() {
    add_options_page(
        'Custom Letter Editor',
        'Custom Letter Editor',
        'manage_options',
        'custom-letter-editor',
        'custom_letter_editor_settings_page_content'
    );
}
add_action('admin_menu', 'custom_letter_editor_settings_page');

function myplugin_settings_api_init() {
    // Add a new section to the Writing settings page
    add_settings_section(
        'myplugin_api_settings_section',
        'MyPlugin API Settings',
        'myplugin_api_settings_section_callback',
        'writing'
    );

    // Add the API Key field to the new section
    add_settings_field(
        'myplugin_api_key',
        'API Key',
        'myplugin_api_key_callback',
        'writing',
        'myplugin_api_settings_section'
    );

    // Register the API Key setting
    register_setting('writing', 'myplugin_api_key');
}
add_action('admin_init', 'myplugin_settings_api_init');

// Callback function for the settings section
function myplugin_api_settings_section_callback() {
    echo '<p>Enter your API Key for MyPlugin here.</p>';
}

// Callback function for the API Key field
function myplugin_api_key_callback() {
    echo '<input name="myplugin_api_key" id="myplugin_api_key" type="text" value="' . get_option('myplugin_api_key') . '" />';
}

// Register widget
function custom_letter_editor_register_widget() {
    register_widget('CustomLetterEditor');
}
add_action('widgets_init', 'custom_letter_editor_register_widget');

// Create settings page content
function custom_letter_editor_settings_page_content() {
    // Save plugin settings
    if (isset($_POST['custom_letter_editor_settings_submit'])) {
        check_admin_referer('custom_letter_editor_settings');

        // Save the API key
        if (isset($_POST['api_key'])) {
            $api_key = sanitize_text_field($_POST['api_key']);
            update_option('custom_letter_editor_api_key', $api_key);
        }

        // Save the recipient email address
        if (isset($_POST['recipient_email'])) {
            $recipient_email = sanitize_text_field($_POST['recipient_email']);
            update_option('custom_letter_editor_recipient_email', $recipient_email);
        }

        // Save the subject
        if (isset($_POST['subject'])) {
            $subject = sanitize_text_field($_POST['subject']);
            update_option('custom_letter_editor_subject', $subject);
        }

        // Save the additional details
        if (isset($_POST['additional_details'])) {
            $additional_details = sanitize_textarea_field($_POST['additional_details']);
            update_option('custom_letter_editor_additional_details', $additional_details);
        }

        // Save reCAPTCHA keys
        if (isset($_POST['recaptcha_site_key'])) {
            $recaptcha_site_key = sanitize_text_field($_POST['recaptcha_site_key']);
            update_option('custom_letter_editor_recaptcha_site_key', $recaptcha_site_key);
        }

        if (isset($_POST['recaptcha_secret_key'])) {
            $recaptcha_secret_key = sanitize_text_field($_POST['recaptcha_secret_key']);
            update_option('custom_letter_editor_recaptcha_secret_key', $recaptcha_secret_key);
        }

        // Save custom CSS
        if (isset($_POST['custom_css'])) {
            $custom_css = sanitize_textarea_field($_POST['custom_css']);
            update_option('custom_letter_editor_custom_css', $custom_css);
        }
        // Save custom JavaScript
        if (isset($_POST['custom_js'])) {
            $custom_js = sanitize_textarea_field($_POST['custom_js']);
            update_option('custom_letter_editor_custom_js', $custom_js);
        }

        // Save the sentiment
        if (isset($_POST['sentiment'])) {
            $sentiment = sanitize_text_field($_POST['sentiment']);
            update_option('custom_letter_editor_sentiment', $sentiment);
        }

        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }

    // Display the settings page content
    ?>
    <div class="wrap">
    <h1>Custom Letter Editor</h1>
    <form method="post" action="">
        <?php wp_nonce_field('custom_letter_editor_settings'); ?>
        <h2>Email Settings</h2>
        <label for="recipient_email">Recipient Email Address:</label>
        <input type="email" name="recipient_email" id="recipient_email" value="<?php echo get_option('custom_letter_editor_recipient_email'); ?>" required><br>
        <label for="subject">Subject:</label>
        <input type="text" name="subject" id="subject" value="<?php echo get_option('custom_letter_editor_subject'); ?>" required><br>
        <label for="additional_details">Additional Details:</label>
        <textarea name="additional_details" id="additional_details" rows="5" required><?php echo get_option('custom_letter_editor_additional_details'); ?></textarea><br>
        <h2>API Settings</h2>
        <label for="api_key">API Key:</label>
        <input type="text" name="api_key" id="api_key" value="<?php echo get_option('custom_letter_editor_api_key'); ?>" required><br>
        <h2>reCAPTCHA Settings</h2>
        <label for="recaptcha_site_key">reCAPTCHA Site Key:</label>
        <input type="text" name="recaptcha_site_key" id="recaptcha_site_key" value="<?php echo get_option('custom_letter_editor_recaptcha_site_key'); ?>" required><br>
        <label for="recaptcha_secret_key">reCAPTCHA Secret Key:</label>
        <input type="text" name="recaptcha_secret_key" id="recaptcha_secret_key" value="<?php echo get_option('custom_letter_editor_recaptcha_secret_key'); ?>" required><br>
        <h2>Custom CSS</h2>
        <label for="custom_css">Custom CSS:</label>
        <textarea name="custom_css" id="custom_css" rows="10"><?php echo get_option('custom_letter_editor_custom_css'); ?></textarea><br>
        <h2>Custom JavaScript</h2>
        <label for="custom_js">Custom JavaScript:</label>
        <textarea name="custom_js" id="custom_js" rows="10"><?php echo get_option('custom_letter_editor_custom_js'); ?></textarea><br>
        <h2>Letter Sentiment</h2>
        <label for="sentiment">Letter Sentiment:</label><br>
        <input type="radio" name="sentiment" value="positive" <?php checked(get_option('custom_letter_editor_sentiment'), 'positive'); ?>> Positive<br>
        <input type="radio" name="sentiment" value="negative" <?php checked(get_option('custom_letter_editor_sentiment'), 'negative'); ?>> Negative<br><br>
        <input type="submit" name="custom_letter_editor_settings_submit" class="button button-primary" value="Save Settings">
    </form>
</div>
<?php
}

class CustomLetterEditor extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'custom_letter_editor',
            'Custom Letter Editor',
            array('description' => 'This is a widget for the custom letter editor')
        );
    }
function widget($args, $instance) {
    echo $args['before_widget'];
    echo $args['before_title'] . $instance['title'] . $args['after_title'];

    // Generate a unique nonce for the widget form
    $nonce = wp_create_nonce('custom_letter_editor_widget_nonce');

    // Display the widget form
    ?>
    <form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
        <input type="hidden" name="action" value="custom_letter_editor_handle_submission">
        <input type="hidden" name="custom_letter_editor_nonce" value="<?php echo $nonce; ?>">

        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="address">Address:</label>
        <textarea name="address" id="address" required></textarea>

        <input type="submit" value="Submit">
    </form>
    <?php

    echo $args['after_widget'];
}


function custom_letter_editor_handle_submission() {
   if (!isset($_POST['custom_letter_editor_nonce']) || !wp_verify_nonce($_POST['custom_letter_editor_nonce'], 'custom_letter_editor_handle_submission')) {
        wp_send_json_error('Invalid nonce.');
    }

    // Verify the reCAPTCHA response
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $recaptcha_secret_key = get_option('custom_letter_editor_recaptcha_secret_key');
    $recaptcha_verify_url = 'https://www.google.com/recaptcha/api/siteverify';

    $recaptcha_data = array(
        'secret' => $recaptcha_secret_key,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR'],
    );

    $recaptcha_options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($recaptcha_data),
        ),
    );

    $recaptcha_context = stream_context_create($recaptcha_options);
    $recaptcha_result = file_get_contents($recaptcha_verify_url, false, $recaptcha_context);
    $recaptcha_json = json_decode($recaptcha_result, true);

    if (!$recaptcha_json['success']) {
        wp_send_json_error('reCAPTCHA verification failed.');
    }

    // Process form submission and generate letter
    $apiKey = get_option('custom_letter_editor_api_key');
    $selectedRecipient = get_option('custom_letter_editor_recipient_email');
    $selectedSubject = get_option('custom_letter_editor_subject');
    $selectedAdditionalDetails = get_option('custom_letter_editor_additional_details');
    $selectedSentiment = get_option('custom_letter_editor_sentiment');
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $address = sanitize_text_field($_POST['address']);

    // Generate letter using GPT API
    $gptApiResponse = generate_custom_letter($apiKey, $selectedRecipient, $selectedSubject, $selectedAdditionalDetails, $name, $email, $address, $selectedSentiment);

    // Display or save the generated letter as needed
    if ($gptApiResponse['success']) {
        $generatedLetter = $gptApiResponse['data']['generated_letter'];
        echo '<div class="notice notice-success"><p>' . $generatedLetter . '</p></div>';

        // Get the email subject from the GPT API response
        $emailSubject = $gptApiResponse['data']['email_subject'];

        // Send email to recipient(s)
        $recipientEmails = explode(',', $selectedRecipient);
        $emailSubject = get_option('custom_letter_editor_subject');
        $emailBody = $generatedLetter;
        $fromName = sanitize_text_field($_POST['name']);
        $fromEmail = sanitize_email($_POST['email']);

        foreach ($recipientEmails as $recipientEmail) {
            wp_mail(
                $recipientEmail,
                $emailSubject,
                $emailBody,
                array(
                    'From: ' . $fromName . ' <' . $fromEmail . '>',
                    'Reply-To: ' . $fromName . ' <' . $fromEmail . '>',
                )
            );
        }

        // Store user information in the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_letter_editor_users';

        $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'address' => $address,
            )
        );
    } else {
        $errorMessage = $gptApiResponse['message'];
        echo '<div class="notice notice-error"><p>' . $errorMessage . '</p></div>';
    }

    // Always die or exit at the end of AJAX functions
    wp_die();
}


// Function to generate the custom letter using GPT API
function generate_custom_letter($apiKey, $recipient, $subject, $additionalDetails, $name, $email, $address, $selectedSentiment) {
    // Define the API URL
    $apiUrl = 'https://api.openai.com/v1/engines/davinci-codex/completions';

    // Define the headers for the API request
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    );

    // Prepare the prompt using the input parameters
    $prompt = "Recipient: $recipient\nSubject: $subject\nAdditional Details: $additionalDetails\nName: $name\nEmail: $email\nAddress: $address\n\nSentiment: $selectedSentiment\nWrite a letter:";

    // Define the data for the API request
    $data = array(
        'prompt' => $prompt,
        'max_tokens' => 200, // Adjust the number as needed
    );

    // Initialize a new cURL session
    $ch = curl_init();

    // Set the cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL session and fetch the response
    $response = curl_exec($ch);

    // Close the cURL session
    curl_close($ch);

    // Decode the response JSON
    $apiResponse = json_decode($response, true);

    // Check for errors
    if (isset($apiResponse['choices'][0]['text'])) {
        $generatedLetter = $apiResponse['choices'][0]['text'];
        return array(
            'success' => true,
            'data' => array('generated_letter' => $generatedLetter),
        );
    } else {
        $errorMessage = 'An error occurred while generating the letter.';
        if (isset($apiResponse['error']['message'])) {
            $errorMessage = $apiResponse['error']['message'];
        }
        return array(
            'success' => false,
            'message' => $errorMessage,
        );
    }
}

// Register plugin activation hook
function custom_letter_editor_activate() {
    // Create table to store user information
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_letter_editor_users';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name text NOT NULL,
        email text NOT NULL,
        address text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'custom_letter_editor_activate');

add_action('wp_ajax_custom_letter_editor_handle_submission', 'custom_letter_editor_handle_submission');
add_action('wp_ajax_nopriv_custom_letter_editor_handle_submission', 'custom_letter_editor_handle_submission');
