<?php
class CustomLetterEditor extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'custom_letter_editor',
            'Custom Letter Editor',
            array('description' => 'This is a widget for the custom letter editor')
        );
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : esc_html__('New title', 'text_domain');
        ?>
        <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:', 'text_domain'); ?></label> 
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

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
add_action('widgets_init', 'custom_letter_editor_register_widget');
function custom_letter_editor_register_widget() {
    register_widget('CustomLetterEditor');
}
