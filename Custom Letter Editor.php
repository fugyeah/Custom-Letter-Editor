<?php
/**
 * Plugin Name: Custom Letter Editor
 * Version: 1.00
 * Author:  Aaron Nevins, Will Jaw
 * Description: A plugin to generate custom letters using GPT API.
 */
// Enqueue necessary scripts and stylesheets

add_action('wp_enqueue_scripts', 'custom_letter_editor_enqueue_scripts');
function custom_letter_editor_enqueue_scripts() {
    // Enqueue stylesheets
    wp_enqueue_style('custom-style', get_stylesheet_uri());

    // Enqueue custom JavaScript
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . '/js/script.js', array('jquery'), '1.0', true);
	
	// Enqueue jQuery
	wp_enqueue_script('jquery');

    // Localize the AJAX URL
    wp_localize_script('custom-script', 'customLetterEditorAjax', array(
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));
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
	 generated_letter longtext NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'custom_letter_editor_activate');

//Old Code
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

    // Register the API Key setting
    register_setting('writing', 'myplugin_api_key');
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
//END download

//OLD CODE
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
        
		<label for="sentiment_positive">Positive</label>
		<input type="radio" name="sentiment" id="sentiment_positive" value="positive" <?php echo (get_option('custom_letter_editor_sentiment') === 'positive') ? 'checked' : ''; 		 ?>><br>

		<label for="sentiment_negative">Negative</label>
		<input type="radio" name="sentiment" id="sentiment_negative" value="negative" <?php echo (get_option('custom_letter_editor_sentiment') === 'negative') ? 'checked' : ''; 		 ?>><br>
        <input type="submit" name="custom_letter_editor_settings_submit" class="button button-primary" value="Save Settings">
    </form>
</div>
<?php
}


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
        // Input validation
        if(empty($apiKey) || empty($recipient) || empty($subject) || empty($additional_details) || empty($name) || empty($email) || empty($address) || empty($sentiment)) {
            return array(
                'success' => false,
                'message' => 'Missing parameters.',
            );
        }

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
	//$retrieved_nonce = $_REQUEST['_wpnonce'];
	
    if (!isset($_POST['custom_letter_editor_nonce'])) {
        wp_send_json_error(array('success' => false, 'message' => 'Invalid nonce.'));
		
    }
	if (!wp_verify_nonce($_POST['custom_letter_editor_nonce'], 'custom_letter_editor_nonce')) {
		wp_send_json_error(array('success' => false, 'message' => 'wp_verify_nonce didn\'t work'));
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

    /*if (!$recaptcha_json['success']) {
        wp_send_json_error(array('success' => false, 'message' => 'reCAPTCHA verification failed.'));
    }*/
	

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
    // Form validation and reCAPTCHA verification code goes here ...

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


class CustomLetterEditor extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'custom_letter_editor',
            'Custom Letter Editor',
            array('description' => 'This is a widget for the custom letter editor')
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo $args['before_title'] . $instance['title'] . $args['after_title'];


        // Display the widget form
        ?>
       <form id="custom-letter-form" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
            <input type="hidden" name="action" value="custom_letter_editor_handle_submission">
            <?php wp_nonce_field('custom_letter_editor_nonce', 'custom_letter_editor_nonce'); ?>
      
            <label for="name">Name:</label>
            <input type="text" name="username" id="name" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="address">Address:</label>
            <textarea name="address" id="address" required></textarea>

            <input type="submit" value="Submit">
        </form>
        <?php

        echo $args['after_widget'];
    }
}
// Register widget
// 

add_action('widgets_init', 'custom_letter_editor_register_widget');
function custom_letter_editor_register_widget() {
    register_widget('CustomLetterEditor');
}

