<?php
/**
 * Plugin Name: Custom Letter Editor
 * Version: 1.50
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

// Function to be run upon plugin activation
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

// Register plugin activation hook
register_activation_hook(__FILE__, 'custom_letter_editor_activate');

require_once plugin_dir_path(__FILE__) . 'custom-letter-editor-settings.php';
require_once plugin_dir_path(__FILE__) . 'custom-letter-editor-widget.php';
require_once plugin_dir_path(__FILE__) . 'custom-letter-editor-ajax.php';
