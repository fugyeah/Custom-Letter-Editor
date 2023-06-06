<?php
/**
 * Plugin Name: Gravity Forms Custom Letter Editor
 * Plugin URI: http://www.chelsea-road.com
 * Description: A custom letter creator for Gravity Forms using GPT.
 * Version: .97
 * Author: Aaron Nevins
 * Author URI: http://www.chelsea-road.com
 * License: GPL2
 */

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
/**
 * The Singleton method for getting an instance of the GFCustomLetterEditor class.
 *
 * This method allows you to create a single instance of the GFCustomLetterEditor class.
 * It is a design pattern used to restrict a class to instantiate only one object.
 * 
 * @return GFCustomLetterEditor Returns the single instance of the GFCustomLetterEditor class.
 */
    public static function get_instance() {
        if (self::$_instance == null) {
            self::$_instance = new GFCustomLetterEditor();
        }

        return self::$_instance;
    }
/**
 * This function creates a new database table.
 * 
 * This function is used to create a new table in the WordPress database with the help of the wpdb global object.
 * This is used for storing users' information including name, email, address, generated letter and email sent time.
 */
    public function create_table() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'custom_letter_editor_users';

    // SQL to create your table
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email text NOT NULL,
        address text NOT NULL,
        generated_letter text NOT NULL,
        email_sent_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
    /**
 * Registers a hidden page for exporting CSV data.
 *
 * This function adds a hidden submenu page to the WordPress admin area. This page will be used to handle CSV export
 * operations, hence it doesn't appear in the menu structure. It is only accessible through a direct URL.
 */
public function register_export_page() {
    add_submenu_page(
        null, // this makes it a hidden page
        'Export CSV', // page_title
        'Export CSV', // menu_title
        'manage_options', // capability
        'gf_custom_letter_editor_export', // menu_slug
        array($this, 'handle_csv_export') // function
    );
}
 /**
 * Defines the settings fields for the plugin.
 * 
 * This function is used to define the fields that will appear in the settings page of the plugin. 
 * This is where users can customize their experience, for example by setting an API key, defining the email recipient and subject, 
 * and configuring additional details.
 *
 * @return array Returns an array of settings fields.
 */
class My_Plugin {
    public function __construct() {
        add_filter( 'gform_addon_navigation', array($this, 'create_menu') );
    }

    public function create_menu($menus) {
        $menus[] = array(
            'name' => $this->_slug,
            'label' => $this->_title,
            'callback' => array($this, 'plugin_settings_page')
        );
        return $menus;
    }

    public function plugin_settings_page() {
        GFFormSettings::output_plugin_settings_page($this->plugin_settings_fields());
    }

public function plugin_settings_fields() {
    return array(
        array(
            'title'  => 'Custom Letter Editor Settings',
            'fields' => array(
                array(
                    'name'    => 'export_csv',
                    'label'   => '',
                    'type'    => 'save',
                    'value'   => 'Export to CSV',
                    'onclick' => "window.location.href = '" . wp_nonce_url(admin_url('admin.php?page=gf_custom_letter_editor_export'), 'gf_custom_letter_editor_export') . "'",
                ),
                array(
                    'name'    => 'api_key',
                    'label'   => 'API Key',
                    'type'    => 'text',
                    'class'   => 'medium',
                ),
                array(
                    'name'    => 'recipient',
                    'label'   => 'Recipient',
                    'type'    => 'text',
                    'class'   => 'medium',
                ),
                array(
                    'name'    => 'subject',
                    'label'   => 'Subject',
                    'type'    => 'text',
                    'class'   => 'medium',
                ),
                array(
                    'name'    => 'additional_details',
                    'label'   => 'Additional Details',
                    'type'    => 'text',
                    'class'   => 'large',
                ),
                array(
                    'name'    => 'sentiment',
                    'label'   => 'Sentiment',
                    'type'    => 'radio',
                    'choices' => array(
                        array(
                            'label' => 'Positive',
                            'value' => 'positive',
                        ),
                        array(
                            'label' => 'Negative',
                            'value' => 'negative',
                        ),                       
                    ),
                ),
            ),
        ),
    );
}

/**
 * Defines the feed settings fields for the plugin.
 * 
 * This function is used to define the fields that will be displayed in the feed settings page of the plugin.
 * This is where users can customize their data feed, for example by setting a name, email, and optional address.
 *
 * @return array Returns an array of feed settings fields.
 */
public function feed_settings_fields() {
    return array(
        array(
            'fields' => array(
                array(
                    'name'    => 'name',
                    'label'   => 'Name',
                    'type'    => 'text',
                    'class'   => 'medium',
                ),
                array(
                    'name'    => 'email',
                    'label'   => 'Email',
                    'type'    => 'text',
                    'class'   => 'medium',
                ),
                array(
                    'name'    => 'address',
                    'label'   => 'Address (optional)',
                    'type'    => 'text',
                    'class'   => 'medium',
                ),
            )
        )
    );
}
/**
 * This function is responsible for processing the feed.
 *
 * It retrieves the settings, generates a custom letter, saves it to the database, and sends it as an email.
 *
 * @param $feed - The Feed object containing all the feed settings.
 * @param $entry - The Entry object containing the submitted values.
 * @param $form - The Form object containing all the form settings.
 */
public function process_feed($feed, $entry, $form) {
    $api_key = $this->get_plugin_setting('api_key');

    // Check API Key
    if (empty($api_key)) {
        error_log("Error: API Key is missing.");
        return;
    }

    $recipient = $this->get_plugin_setting('recipient');
    $subject = $this->get_plugin_setting('subject');
    $additional_details = $this->get_plugin_setting('additional_details');
    $sentiment = $this->get_plugin_setting('sentiment');

    $name = sanitize_text_field($this->get_field_value($form, $entry, $feed['meta']['name']));
    $email = sanitize_email($this->get_field_value($form, $entry, $feed['meta']['email']));
    $address = sanitize_text_field($this->get_field_value($form, $entry, $feed['meta']['address']));

    $result = $this->generate_custom_letter($api_key, $recipient, $subject, $additional_details, $name, $email, $address, $sentiment);

    if ($result['success']) {
        // Save the generated letter to the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_letter_editor_users';
       $wpdb->insert(
    $table_name,
    array(
        'name' => $name,
        'email' => $email,
        'address' => $address,
        'generated_letter' => $result['data']['generated_letter'],
        'email_sent_time' => current_time('mysql'),
    )
);
        // Send the generated letter to the recipient(s)
        $to = $recipient; 
        $subject = "$subject";
        $message = $result['data']['generated_letter'];
        $from = $name;
        $reply = $email;
        $admin_email = get_option( 'admin_email' );

        // Prepare the headers for the email
        $headers = array(
            "From: $from <$email>",
            "Reply-To: $reply",
            "BCC: $admin_email",
        );

        // Send the email
        if(!wp_mail($to, $subject, $message, $headers)){
            error_log("Error sending email: " . $GLOBALS['phpmailer']->ErrorInfo);
        }

    } else {
        // Handle error
        error_log("Error generating custom letter: " . $result['message']);
    }
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
      
  public function generate_custom_letter($apiKey, $recipient, $subject, $additional_details, $name, $email, $address, $sentiment) {
        // Define the API URL
        $apiUrl = 'https://api.openai.com/v1/engines/davinci-codex/completions';

        // Define the headers for the API request
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        );
        // Prepare the prompt using the input parameters
 $prompt = "Recipient: $recipient\nSubject: $subject\nAdditional Details: $additionalDetails\nName: $name\nEmail: $email\nAddress: $address\n\nSentiment: $sentiment\nWrite a letter:";

        // Define the data for the API request
        $data = array(
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

/**
 * The activation hook function for the plugin.
 *
 * This function creates a table in the database when the plugin is activated.
 * It calls the create_table() method through the get_instance() singleton method of the GFCustomLetterEditor class.
 */ 
public static function activate() {
    self::get_instance()->create_table();
}
        
/**
 * Handles the CSV export.
 *
 * This function is the callback for the hidden page registered in the register_export_page() function.
 * It checks if the current user has the required permissions and then exports the data from the table to a CSV file.
 * It calls the export_to_csv() method which handles the actual export.
 */
    public function handle_csv_export() {
    // Check if the user has the required capability
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Call your export_to_csv method
    $this->export_to_csv();
}
/**
 * Exports data to a CSV file.
 *
 * This function fetches all the data from the 'custom_letter_editor_users' table of the database, and writes it to a CSV file.
 * The file is named 'export.csv' and is sent to the user as a downloadable file.
 * The first row of the CSV file contains the column names of the table, and each subsequent row contains the data of a row from the table.
 */        
        public function export_to_csv() {
    global $wpdb;
    
    $filename = 'export.csv';
    $table_name = $wpdb->prefix . 'custom_letter_editor_users';
    $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    if(empty($data)) return;

    // Open the output stream
    $output = fopen('php://output', 'w');

    // output headers
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename={$filename}");
    header("Pragma: no-cache");
    header("Expires: 0");

    // output the column headings
    fputcsv($output, array_keys($data[0]));

    // output the data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
/**
 * The initialization method for the plugin.
 *
 * This function is called when the plugin is loaded. It first calls the init() method of the parent class (GFAddOn), 
 * and then adds an action to the 'admin_menu' hook, which will call the register_export_page() method 
 * when the admin menu is being rendered.
 */        
public function init() {
    parent::init();
    add_action('admin_menu', array($this, 'register_export_page'));
    }
}

/**
 * The activation hook for the plugin.
 *
 * This function is called when the plugin is activated. It calls the static activate() method of the GFCustomLetterEditor class.
 */
register_activation_hook(__FILE__, array('GFCustomLetterEditor', 'activate'));
?>
