<?php
  /**
 * This function generates a custom letter using OpenAI's GPT-3 API.
 *
 * It prepares a prompt using the provided parameters, sends a request to the API, and returns the generated letter.
 *
 * @param string $apiKey - The API key for authenticating the API request.
 * @param string $recipient - The recipient of the letter.
 * @param string $subject - The subject of the letter.
 * @param string $additionalDetails - Additional details to be included in the letter.
 * @param string $name - The name of the sender.
 * @param string $email - The email of the sender.
 * @param string $address - The address of the sender.
 * @param string $sentiment - The sentiment of the letter (e.g., positive, negative, neutral).
 *
 * @return array - An associative array containing the API response. If the request is successful, 
 *                 'success' is set to true and 'data' contains the generated letter. 
 *                 If an error occurs, 'success' is set to false and 'message' contains an error message.
 */
      
function generate_custom_letter($apiKey, $recipient, $subject, $additional_details, $name, $email, $address, $sentiment) {
    // Input validation and sanitization
    if(empty($apiKey) || empty($recipient) || empty($subject) || empty($additional_details) || empty($name) || empty($email) || empty($address) || empty($sentiment)) {
        return array(
            'success' => false,
            'message' => 'Missing parameters.',
        );
    }
    $recipient = sanitize_text_field($recipient);
    $subject = sanitize_text_field($subject);
    $additional_details = sanitize_text_field($additional_details);
    $name = sanitize_text_field($name);
    $email = sanitize_email($email);
    $address = sanitize_text_field($address);
    $sentiment = sanitize_text_field($sentiment);

        // Define the API URL
        $apiUrl = 'https://api.openai.com/v1/completions';

        // Define the headers for the API request
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $apiKey,
        );

        // Prepare the prompt using the input parameters
        $prompt = "Recipient: $recipient\nSubject: $subject\nAdditional Details: $additional_details\nName: $name\nEmail: $email\nAddress: $address\nSentiment: $sentiment\nWrite a 300 word editorial using the above facts:";

        // Define the data for the API request
        $data = array(
			'model' => "text-davinci-002",
            'prompt' => $prompt,
            'max_tokens' => 500, // Adjust the number as needed
        );

        // Initialize a new cURL session
        $ch = curl_init();

        // Set the cURL options
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // Execute the cURL session and fetch the response
        $response = curl_exec($ch);
 
    // Check for cURL errors and handle them
    if (curl_errno($ch)) {
        return array(
            'success' => false,
            'message' => curl_error($ch),
        );
    }
    // Check the HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode == 429) {
        // We hit the rate limit
        $retryAfter = curl_getinfo($ch, CURLINFO_RETRY_AFTER);
        if ($retryAfter) {
            // The server told us how long to wait
            sleep($retryAfter);
        } else {
            // The server didn't tell us how long to wait, so let's wait for a default period and then retry
            // The default period is calculated as 2^retryCount seconds, with a maximum of 64 seconds
            $waitTime = min(pow(2, $retryCount), 64);
            sleep($waitTime);
        }
        // Retry the request, increasing the retry count
        return generate_custom_letter($apiKey, $recipient, $subject, $additional_details, $name, $email, $address, $sentiment, $retryCount + 1);
    }

        // Close the cURL session
        curl_close($ch);

        // Decode the response JSON
        $apiResponse = json_decode($response, true);
		//echo $apiResponse;

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

add_action('wp_ajax_custom_letter_editor_handle_submission', 'custom_letter_editor_handle_submission');
add_action('wp_ajax_nopriv_custom_letter_editor_handle_submission', 'custom_letter_editor_handle_submission');

function custom_letter_editor_handle_submission() {
    if (!isset($_POST['custom_letter_editor_nonce']) || !wp_verify_nonce($_POST['custom_letter_editor_nonce'], 'custom_letter_editor_nonce')) {
        wp_send_json_error(array('success' => false, 'message' => 'Invalid nonce.'));
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
        wp_send_json_error(array('success' => false, 'message' => 'reCAPTCHA verification failed.'));
    }
	
    // Process form submission and generate letter
    $apiKey = get_option('custom_letter_editor_api_key');
    $selectedRecipient = get_option('custom_letter_editor_recipient_email');
    $selectedSubject = get_option('custom_letter_editor_subject');
    $selectedAdditionalDetails = get_option('custom_letter_editor_additional_details');
    $selectedSentiment = get_option('custom_letter_editor_sentiment');
    $name = sanitize_text_field($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $address = sanitize_text_field($_POST['address']);

    // Generate letter using GPT API
    
    $gptApiResponse = generate_custom_letter($apiKey, $selectedRecipient, $selectedSubject, $selectedAdditionalDetails, $name, $email, $address, $selectedSentiment);
	var_dump($gptApiResponse);
	
	error_log(json_encode($gptApiResponse));

    // Display and save the generated letter as needed
	if ($gptApiResponse['success']) {
    $generatedLetter = $gptApiResponse['data']['generated_letter'];
    $emailSubject = $gptApiResponse['data']['email_subject'];

		///OLD CODE
// $recipientEmails = explode(',', $selectedRecipient);
  //  $emailSubject = get_option('custom_letter_editor_subject');
//	$emailBody = $generatedLetter;
//	$emailBody .= "Name: " . $name . "\n";
//	$emailBody .= "Email: " . $email . "\n";
//	$emailBody .= "Address: " . $address . "\n";
//    $fromName = sanitize_text_field($_POST['name']);
//    $fromEmail = sanitize_email($_POST['email']);
//
//    foreach ($recipientEmails as $recipientEmail) {
//        wp_mail(
//            $recipientEmail,
//            $emailSubject,
 //           $emailBody,
 //           array(
//                'From: ' . $fromName . ' <' . $fromEmail . '>',
//                'Reply-To: ' . $fromName . ' <' . $fromEmail . '>',
//            )
//        );
//    }
//
//////// end cut code/////	
    // Store user information in the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_letter_editor_users';

    $wpdb->insert(
        $table_name,
        array(
            'name' => $name,
            'email' => $email,
            'address' => $address,
	 'generated_letter' => $generatedLetter,
        )
    );

    wp_send_json_success(array('generated_letter' => $generatedLetter));
	} else {
    $errorMessage = $gptApiResponse['message'];
    wp_send_json_error(array('success' => false, 'message' => $errorMessage));
	}

    // Always die or exit at the end of AJAX functions
    wp_die();
}

////// Send email to recipient(s) ////////////

add_action('wp_ajax_custom_letter_editor_send_email', 'custom_letter_editor_send_email');
add_action('wp_ajax_nopriv_custom_letter_editor_send_email', 'custom_letter_editor_send_email');

function custom_letter_editor_send_email() {
    // Check the nonce
    if (!isset($_POST['custom_letter_editor_nonce']) || !wp_verify_nonce($_POST['custom_letter_editor_nonce'], 'custom_letter_editor_nonce')) {
        wp_send_json_error(array('success' => false, 'message' => 'Invalid nonce.'));
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
        wp_send_json_error(array('success' => false, 'message' => 'reCAPTCHA verification failed.'));
    }

    // Form validation
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['address']) || empty($_POST['generated_letter'])) {
        wp_send_json_error(array('success' => false, 'message' => 'Missing parameters.'));
    }

    // Get the email details from the POST data and sanitize them
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

