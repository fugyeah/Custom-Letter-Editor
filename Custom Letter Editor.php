<?php
/**
 * Plugin Name: Custom Letter Editor
 * Version: 2.00
 * Author:  Aaron Nevins, Will Jaw
 * Description: A plugin to generate custom letters using GPT API.
 */

// Enqueue necessary scripts and stylesheets
add_action('wp_enqueue_scripts', 'custom_letter_editor_enqueue_scripts');
function custom_letter_editor_enqueue_scripts() {
    // Enqueue stylesheets
    wp_enqueue_style('custom-style', get_stylesheet_uri());
    // Enqueue custom JavaScript
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.0', true);
    // Enqueue jQuery
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-letter-editor-js', plugin_dir_url(__FILE__) . 'js/email.js', array('jquery'), '1.0.0', true);

    // Localize the script with new data
    $ajax_data = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
    );
    wp_localize_script('custom-letter-editor-js', 'customLetterEditorAjax', $ajax_data);
}

// Plugin Activation - Table creation
register_activation_hook(__FILE__, 'custom_letter_editor_activate');
function custom_letter_editor_activate() {
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

// Admin Menu and Settings
include(plugin_dir_path(__FILE__) . 'admin-settings.php');

// AJAX Handling
include(plugin_dir_path(__FILE__) . 'ajax-handlers.php');

// Widgets
include(plugin_dir_path(__FILE__) . 'widgets.php');

// Email Functions
include(plugin_dir_path(__FILE__) . 'email-functions.php');


require_once dirname( __FILE__ ) . '/inc/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'my_custom_letter_editor_register_required_plugins' );

function my_custom_letter_editor_register_required_plugins() {
    $plugins = array(
        array(
            'name'      => 'WP Mail SMTP',
            'slug'      => 'wp-mail-smtp',
            'required'  => true, // You can set this to false if the plugin is only recommended and not required
        ),
    );

    $config = array(
        'id'           => 'custom-letter-editor',   // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                       // Default absolute path to bundled plugins.
        'menu'         => 'tgmpa-install-plugins',  // Menu slug.
        'parent_slug'  => 'plugins.php',            // Parent menu slug.
        'capability'   => 'manage_options',         // Capability needed to view plugin install page
        'has_notices'  => true,                     // Show admin notices or not.
        'dismissable'  => true,                     // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                       // Custom message to output right before the nag.
        'is_automatic' => true,                     // Automatically activate plugins after installation or not.
    );

    tgmpa( $plugins, $config );
}