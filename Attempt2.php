<?php
/**
 * Plugin Name: Custom Letter Editor
 * Version: 0.89
 * Description: A plugin to generate custom letters using GPT API.
*/

<?php
GFForms::include_addon_framework();

class GFCustomLetterEditor extends GFAddOn {
    protected $_version = '1.0';
    protected $_min_gravityforms_version = '1.9';
    protected $_slug = 'gf-custom-letter-editor';
    protected $_path = 'gf-custom-letter-editor/gf-custom-letter-editor.php';
    protected $_full_path = __FILE__;
    protected $_url = 'http://www.your-url.com';
    protected $_title = 'Gravity Forms Custom Letter Editor';
    protected $_short_title = 'Custom Letter Editor';
    protected $_requires_credit_card = false;
    protected $_supports_callbacks = true;
    
    private static $_instance = null;

    public static function get_instance() {
        if (self::$_instance == null) {
            self::$_instance = new GFCustomLetterEditor();
        }

        return self::$_instance;
    }

    public function plugin_settings_fields() {
        return array(
            array(
                'title'  => 'API Settings',
                'fields' => array(
                    array(
                        'name'    => 'api_key',
                        'tooltip' => 'Enter your API Key here.',
                        'label'   => 'API Key',
                        'type'    => 'text',
                        'class'   => 'medium',
                    ),
                ),
            ),
        );
    }

    public function feed_settings_fields() {
        return array(
            array(
                'fields' => array(
                    array(
                        'name'    => 'recipient',
                        'label'   => 'Recipient',
                        'type'    => 'text',
                        'class'   => 'medium',
                    ),
                    // More fields as required
                )
            )
        );
    }

    public function process_feed($feed, $entry, $form) {
        $api_key = $this->get_plugin_setting('api_key');
        $recipient = $this->get_field_value($form, $entry, $feed['meta']['recipient']);
        // More fields as required

        // Use these values to make API call or other processing
        $result = $this->generate_custom_letter($api_key, $recipient);
        // Handle $result as required
    }

    public function generate_custom_letter($apiKey, $recipient) {
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
    // Include other methods like custom_letter_editor_activate(), export_to_csv(), add_export_link() here
}

