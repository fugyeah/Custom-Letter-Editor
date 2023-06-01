<?php
/**
 * Plugin Name: Custom Letter Editor
 * Version: 0.89
 * Description: A plugin to generate custom letters using GPT API.
*/

function custom_letter_editor_enqueue_scripts() {
    wp_register_style('custom-letter-editor-style', false);
    wp_enqueue_style('custom-letter-editor-style');
    $custom_css = get_option('custom_letter_editor_custom_css');
    wp_add_inline_style('custom-letter-editor-style', $custom_css);

    wp_register_script('custom-letter-editor-script', '', [], '', true);
    wp_enqueue_script('custom-letter_editor-script');
    $custom_js = get_option('custom_letter_editor_custom_js');
    wp_add_inline_script('custom-letter-editor-script', $custom_js);
}
add_action('admin_enqueue_scripts', 'custom_letter_editor_enqueue_scripts');

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
add_action('admin_menu', 'custom_letter_editor_add_menu_page');

function custom_letter_editor_settings_page() {
    add_options_page(
        'Custom Letter Editor',
        'Custom Letter Editor',
        'manage_options',
        'custom-letter-editor',
        'custom_letter_editor_settings_page_content'
    );
}
add_action('admin_menu', 'custom_letter_editor_settings_page');

function custom_letter_editor_register_widget() {
    register_widget('CustomLetterEditor');
}
add_action('widgets_init', 'custom_letter_editor_register_widget');

// Your existing code for settings page content...

class CustomLetterEditor extends WP_Widget {

    // Your existing code for widget...

    public function update($new_instance, $old_instance) {
        // Save widget settings
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        // Repeat for other fields
        return $instance;
    }
}

function custom_letter_editor_handle_submission() {
    // Your existing code for form submission...
}

// Action to handle AJAX request
add_action('wp_ajax_custom_letter_editor_handle_submission', 'custom_letter_editor_handle_submission');
add_action('wp_ajax_nopriv_custom_letter_editor_handle_submission', 'custom_letter_editor_handle_submission');

// Your existing code for GPT API...

function custom_letter_editor_activate() {
    // Your existing code for plugin activation...
}
register_activation_hook(__FILE__, 'custom_letter_editor_activate');
?>
