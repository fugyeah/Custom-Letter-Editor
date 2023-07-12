<?php
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

// Add settings page
add_action('admin_menu', 'custom_letter_editor_settings_page');
function custom_letter_editor_settings_page() {
    add_options_page(
        'Custom Letter Editor',
        'Custom Letter Editor',
        'manage_options',
        'custom-letter-editor',
        'custom_letter_editor_settings_page_content'
    );
}

add_action('admin_init', 'myplugin_settings_api_init');
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

    // Register the API Key setting under custom group
    register_setting('custom-letter-editor', 'myplugin_api_key');
}

// Callback function for the settings section
function myplugin_api_settings_section_callback() {
    echo '<p>Enter your API Key for MyPlugin here.</p>';
}

// Callback function for the API Key field
function myplugin_api_key_callback() {
    echo '<input name="myplugin_api_key" id="myplugin_api_key" type="text" value="' . get_option('myplugin_api_key') . '" />';
}

//** This function is hooked to the admin_post_download_csv action, so it's executed when you visit the URL http://yourwebsite.com/wp-admin/admin-post.php?action=download_csv.//
function export_users_to_csv() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_letter_editor_users';

    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    if (empty($results)) {
        return;
    }

    $csv_output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($csv_output, array_keys($results[0]));

    // Output the rows
    foreach ($results as $row) {
        fputcsv($csv_output, $row);
    }

    fclose($csv_output);
}

// Use this function to download the CSV file
function download_users_csv() {
    if (!current_user_can('manage_options')) {
        return;
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users.csv"');

    export_users_to_csv();

    exit;
}

// Hook the function to a custom URL
add_action('admin_post_download_csv', 'download_users_csv');

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

        // Save the recipient email address with validation
        if (isset($_POST['recipient_email']) && is_email($_POST['recipient_email'])) {
            $recipient_email = sanitize_email($_POST['recipient_email']);
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

    // Display the settings page content using form-table class
    ?>
<div class="wrap">
    <h1>Custom Letter Editor</h1>
    <form method="post" action="">
        <?php wp_nonce_field('custom_letter_editor_settings'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="recipient_email">Recipient Email Address:</label></th>
                <td><input type="email" name="recipient_email" id="recipient_email" value="<?php echo get_option('custom_letter_editor_recipient_email'); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="subject">Subject:</label></th>
                <td><input type="text" name="subject" id="subject" value="<?php echo get_option('custom_letter_editor_subject'); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="additional_details">Additional Details:</label></th>
                <td><textarea name="additional_details" id="additional_details" rows="5" required><?php echo get_option('custom_letter_editor_additional_details'); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="api_key">API Key:</label></th>
                <td><input type="text" name="api_key" id="api_key" value="<?php echo get_option('custom_letter_editor_api_key'); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="recaptcha_site_key">reCAPTCHA Site Key:</label></th>
                <td><input type="text" name="recaptcha_site_key" id="recaptcha_site_key" value="<?php echo get_option('custom_letter_editor_recaptcha_site_key'); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="recaptcha_secret_key">reCAPTCHA Secret Key:</label></th>
                <td><input type="text" name="recaptcha_secret_key" id="recaptcha_secret_key" value="<?php echo get_option('custom_letter_editor_recaptcha_secret_key'); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="custom_css">Custom CSS:</label></th>
                <td><textarea name="custom_css" id="custom_css" rows="10"><?php echo get_option('custom_letter_editor_custom_css'); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="custom_js">Custom JavaScript:</label></th>
                <td><textarea name="custom_js" id="custom_js" rows="10"><?php echo get_option('custom_letter_editor_custom_js'); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="sentiment">Letter Sentiment:</label></th>
                <td>
                    <label for="sentiment_positive">Positive</label>
                    <input type="radio" name="sentiment" id="sentiment_positive" value="positive" <?php echo (get_option('custom_letter_editor_sentiment') === 'positive') ? 'checked' : ''; ?>><br>

                    <label for="sentiment_negative">Negative</label>
                    <input type="radio" name="sentiment" id="sentiment_negative" value="negative" <?php echo (get_option('custom_letter_editor_sentiment') === 'negative') ? 'checked' : ''; ?>><br>
                </td>
            </tr>
        </table>
        <input type="submit" name="custom_letter_editor_settings_submit" class="button button-primary" value="Save Settings">
    </form>
</div>
<?php
}
?>
