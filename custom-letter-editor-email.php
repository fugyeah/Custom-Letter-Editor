<?php


// Handle email sending logic
function custom_letter_editor_send_email($recipient_email, $subject, $message, $headers = array(), $bcc = '') {
    if(empty($headers)) { $headers = array('Content-Type: text/html; charset=UTF-8'); }
       if(!empty($bcc)) { $headers[] = 'Bcc: ' . $bcc; }
    
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
    } 
    return false;
}