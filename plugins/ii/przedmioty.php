<?php

// Add meta boxes for 'Przedmioty'
function ii_przedmiot_meta_box()
{
  add_meta_box('ii-przedmiot', 'Dane przedmiotu', 'ii_przedmiot_meta_box_callback');
}

// Callback to display fields in the meta box for the subject
function ii_przedmiot_meta_box_callback($post)
{
  // Retrieve meta data
  $semestry = get_post_meta($post->ID, 'ii_semestry', true);
  $sylabus_id = get_post_meta($post->ID, 'ii_file', true);

  // Nonce field for security
  wp_nonce_field('ii_przedmiot_save_meta_box_data', 'ii_przedmiot_meta_box_nonce');
?>
<div>
  <label>Semestry</label>
  <?php echo ii_get_subject_semesters($post->ID); ?>
  <input type="hidden" name="ii_semestry" value="<?php echo implode(',', (array) $semestry); ?>">
</div>
<div style="margin-top: 10px;">
  <label>Sylabus</label><br>
  <?php
    if ($sylabus_id) {
      $sylabus_url = wp_get_attachment_url($sylabus_id);
      echo "<a href='$sylabus_url' target='_blank'>" . basename($sylabus_url) . "</a>";
    } else {
      echo '<p>No sylabus found for this subject.</p>';
    }
    ?>
  <input class="upload_image_button" id="ii_sylabus_btn" type="button" class="button" value="Wybierz plik" />
  <input type="hidden" name="ii_sylabus_id" id="ii_sylabus_id" value="<?php echo esc_attr($sylabus_id); ?>">
</div>
<div>
  <label>Zagadnienia do egzaminu</label>
  <textarea name="ii_zagadnienia" rows="4"
    style="width: 100%;"><?php echo esc_textarea(get_post_meta($post->ID, 'ii_zagadnienia', true)); ?></textarea>
</div>
<script type='text/javascript'>
jQuery(document).ready(function($) {
  var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id

  $('#ii_sylabus_btn').on('click', function(event) {
    event.preventDefault();

    var sylabusFileFrame = wp.media({
      title: 'Wybierz plik Sylabus',
      button: {
        text: 'Użyj wybranego pliku',
      },
      multiple: false
    });

    sylabusFileFrame.on('select', function() {
      var attachment = sylabusFileFrame.state().get('selection').first().toJSON();
      $('#ii_sylabus_id').val(attachment.id);
      $('#ii_sylabus_prev').attr('href', attachment.url);
      $('#ii_sylabus_prev').text(attachment.filename); // Update the link text with the file name
      wp.media.model.settings.post.id = wp_media_post_id;
      sylabusFileFrame.close();
    });

    sylabusFileFrame.open();
  });
});
</script>
<?php
}

function ii_przedmiot_meta_box_save($post_id)
{
  if (!isset($_POST['ii_przedmiot_meta_box_nonce']) || !wp_verify_nonce($_POST['ii_przedmiot_meta_box_nonce'], 'ii_przedmiot_save_meta_box_data')) {
    return;
  }

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return;
  }

  if (!current_user_can('edit_post', $post_id)) {
    return;
  }

  if (isset($_POST['ii_semestry'])) {
    $semesters = array_map('sanitize_text_field', explode(',', $_POST['ii_semestry']));
    update_post_meta($post_id, 'ii_semestry', $semesters);
  }
  if (isset($_POST['ii_sylabus_id'])) {
    update_post_meta($post_id, 'ii_file', sanitize_text_field($_POST['ii_sylabus_id']));
  }
  if (isset($_POST['ii_zagadnienia'])) {
    update_post_meta($post_id, 'ii_zagadnienia', sanitize_text_field($_POST['ii_zagadnienia']));
  }
}
add_action('add_meta_boxes_przedmiot', 'ii_przedmiot_meta_box');
add_action('save_post', 'ii_przedmiot_meta_box_save');


// Funkcja do automatycznego dodawania tagu [przedmiot]
function ii_add_przedmiot_tag($post_id)
{
  // Sprawdzamy, czy to jest nowy post
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  // Sprawdzamy, czy post jest typu 'przedmiot'
  if (get_post_type($post_id) !== 'przedmiot') return;

  $post = get_post($post_id);
  $content = $post->post_content;

  // Dodajemy tag [przedmiot], jeśli go nie ma
  if (strpos($content, '[przedmiot]') === false) {
    $new_content = $content . "\n[przedmiot]";
    wp_update_post(array(
      'ID'           => $post_id,
      'post_content' => $new_content
    ));
  }
}
add_action('save_post', 'ii_add_przedmiot_tag');


// Function to retrieve and display semesters associated with a subject
function ii_get_subject_semesters($post_id)
{
  $semesters = get_post_meta($post_id, 'ii_semestry', true);

  $output = ''; // Initialize output variable

  if (!empty($semesters) && is_array($semesters)) {
    $output .= '<ul>';
    foreach ($semesters as $semester_id) {
      $semester_post = get_post($semester_id);

      if ($semester_post && $semester_post->post_status != 'trash') {
        $semester_title = esc_html($semester_post->post_title);
        $output .= "<li><a href='" . get_permalink($semester_id) . "'>$semester_title</a></li>";
      }
    }
    $output .= '</ul>';
  } else {
    $output .= '<p>No semesters found for this subject.</p>';
  }

  return $output; // Return the output
}

// Function to handle the subject shortcode
// Function to handle the subject shortcode
function ii_przedmiot_shortcode($atts)
{
  global $post;

  // Pobierz atrybuty shortcode
  $atts = shortcode_atts(array(
    'id' => $post->ID,
  ), $atts, 'przedmiot');

  $post_id = intval($atts['id']);
  $subject_post = get_post($post_id);

  if (!$subject_post || $subject_post->post_type !== 'przedmiot') {
    return '<p>Nie znaleziono przedmiotu.</p>';
  }

  // Get meta data
  $semestry = get_post_meta($post_id, 'ii_semestry', true);
  $sylabus_id = get_post_meta($post_id, 'ii_file', true);
  $sylabus_url = $sylabus_id ? wp_get_attachment_url($sylabus_id) : '';
  $zagadnienia = get_post_meta($post_id, 'ii_zagadnienia', true);

  // Start building the output
  $output = '<div class="przedmiot-details">';

  // Semestry
  $output .= '<h3>Semestry</h3>';
  if (!empty($semestry) && is_array($semestry)) {
    $output .= '<ul>';
    foreach ($semestry as $semester_id) {
      $semester_post = get_post($semester_id);
      if ($semester_post && $semester_post->post_status != 'trash') {
        $semester_title = esc_html($semester_post->post_title);
        $output .= "<li><a href='" . get_permalink($semester_id) . "'>$semester_title</a></li>";
      }
    }
    $output .= '</ul>';
  } else {
    $output .= '<p>Brak semestrów dla danego przedmiotu.</p>';
  }

  // Sylabus
  if ($sylabus_url) {
    $output .= '<p><strong>Sylabus:</strong> <a href="' . esc_url($sylabus_url) . '" target="_blank">' . esc_html(basename($sylabus_url)) . '</a></p>';
  } else {
    $output .= '<p>Brak sylabusu dla tego przedmiotu.</p>';
  }

  // Zagadnienia do egzaminu
  $output .= '<h3>Zagadnienia do egzaminu</h3>';
  if (!empty($zagadnienia)) {
    $output .= '<p>' . esc_html($zagadnienia) . '</p>';
  } else {
    $output .= '<p>Brak zagadnień do egzaminu dla tego przedmiotu.</p>';
  }

  $output .= '</div>';

  return $output;
}
add_shortcode('przedmiot', 'ii_przedmiot_shortcode');