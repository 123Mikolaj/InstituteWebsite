<?php
// Add Meta Box for 'Wydarzenia' post type
function ii_wydarzenie_meta_box()
{
    add_meta_box(
        'ii-wydarzenie',
        'Dane wydarzenia',
        'ii_wydarzenie_meta_box_callback',
        'wydarzenie', // Typ postu
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ii_wydarzenie_meta_box');

// Callback function to display the form fields
function ii_wydarzenie_meta_box_callback($post)
{
    // Add a nonce field for security
    wp_nonce_field('ii_save_wydarzenie_meta_box_data', 'ii_wydarzenie_nonce');

    // Retrieve existing values
    $start_date = get_post_meta($post->ID, '_ii_start_date', true);
    $end_date = get_post_meta($post->ID, '_ii_end_date', true);
    $selected_form = get_post_meta($post->ID, '_event_form_id', true);

    // Display form fields
    ?>
<p>
  <label for="ii_start_date">Data rozpoczęcia:</label>
  <input type="date" id="ii_start_date" name="ii_start_date" value="<?php echo esc_attr($start_date); ?>" />
</p>
<p>
  <label for="ii_end_date">Data zakończenia:</label>
  <input type="date" id="ii_end_date" name="ii_end_date" value="<?php echo esc_attr($end_date); ?>" />
</p>
<p>
  <label for="event_form_id">Formularz:</label>
  <?php
            global $wpdb;
            $table_forms = $wpdb->prefix . 'form_forms';

            // Fetch forms from the custom table
            $forms = $wpdb->get_results("SELECT * FROM $table_forms");

            echo '<select id="event_form_id" name="event_form_id">';
            echo '<option value="">Wybierz formularz</option>';
            foreach ($forms as $form) {
                $selected = ($form->id == $selected_form) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($form->id) . '" ' . $selected . '>' . esc_html($form->name) . '</option>';
            }
            echo '</select>';
            ?>
</p>
<?php
}

// Save the meta box data
function ii_save_wydarzenie_meta_box_data($post_id)
{
    // Check if our nonce is set.
    if (!isset($_POST['ii_wydarzenie_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['ii_wydarzenie_nonce'], 'ii_save_wydarzenie_meta_box_data')) {
        return;
    }

    // Check if the current user has permission to edit the post
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Sanitize and save the data
    if (isset($_POST['ii_start_date'])) {
        $start_date = sanitize_text_field($_POST['ii_start_date']);
        update_post_meta($post_id, '_ii_start_date', $start_date);
    }
    if (isset($_POST['ii_end_date'])) {
        $end_date = sanitize_text_field($_POST['ii_end_date']);
        update_post_meta($post_id, '_ii_end_date', $end_date);
    }
    if (isset($_POST['event_form_id'])) {
        update_post_meta($post_id, '_event_form_id', intval($_POST['event_form_id']));
    }
}
add_action('save_post', 'ii_save_wydarzenie_meta_box_data');
?>