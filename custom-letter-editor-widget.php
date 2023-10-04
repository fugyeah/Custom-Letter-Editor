<?php
// Enqueue custom styles and scripts
add_action('wp_enqueue_scripts', 'custom_letter_editor_enqueue_custom_styles_scripts');
function custom_letter_editor_enqueue_custom_styles_scripts() {
    // Ensure base styles and scripts are enqueued first
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'Default WordPress Theme CSS (via get_stylesheet_uri())'); // Modify the path accordingly
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . '/js/script.js', array('jquery'), '1.0', true);

    wp_localize_script('custom-script', 'customLetterEditorAjax', array(
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));
    
    $custom_css = get_option('custom_letter_editor_custom_css');
    $custom_js = get_option('custom_letter_editor_custom_js');

    // Custom CSS
    if (!empty($custom_css)) {
        wp_add_inline_style('custom-style', $custom_css);
    }

    // Custom JS
    if (!empty($custom_js)) {
        wp_add_inline_script('custom-script', $custom_js);
    }
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
         <form id="custom-letter-form" method="post" action="javascript:void(0);">
              <input type="hidden" name="action" value="custom_letter_editor_handle_submission">
              <?php wp_nonce_field('custom_letter_editor_nonce', 'custom_letter_editor_nonce'); ?>
        
              <label for="name">Name:</label>
              <input type="text" name="username" id="name" required>
  
              <label for="email">Email:</label>
              <input type="email" name="email" id="email" required>
  
              <label for="address">Address:</label>
              <textarea name="address" id="address"></textarea>
  
              <label for="extra">Extra:</label>
              <textarea name="extra" id="extra"></textarea>
  
              <input type="submit" value="Generate Letter">
          </form>
          <div id="message-response"></div> <!-- Placeholder for displaying the generated message -->
          <button id="confirm-send-email" style="display:none;">Confirm and Send Email</button>
          <button id="cancel-send-email" style="display:none;">Cancel</button>
          <?php
  
          echo $args['after_widget'];
      }
  }

// Register the widget
add_action('widgets_init', 'custom_letter_editor_register_widget');
function custom_letter_editor_register_widget() {
    register_widget('CustomLetterEditor');
}
