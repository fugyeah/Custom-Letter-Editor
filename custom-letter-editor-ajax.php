<?php

// AJAX handler for the custom letter generation based on user input
add_action('wp_ajax_custom_letter_editor_handle_submission', 'custom_letter_editor_handle_submission');
add_action('wp_ajax_nopriv_custom_letter_editor_handle_submission', 'custom_letter_editor_handle_submission');
add_action('wp_ajax_custom_letter_editor_send_email', 'custom_letter_editor_send_email');
add_action('wp_ajax_nopriv_custom_letter_editor_send_email', 'custom_letter_editor_send_email');


function custom_letter_editor_handle_submission() {
    // Nonce verification for security
    if (!isset($_POST['custom_letter_editor_nonce']) || !wp_verify_nonce($_POST['custom_letter_editor_nonce'], 'custom_letter_editor_nonce')) {
        wp_send_json_error(array('success' => false, 'message' => 'Invalid nonce.'));
    }

    // ReCAPTCHA verification
    $recaptcha_secret_key = get_option('custom_letter_editor_recaptcha_secret_key');
    $recaptcha_site_key = get_option('custom_letter_editor_recaptcha_site_key');
    if (!empty($recaptcha_secret_key) && !empty($recaptcha_site_key)) {
        if (!verify_recaptcha($_POST['g-recaptcha-response'], $recaptcha_secret_key)) {
            wp_send_json_error(array('success' => false, 'message' => 'reCAPTCHA verification failed.'));
        }
    }

    // Extract data from the AJAX request
    $senderName = sanitize_text_field($_POST['name']);
    $extra = sanitize_text_field($_POST['extra']);
    $locality = get_option('custom_letter_editor_locality');
    $supportOppose = get_option('custom_letter_editor_support_oppose');
    $apiKey = get_option('custom_letter_editor_api_key');

    // Generate the message using the OpenAI API
    $generatedMessage = generate_message($apiKey, $senderName, $extra, $locality, $supportOppose);
    
    // If the message generation was successful, send it as the AJAX response
    if ($generatedMessage) {
        wp_send_json_success(array('message' => 'Message generated successfully!', 'generatedMessage' => $generatedMessage));
    } else {
        wp_send_json_error(array('message' => 'Error generating the message.'));
    }
    wp_die();
}

function verify_recaptcha($response, $secret_key) {
    $recaptcha_verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = array(
        'secret' => $secret_key,
        'response' => $response,
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

function generate_message($apiKey, $senderName, $extra, $locality, $supportOppose) {
    // Define the API URL and headers
    $apiUrl = 'https://api.openai.com/v1/completions';
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer '. $apiKey,
    );

    // Prepare the prompt using the input parameters
    $prompt = "Name: $senderName\nExtra Details: $extra\nLocality: $locality\nSupport/Oppose: $supportOppose\nWrite a custom letter using the above details:";

    // Define the data for the API request
    $data = array(
        'model' => "text-davinci-003",
        'prompt' => $prompt,
        'max_tokens' => 500,
    );

    // Initialize a new cURL session and set options
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL session and fetch the response
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        // Handle the error as needed, e.g., log it or return a specific error message.
    }

    curl_close($ch);
    // Decode the response JSON
    $apiResponse = json_decode($response, true);

    // Check for errors and return the generated letter or an error message
    if (isset($apiResponse['choices'][0]['text'])) {
        return $apiResponse['choices'][0]['text'];
    } else {
        return false;
    }
}
