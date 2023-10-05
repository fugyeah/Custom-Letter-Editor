<?php
add_action('admin_head', 'custom_letter_editor_admin_styles');
function custom_letter_editor_admin_styles() {
    echo '<style>
        .add-remove-button {
            background-color: #f7f7f7;
            border: 1px solid #ccc;
            padding: 2px 5px;
            font-size: 14px;
            line-height: 1;
            cursor: pointer;
            margin-left: 5px;
        }

        .add-remove-button:hover {
            background-color: #e6e6e6;
        }
    </style>';
}

// Add administration menu page
add_action('admin_menu', 'custom_letter_editor_add_menu_page');
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


// Display the settings page content
function custom_letter_editor_settings_page_content() {
    // Save plugin settings
    if (isset($_POST['custom_letter_editor_settings_submit'])) {
        check_admin_referer('custom_letter_editor_settings');


        // Save the GPT API key
        if (isset($_POST['apikey'])) {
            $apikey = sanitize_text_field($_POST['apikey']);
            update_option('custom_letter_editor_apikey', $apikey);
        }

        // Save the recipient email address
        if (isset($_POST['recipient_email'])) {
            $recipient_emails = array_map('sanitize_email', explode(',', $_POST['recipient_email']));
            $recipient_email = implode(',', $recipient_emails);
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

        // Save the locality
        if (isset($_POST['locality'])) {
            $locality = sanitize_text_field($_POST['locality']);
            update_option('custom_letter_editor_locality', $locality);
        }

        // Save the talking points
        if (isset($_POST['talking_points'])) {
            $talkingPoints = array_map('sanitize_text_field', $_POST['talking_points']);
            $talkingPointsString = implode(', ', $talkingPoints);
            update_option('custom_letter_editor_talking_points', $talkingPointsString);
}


        // Save the support/oppose radio
        if (isset($_POST['support_oppose'])) {
            $support_oppose = sanitize_text_field($_POST['support_oppose']);
            update_option('custom_letter_editor_support_oppose', $support_oppose);
        }

        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Custom Letter Editor Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('custom_letter_editor_settings'); ?>
            
            <!-- Email Settings -->
        <h2>Email Settings</h2>
            <label for="recipient_email">Recipient Email Addresses (comma-separated):</label>
            <input type="text" name="recipient_email" id="recipient_email" placeholder="example1@example.com, example2@example.com" value="<?php echo esc_attr(get_option('custom_letter_editor_recipient_email')); ?>" required>
        <br>
            <label for="bcc_email">BCC Email Address:</label>
            <input type="email" name="bcc_email" id="bcc_email" value="<?php echo esc_attr(get_option('custom_letter_editor_bcc_email')); ?>">
        <br>
            <label for="subject">Subject:</label>
            <input type="text" name="subject" id="subject" value="<?php echo get_option('custom_letter_editor_subject'); ?>" required>
        <br>
            <label for="additional_details">Additional Details:</label>
            <textarea name="additional_details" id="additional_details" rows="5" required><?php echo esc_html(get_option('custom_letter_editor_additional_details')); ?></textarea>

            <!-- Talking Points -->
        <h2>Talking Points</h2>
            <div id="talking-points">
          <label for="talking_point_1">Talking Point 1:</label>
          <input type="text" name="talking_points[]" id="talking_point_1" value="<?php echo isset($talkingPoints[0]) ? $talkingPoints[0] : ''; ?>"><br>

           <label for="talking_point_2">Talking Point 2:</label>
           <input type="text" name="talking_points[]" id="talking_point_2" value="<?php echo isset($talkingPoints[1]) ? $talkingPoints[1] : ''; ?>"><br>

           <label for="talking_point_3">Talking Point 3:</label>
           <input type="text" name="talking_points[]" id="talking_point_3" value="<?php echo isset($talkingPoints[2]) ? $talkingPoints[2] : ''; ?>"><br>
           <button class="add-remove-button remove-talking-point" type="button" disabled>-</button><br>
</div>
<button class="add-remove-button" id="add-talking-point" type="button">+</button>
            
            <!-- API Settings -->
            <h2>API Settings</h2>
            <label for="apikey">OpenAI API Key:</label>
            <input type="text" name="apikey" id="apikey" value="<?php echo esc_attr(get_option('custom_letter_editor_api_key')); ?>" required><br>
           
            <!-- reCAPTCHA Settings -->
            <h2>reCAPTCHA Settings</h2>
            <label for="recaptcha_site_key">reCAPTCHA Site Key:</label>
            <input type="text" name="recaptcha_site_key" id="recaptcha_site_key" value="<?php echo get_option('custom_letter_editor_recaptcha_site_key'); ?>" required><br>
            <label for="recaptcha_secret_key">reCAPTCHA Secret Key:</label>
            <input type="text" name="recaptcha_secret_key" id="recaptcha_secret_key" value="<?php echo get_option('custom_letter_editor_recaptcha_secret_key'); ?>" required><br>
            
            <!-- Custom CSS and JS -->
            <h2>Custom Styling</h2>
            <label for="custom_css">Custom CSS:</label>
            <textarea name="custom_css" id="custom_css" rows="10"><?php echo esc_html(get_option('custom_letter_editor_custom_css')); ?></textarea><br>
            <label for="custom_js">Custom JavaScript:</label>
            <textarea name="custom_js" id="custom_js" rows="10"><?php echo esc_html(get_option('custom_letter_editor_custom_js')); ?></textarea><br>
            
            <!-- Sentiment Selection -->
            <h2>Letter Sentiment</h2>
            <label for="sentiment_positive">Positive</label>
            <input type="radio" name="sentiment" id="sentiment_positive" value="positive" <?php checked(get_option('custom_letter_editor_sentiment'), 'positive'); ?>><br>
            <label for="sentiment_negative">Negative</label>
            <input type="radio" name="sentiment" id="sentiment_negative" value="negative" <?php checked(get_option('custom_letter_editor_sentiment'), 'negative'); ?>><br>
            
            <!-- Locality -->
            <h2>Locality</h2>
            <label for="locality">Locality:</label>
            <input type="text" name="locality" id="locality" value="<?php echo get_option('custom_letter_editor_locality'); ?>"><br>
            
            <!-- Support or Oppose -->
            <h2>Support or Oppose</h2>
            <label for="support">In Support</label>
            <input type="radio" name="support_oppose" id="support" value="in support" <?php checked(get_option('custom_letter_editor_support_oppose'), 'in support'); ?>><br>
            <label for="oppose">In Opposition</label>
            <input type="radio" name="support_oppose" id="oppose" value="in opposition" <?php checked(get_option('custom_letter_editor_support_oppose'), 'in opposition'); ?>><br>
            
            <!-- Submit Button -->
            <input type="submit" name="custom_letter_editor_settings_submit" class="button button-primary" value="Save Settings">
        </form>
    </div>
    <?php
}

add_action('admin_init', 'custom_letter_editor_settings_api_init');
function custom_letter_editor_settings_api_init() {
    // Add a new section to the Writing settings page
    add_settings_section(
        'custom_letter_editor_api_settings_section',
        'Custom Letter Editor API Settings',
        'custom_letter_editor_api_settings_section_callback',
        'writing'
    );

    // Add the API Key field to the new section
    add_settings_field(
        'custom_letter_editor_api_key',
        'API Key',
        'custom_letter_editor_api_key_callback',
        'writing',
        'custom_letter_editor_api_settings_section'
    );

    // Register the API Key setting
    register_setting('writing', 'custom_letter_editor_api_key');
}

// Callback function for the settings section
function custom_letter_editor_api_settings_section_callback() {
    echo '<p>Enter your API Key for Chat GPT here.</p>';
}

// Callback function for the API Key field
function custom_letter_editor_api_key_callback() {
    echo '<input name="custom_letter_editor_api_key" id="custom_letter_editor_api_key" type="text" value="' . get_option('custom_letter_editor_api_key') . '" />';
}

function custom_letter_editor_enqueue_admin_scripts() {
    wp_enqueue_script('custom-letter-editor-admin', plugin_dir_url(__FILE__) . 'js/custom-letter-editor-admin.js', array('jquery'), PLUGIN_VERSION, true);
}
add_action('admin_enqueue_scripts', 'custom_letter_editor_enqueue_admin_scripts');