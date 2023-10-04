<?php


// Handle email sending logic
function custom_letter_editor_send_email($recipient_email, $subject, $message) {
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    // Use the built-in wp_mail function to send the email
    if (wp_mail($recipient_email, $subject, $message, $headers)) {
        return true; // Email sent successfully
    } else {
        return false; // There was a problem sending the email
    }
}

// Handle AJAX request for sending email
add_action('wp_ajax_custom_letter_editor_send_email', 'handle_ajax_email_send');
add_action('wp_ajax_nopriv_custom_letter_editor_send_email', 'handle_ajax_email_send');

function handle_ajax_email_send() {
     
        // Get the email details from the POST data
        $selectedRecipient = get_option('custom_letter_editor_recipient_email');
        $name = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $address = sanitize_text_field($_POST['address']);
        $generatedLetter = sanitize_text_field($_POST['generated_letter']);
    
        // Send email to recipient(s)
        $recipientEmails = explode(',', $selectedRecipient);
        $emailSubject = get_option('custom_letter_editor_subject');
        $emailBody = $generatedLetter;
        $emailBody .= "Name: " . $name . "\n";
        $emailBody .= "Email: " . $email . "\n";
        $emailBody .= "Address: " . $address . "\n";
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
    
        wp_send_json_success(array('message' => 'Email sent successfully!'));
    
        // Always die or exit at the end of AJAX functions
        wp_die();
    }

function verify_recaptcha() {
    $recaptcha_secret_key = get_option('custom_letter_editor_recaptcha_secret_key');
    $recaptcha_site_key = get_option('custom_letter_editor_recaptcha_site_key');
    
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

    if (is_plugin_active('google-recaptcha-plugin-folder/main-file.php')) {
        // Use the reCAPTCHA functionality from the Google reCAPTCHA WordPress plugin
        return true;  // Assuming the reCAPTCHA plugin handles the verification automatically. Adjust if needed.
    } elseif (!empty($recaptcha_secret_key) && !empty($recaptcha_site_key)) {
        // Perform your own reCAPTCHA check using the keys from your plugin's settings
        $recaptcha_response = $_POST['g-recaptcha-response'];
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

        return $recaptcha_json['success'];
    }
    return false;
}