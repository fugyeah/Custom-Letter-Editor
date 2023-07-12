<?php
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
