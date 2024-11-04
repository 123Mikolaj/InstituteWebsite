<?php

// Funkcja do dodania metaboxów dla 'Semestry'
function ii_semestr_meta_box()
{
  add_meta_box('ii-semestr', 'Dane semestru', 'ii_semestr_meta_box_callback', 'semestr', 'normal', 'high');
}

// Callback do wyświetlenia pól w metaboxie dla semestru
function ii_semestr_meta_box_callback($post)
{
  // Pobranie meta danych
  $post_meta = get_post_meta($post->ID);
  $starosta = isset($post_meta['ii_starosta'][0]) ? $post_meta['ii_starosta'][0] : '';
  $harmonogram = isset($post_meta['ii_harmonogram'][0]) ? $post_meta['ii_harmonogram'][0] : '';
  $harmonogram_url = $harmonogram ? wp_get_attachment_url(intval($harmonogram)) : '#';

  // Pobieranie wszystkich pracowników posortowanych po polu 'ii_sort'
  $pracownicy = get_posts(array(
    'post_type' => 'pracownik',
    'posts_per_page' => -1,
    'orderby' => 'meta_value',
    'meta_key' => 'ii_sort',
    'order' => 'ASC'
  ));

  $opiekun = isset($post_meta['ii_opiekun'][0]) ? $post_meta['ii_opiekun'][0] : '';
?>
<div style="margin-bottom: 10px;">
  <label>Sezon</label><br>
  <select name="ii_sezon">
    <option value="">Wybierz sezon</option>
    <option value="zima" <?php selected(isset($post_meta['ii_sezon'][0]) ? $post_meta['ii_sezon'][0] : '', 'zima'); ?>>
      Zima</option>
    <option value="lato" <?php selected(isset($post_meta['ii_sezon'][0]) ? $post_meta['ii_sezon'][0] : '', 'lato'); ?>>
      Lato</option>
  </select>
</div>
<div style="margin-bottom: 10px;">
  <label>Rok</label><br>
  <input type="number" name="ii_rok"
    value="<?php echo esc_attr(isset($post_meta['ii_rok'][0]) ? $post_meta['ii_rok'][0] : ''); ?>">
</div>
<div style="margin-bottom: 10px;">
  <label>Opiekun</label><br>
  <select name="ii_opiekun">
    <option value=""></option>
    <?php foreach ($pracownicy as $pracownik) : ?>
    <option value="<?php echo esc_attr($pracownik->ID); ?>" <?php selected($opiekun, $pracownik->ID); ?>>
      <?php echo esc_html($pracownik->post_title); ?>
    </option>
    <?php endforeach; ?>
  </select>
</div>
<div style="margin-bottom: 14px;">
  <label>Starosta</label><br>
  <input type="text" name="ii_starosta" value="<?php echo esc_attr($starosta); ?>">
</div>
<div style="margin-bottom: 38px;">
  <label>Harmonogram</label><br>
  <input class="upload_image_button" id="ii_harmonogram_btn_1" type="button" class="button" value="Wybierz plik" />
  <input type="hidden" name="ii_harmonogram" id="ii_harmonogram_1" value="<?php echo esc_attr($harmonogram); ?>">
  <a id="ii_harmonogram_prev_1" href="<?php echo esc_attr($harmonogram_url); ?>" target="_blank">Harmonogram</a>
</div>
<div style="margin-bottom: 10px;">
  <label>Przedmioty</label>
  <?php ii_get_semester_subjects($post->ID); ?>
</div>

<script type='text/javascript'>
jQuery(document).ready(function($) {
  var wp_media_post_id = wp.media.model.settings.post.id;

  $('.upload_image_button').on('click', function(event) {
    var file_frame;
    var idelem = event.target.id.substr(19);
    var set_to_post_id = idelem;
    event.preventDefault();

    if (file_frame) {
      file_frame.uploader.uploader.param('post_id', set_to_post_id);
      file_frame.open();
      return;
    } else {
      wp.media.model.settings.post.id = set_to_post_id;
    }

    file_frame = wp.media.frames.file_frame = wp.media({
      title: 'Wybierz plik',
      button: {
        text: 'Użyj wybranego pliku',
      },
      multiple: false
    });

    file_frame.on('select', function() {
      var attachment = file_frame.state().get('selection').first().toJSON();
      $('#ii_harmonogram_' + idelem).val(attachment.id);
      $('#ii_harmonogram_prev_' + idelem).attr('href', attachment.url).text(attachment.filename);
      wp.media.model.settings.post.id = wp_media_post_id;
      delete file_frame;
    });

    file_frame.open();
  });

  $('a.add_media').on('click', function() {
    wp.media.model.settings.post.id = wp_media_post_id;
  });
});
</script>

<?php
}

// Funkcja do zapisu danych z metaboxów dla semestru
function ii_semestr_meta_box_save($post_id)
{
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  if (isset($_POST['ii_starosta'])) {
    update_post_meta($post_id, 'ii_starosta', sanitize_text_field($_POST['ii_starosta']));
  }
  if (isset($_POST['ii_harmonogram'])) {
    update_post_meta($post_id, 'ii_harmonogram', intval($_POST['ii_harmonogram']));
  }
  if (isset($_POST['ii_opiekun'])) {
    update_post_meta($post_id, 'ii_opiekun', intval($_POST['ii_opiekun']));
  }
  if (isset($_POST['ii_sezon'])) {
    update_post_meta($post_id, 'ii_sezon', sanitize_text_field($_POST['ii_sezon']));
  }
  if (isset($_POST['ii_rok'])) {
    update_post_meta($post_id, 'ii_rok', intval($_POST['ii_rok']));
  }
  // Handle dynamically named file inputs for each subject
  foreach ($_POST as $key => $value) {
    if (strpos($key, 'ii_file_') === 0) {
      $subject_id = str_replace('ii_file_', '', $key);
      update_post_meta($subject_id, 'ii_file', intval($value));
    }
  }
}
add_action('add_meta_boxes_semestr', 'ii_semestr_meta_box');
add_action('save_post', 'ii_semestr_meta_box_save');

// Funkcja do wyświetlenia przedmiotów semestru
function ii_get_semester_subjects($semester_id)
{
  // Pobieranie przedmiotów przypisanych do danego semestru
  $subjects = get_posts(array(
    'post_type' => 'przedmiot',
    'meta_query' => array(
      array(
        'key' => 'ii_semestry',
        'value' => $semester_id,
        'compare' => 'LIKE'
      )
    ),
    'posts_per_page' => -1,
    'cache_results' => false
  ));

  echo '<p>Liczba przedmiotów w tym semestrze: ' . count($subjects) . '</p>';
  echo '<p>ID: ' . ($semester_id) . '</p>';

  if (!empty($subjects)) {
    echo '<table class="widefat striped">';
    echo '<thead><tr><th scope="col">Nr</th><th scope="col">Przedmiot</th><th scope="col">Sylabus</th><th scope="col">Link</th></tr></thead>';
    echo '<tbody>';

    $i = 1;
    foreach ($subjects as $subject) {
      $subject_title = get_the_title($subject->ID);
      $file_id = get_post_meta($subject->ID, 'ii_file', true);
      $file_url = $file_id ? wp_get_attachment_url($file_id) : '#';

      $file_btn_id = "ii_file_btn_" . $subject->ID;
      $file_prev_id = "ii_file_prev_" . $subject->ID;
      $file_input_id = "ii_file_input_" . $subject->ID;

      echo "<tr><td>$i</td><td><a href='" . get_permalink($subject->ID) . "'>$subject_title</a></td>";
      echo "<td><input class='upload_image_button' id='{$file_btn_id}' type='button' class='button' value='Wybierz plik' /></td>";
      echo "<td><a id='{$file_prev_id}' href='$file_url' target='_blank'>" . basename($file_url) . "</a></td>";
      echo "<td><input type='hidden' id='{$file_input_id}' name='ii_file_{$subject->ID}' value='{$file_id}' /></td>";
      echo "</tr>";

      $i++;
    }

    echo '</tbody></table>';
  } else {
    echo '<p>Nie znaleziono przedmiotów dla tego semestru.</p>';
  }
?>
<script type='text/javascript'>
jQuery(document).ready(function($) {
  <?php foreach ($subjects as $subject) { ?>
    (function(subject_id) {
      var file_btn_id = "ii_file_btn_" + subject_id;
      var file_prev_id = "ii_file_prev_" + subject_id;
      var file_input_id = "ii_file_input_" + subject_id;

      $('#' + file_btn_id).on('click', function(event) {
        event.preventDefault();

        var file_frame = wp.media.frames.file_frame = wp.media({
          title: 'Wybierz plik',
          button: {
            text: 'Użyj wybranego pliku',
          },
          multiple: false
        });

        file_frame.on('select', function() {
          var attachment = file_frame.state().get('selection').first().toJSON();
          $('#' + file_input_id).val(attachment.id);
          $('#' + file_prev_id).attr('href', attachment.url).text(attachment.filename);
        });

        file_frame.open();
      });
    })('<?php echo $subject->ID; ?>');
  <?php } ?>
});
</script>
<?php
}

//SHORTCODE
function ii_semestr_shortcode()
{
  global $post;

  // Check if the current post is of type 'semestr'
  if (!$post || $post->post_type !== 'semestr') {
    return '<p>Nie znaleziono semestru.</p>';
  }

  // Get the current post ID
  $semester_id = $post->ID;

  // Retrieve metadata
  $post_meta = get_post_meta($semester_id);
  $starosta = isset($post_meta['ii_starosta'][0]) ? $post_meta['ii_starosta'][0] : '';
  $harmonogram_id = isset($post_meta['ii_harmonogram'][0]) ? $post_meta['ii_harmonogram'][0] : '';
  $harmonogram_url = $harmonogram_id ? wp_get_attachment_url(intval($harmonogram_id)) : '#';
  $opiekun = isset($post_meta['ii_opiekun'][0]) ? $post_meta['ii_opiekun'][0] : '';
  $sezon = isset($post_meta['ii_sezon'][0]) ? $post_meta['ii_sezon'][0] : '';
  $rok = isset($post_meta['ii_rok'][0]) ? $post_meta['ii_rok'][0] : '';

  // Generate HTML output
  $output = '<div class="semestr-details">';
  $output .= '<p><strong>Sezon:</strong> ' . esc_html($sezon) . '</p>';
  $output .= '<p><strong>Rok:</strong> ' . esc_html($rok) . '</p>';
  $output .= '<p><strong>Opiekun:</strong> ' . ($opiekun ? get_the_title($opiekun) : 'Brak opiekuna') . '</p>';
  $output .= '<p><strong>Starosta:</strong> ' . ($starosta ? esc_html($starosta) : 'Brak starosty') . '</p>';
  $output .= '<p><strong>Harmonogram:</strong> <a href="' . esc_url($harmonogram_url) . '" target="_blank">' . ($harmonogram_url === '#' ? 'Brak harmonogramu' : 'Pobierz harmonogram') . '</a></p>';

  // Retrieve subjects associated with the semester
  $subjects = get_posts(array(
    'post_type' => 'przedmiot',
    'meta_query' => array(
      array(
        'key' => 'ii_semestry',
        'value' => $semester_id,  // Changed to direct match
        'compare' => 'LIKE'
      )
    ),
    'posts_per_page' => -1,
    'cache_results' => false
  ));

  if (!empty($subjects)) {
    $output .= '<h3>Przedmioty</h3>';
    $output .= '<table class="widefat striped">';
    $output .= '<thead><tr><th scope="col">Nr</th><th scope="col">Przedmiot</th><th scope="col">Sylabus</th></tr></thead>';
    $output .= '<tbody>';

    $i = 1;
    foreach ($subjects as $subject) {
      $subject_title = get_the_title($subject->ID);
      $file_id = get_post_meta($subject->ID, 'ii_file', true);
      $file_url = $file_id ? wp_get_attachment_url($file_id) : '#';

      $output .= "<tr><td>$i</td><td><a href='" . get_permalink($subject->ID) . "'>$subject_title</a></td>";
      $output .= "<td><a href='$file_url' target='_blank'>" . ($file_url === '#' ? 'Brak pliku' : basename($file_url)) . "</a></td></tr>";

      $i++;
    }

    $output .= '</tbody></table>';
  } else {
    $output .= '<p>Nie znaleziono przedmiotów dla tego semestru.</p>';
  }

  $output .= '</div>';

  return $output;
}
add_shortcode('semestr', 'ii_semestr_shortcode');