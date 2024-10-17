<?php

include(plugin_dir_path(__FILE__) . 'semestry.php');

// Funkcja do dodania metaboxów dla 'Roczniki'
function ii_rocznik_meta_box()
{
  add_meta_box('ii-rocznik', 'Dane rocznika', 'ii_rocznik_meta_box_callback');
  add_meta_box('ii-rocznik-semestry', 'Semestry', 'ii_rocznik_semestry_meta_box_callback');
}

function ii_rocznik_meta_box_callback($post)
{
  $post_meta = get_post_meta($post->ID);

  $rocznik = isset($post_meta['ii_rocznik'][0]) ? $post_meta['ii_rocznik'][0] : '';
  $kierunek = isset($post_meta['ii_kierunek'][0]) ? $post_meta['ii_kierunek'][0] : '';
  $rodzaj = isset($post_meta['ii_rodzaj'][0]) ? $post_meta['ii_rodzaj'][0] : '';
  $sylwetka_absolwenta = isset($post_meta['ii_sylwetka_absolwenta'][0]) ? $post_meta['ii_sylwetka_absolwenta'][0] : '';
  $efekty_ksztalcenia = isset($post_meta['ii_efekty_ksztalcenia'][0]) ? $post_meta['ii_efekty_ksztalcenia'][0] : '';

  $sylwetka_absolwenta_guid = $sylwetka_absolwenta ? wp_get_attachment_url(intval($sylwetka_absolwenta)) : '#';
  $efekty_ksztalcenia_guid = $efekty_ksztalcenia ? wp_get_attachment_url(intval($efekty_ksztalcenia)) : '#';

  // Pobranie danych o początku rocznika
  $rocznik_sezon = isset($post_meta['ii_rocznik_sezon'][0]) ? $post_meta['ii_rocznik_sezon'][0] : '';
  $rocznik_rok = isset($post_meta['ii_rocznik_rok'][0]) ? $post_meta['ii_rocznik_rok'][0] : 0;

?>
<div>
  <label>Rocznik</label><br>
  <input type="text" name="ii_rocznik" value="<?php echo esc_attr($rocznik); ?>">
</div>
<div style="margin-top: 10px;">
  <label>Kierunek</label><br>
  <input type="text" name="ii_kierunek" value="<?php echo esc_attr($kierunek); ?>">
</div>
<div style="margin-top: 10px;">
  <label>Rodzaj</label><br>
  <input type="text" name="ii_rodzaj" value="<?php echo esc_attr($rodzaj); ?>">
</div>
<div style="margin-top: 10px;">
  <label>Sezon</label><br>
  <select name="ii_rocznik_sezon">
    <option value="zima" <?php selected($rocznik_sezon, 'zima'); ?>>Zima</option>
    <option value="lato" <?php selected($rocznik_sezon, 'lato'); ?>>Lato</option>
  </select>
</div>
<div style="margin-top: 10px;">
  <label>Początek roku</label><br>
  <input type="number" name="ii_rocznik_rok" value="<?php echo esc_attr($rocznik_rok); ?>">
</div>
<div style="margin-top: 10px;">
  <label>Sylwetka Absolwenta</label><br>
  <input class="upload_image_button" id="ii_sylwetka_absolwenta_btn" type="button" class="button"
    value="Wybierz plik" />
  <input type="hidden" name="ii_sylwetka_absolwenta" id="ii_sylwetka_absolwenta"
    value="<?php echo esc_attr($sylwetka_absolwenta); ?>">
  <a id="ii_sylwetka_absolwenta_prev" href="<?php echo esc_attr($sylwetka_absolwenta_guid); ?>"
    target="_blank"><?php echo esc_html(basename($sylwetka_absolwenta_guid)); ?></a>
</div>
<div style="margin-top: 10px;">
  <label>Efekty Kształcenia</label><br>
  <input class="upload_image_button" id="ii_efekty_ksztalcenia_btn" type="button" class="button" value="Wybierz plik" />
  <input type="hidden" name="ii_efekty_ksztalcenia" id="ii_efekty_ksztalcenia"
    value="<?php echo esc_attr($efekty_ksztalcenia); ?>">
  <a id="ii_efekty_ksztalcenia_prev" href="<?php echo esc_attr($efekty_ksztalcenia_guid); ?>"
    target="_blank"><?php echo esc_html(basename($efekty_ksztalcenia_guid)); ?></a>
</div>

<script type='text/javascript'>
jQuery(document).ready(function($) {
  var wp_media_post_id = wp.media.model.settings.post.id;

  $('#ii_sylwetka_absolwenta_btn').on('click', function(event) {
    event.preventDefault();

    var sylwetkaAbsolwentaFileFrame = wp.media({
      title: 'Wybierz plik',
      button: {
        text: 'Użyj wybranego pliku',
      },
      multiple: false
    });

    sylwetkaAbsolwentaFileFrame.on('select', function() {
      var attachment = sylwetkaAbsolwentaFileFrame.state().get('selection').first().toJSON();
      $('#ii_sylwetka_absolwenta').val(attachment.id);
      $('#ii_sylwetka_absolwenta_prev').attr('href', attachment.url);
      $('#ii_sylwetka_absolwenta_prev').text(attachment.filename);
      wp.media.model.settings.post.id = wp_media_post_id;
      sylwetkaAbsolwentaFileFrame.close();
    });

    sylwetkaAbsolwentaFileFrame.open();
  });

  $('#ii_efekty_ksztalcenia_btn').on('click', function(event) {
    event.preventDefault();

    var efektyKsztalceniaFileFrame = wp.media({
      title: 'Wybierz plik',
      button: {
        text: 'Użyj wybranego pliku',
      },
      multiple: false
    });

    efektyKsztalceniaFileFrame.on('select', function() {
      var attachment = efektyKsztalceniaFileFrame.state().get('selection').first().toJSON();
      $('#ii_efekty_ksztalcenia').val(attachment.id);
      $('#ii_efekty_ksztalcenia_prev').attr('href', attachment.url);
      $('#ii_efekty_ksztalcenia_prev').text(attachment.filename);
      wp.media.model.settings.post.id = wp_media_post_id;
      efektyKsztalceniaFileFrame.close();
    });

    efektyKsztalceniaFileFrame.open();
  });
});
</script>
<?php
}

function ii_rocznik_semestry_meta_box_callback($post)
{
  $post_id = $post->ID;

  echo '<p>Rocznik ID: ' . $post_id . '</p>';

  $query = new WP_Query(array(
    'post_type'      => 'semestr',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_query'     => array(
      array(
        'key'     => 'ii_rocznik',
        'value'   => $post_id,
        'compare' => '=',
      ),
    ),
  ));

  $semestry = array();
  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $semestry[] = array(
        'ID'    => get_the_ID(),
        'title' => get_the_title(),
      );
    }
  }

  usort($semestry, function ($a, $b) {
    $a_semestr = preg_replace('/[^0-9]/', '', $a['title']);
    $b_semestr = preg_replace('/[^0-9]/', '', $b['title']);

    if ($a_semestr < $b_semestr) {
      return -1;
    } elseif ($a_semestr > $b_semestr) {
      return 1;
    } else {
      return 0;
    }
  });

  // Pobierz listę pracowników
  $pracownicy = get_posts(array(
    'post_type'      => 'pracownik',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value',
    'meta_key'       => 'ii_sort',
    'order'          => 'ASC',
  ));

  foreach ($semestry as $semestr) {
    // Pobierz wartości pól meta dla semestru
    $opiekun_id = get_post_meta($semestr['ID'], 'ii_opiekun', true);
    $starosta = get_post_meta($semestr['ID'], 'ii_starosta', true);
    $harmonogram = get_post_meta($semestr['ID'], 'ii_harmonogram', true);
    $harmonogram_guid = $harmonogram ? wp_get_attachment_url(intval($harmonogram)) : '#';

    // Wyświetl metaboks dla semestru
    echo '<div id="iisemestr_' . $semestr['ID'] . '" class="stuffbox">';
    echo '<h2 onclick="iiToggleSemestr(' . $semestr['ID'] . ')"><label for="link_name">' . $semestr['title'] . '</label></h2>';
    echo '<div class="inside" id="iisemestrbox_' . $semestr['ID'] . '" style="display:none;">';

    // Opiekun
    echo '<div style="margin-bottom: 10px;">
            <label>Opiekun</label><br>
            <select name="ii_opiekun_' . $semestr['ID'] . '">
                <option value=""></option>'; // Dodaj pustą opcję

    // Wygeneruj opcje dla selecta opiekuna
    foreach ($pracownicy as $pracownik) {
      echo '<option value="' . esc_attr($pracownik->ID) . '" ' . selected($opiekun_id, $pracownik->ID, false) . '>' . esc_html($pracownik->post_title) . '</option>';
    }

    echo '</select>
        </div>';

    // Starosta
    echo '<div style="margin-bottom: 10px;">
            <label>Starosta</label><br>
            <input type="text" name="ii_starosta_' . $semestr['ID'] . '" value="' . esc_attr($starosta) . '">
        </div>';

    // Harmonogram
    echo '<div style="margin-bottom: 10px;">
            <input class="upload_image_button" id="ii_harmonogram_btn_' . $semestr['ID'] . '" type="button" class="button" value="Wybierz plik" />
            <label>Harmonogram</label><br>
            <input type="hidden" name="ii_harmonogram_' . $semestr['ID'] . '" id="ii_harmonogram_' . $semestr['ID'] . '" value="' . esc_attr($harmonogram) . '">
            <a id="ii_harmonogram_prev_' . $semestr['ID'] . '" href="' . esc_attr($harmonogram_guid) . '" target="_blank">Harmonogram</a>
        </div>';

    // Przedmioty
    echo '<div>
            <label>Przedmioty</label><br>';

    ii_get_semester_subjects($semestr['ID']);

    echo '

</div>

</div>

</div>';
  }

  // Form to add a new semestr
  echo '<input type="text" name="ii_rocz_sem_name"><br>';
  echo '<button name="ii_rocz_sem_add" type="submit" value="true">Dodaj semestr</button>';

?>
<div>

  <label>Wczytaj semestry z pliku Excel</label><br>

  <input class="upload_image_button" id="ii_excel_btn" type="button" class="button" value="Wybierz plik Excel" />

  <input type="hidden" name="ii_excel_path" id="ii_excel_path" value="<?php echo esc_attr($excel_path); ?>">

  <a id="ii_excel_prev" href="<?php echo esc_attr($excel_guid); ?>" target="_blank">

    <?php if (!empty($excel_path)) : ?>

    <?php echo esc_html(basename($excel_path)); ?>

    <?php endif; ?>

  </a>

</div>

<?php

  echo '<input type="hidden" name="ii_rocz_sem_save"><br>';
  echo '<button name="ii_rocz_sem_save_button" type="submit" value="true">Zapisz zmiany</button>';

  ?>

<script type='text/javascript'>
jQuery(document).ready(function($) {

  var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id

  $('#ii_excel_btn').on('click', function(event) {

    event.preventDefault();


    var excelFileFrame = wp.media({

      title: 'Wybierz plik Excel',

      button: {

        text: 'Użyj wybranego pliku',

      },

      multiple: false

    });


    excelFileFrame.on('select', function() {
      var attachment = excelFileFrame.state().get('selection').first().toJSON();
      $('#ii_excel_path').val(attachment.id);
      $('#ii_excel_prev').attr('href', attachment.url);
      $('#ii_excel_prev').text(attachment.filename); // Update the link text with the file name
    });

    excelFileFrame.open();

  });

});
</script>
<?php
}


// Funkcja do zapisu danych z metaboxów dla rocznika
function ii_rocznik_meta_box_save($post_id)
{
  if (isset($_POST['ii_rocznik'])) {
    update_post_meta($post_id, 'ii_rocznik', $_POST['ii_rocznik']);
  }
  if (isset($_POST['ii_kierunek'])) {
    update_post_meta($post_id, 'ii_kierunek', $_POST['ii_kierunek']);
  }
  if (isset($_POST['ii_rodzaj'])) {
    update_post_meta($post_id, 'ii_rodzaj', $_POST['ii_rodzaj']);
  }

  // Zapisz początek rocznika
  if (isset($_POST['ii_rocznik_sezon'])) {
    update_post_meta($post_id, 'ii_rocznik_sezon', $_POST['ii_rocznik_sezon']);
  }
  if (isset($_POST['ii_rocznik_rok'])) {
    update_post_meta($post_id, 'ii_rocznik_rok', $_POST['ii_rocznik_rok']);
  }

  if (isset($_POST['ii_sylwetka_absolwenta'])) {
    update_post_meta($post_id, 'ii_sylwetka_absolwenta', $_POST['ii_sylwetka_absolwenta']);
  }
  if (isset($_POST['ii_efekty_ksztalcenia'])) {
    update_post_meta($post_id, 'ii_efekty_ksztalcenia', $_POST['ii_efekty_ksztalcenia']);
  }
  //Dodawanie semestru
  if (isset($_POST['ii_rocz_sem_add']) && isset($_POST['ii_rocz_sem_name']) && $_POST['ii_rocz_sem_name'] != '') {
    unset($_POST['ii_rocz_sem_add']);
    $semestr = array(
      'post_title'    => wp_strip_all_tags($_POST['ii_rocz_sem_name']),
      'post_content'  => 'semestr',
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_type' => 'semestr'
    );
    remove_action('save_post', 'ii_przedmiot_meta_box_save');

    $semestr_id = wp_insert_post($semestr);
    add_action('save_post', 'ii_przedmiot_meta_box_save');

    update_post_meta($semestr_id, 'ii_rocznik', $post_id);
    echo 'Dodano semestr';
  }

  if (isset($_POST['ii_rocz_sem_save_button'])) {
    foreach ($_POST as $key => $value) {
      if (strpos($key, 'ii_opiekun_') === 0) {
        $semestr_id = str_replace('ii_opiekun_', '', $key);
        update_post_meta($semestr_id, 'ii_opiekun', $value);
      } elseif (strpos($key, 'ii_starosta_') === 0) {
        $semestr_id = str_replace('ii_starosta_', '', $key);
        update_post_meta($semestr_id, 'ii_starosta', $value);
      } elseif (strpos($key, 'ii_harmonogram_') === 0) {
        $semestr_id = str_replace('ii_harmonogram_', '', $key);
        update_post_meta($semestr_id, 'ii_harmonogram', $value);
      } elseif (strpos($key, 'ii_file_') === 0) {
        $subject_id = str_replace('ii_file_', '', $key);
        update_post_meta($subject_id, 'ii_file', $value);
      }
    }
  }

  if (isset($_REQUEST['ii_excel_path']) && $_REQUEST['ii_excel_path'] != '') {
    $excel_path = $_REQUEST['ii_excel_path'];
    add_semesters_excel2($excel_path, $post_id);
  }
}
add_action('add_meta_boxes_rocznik', 'ii_rocznik_meta_box');
add_action('save_post', 'ii_rocznik_meta_box_save');


function ii_rocznik_shortcode($atts)
{
  global $post;

  // Pobierz atrybuty shortcode
  $atts = shortcode_atts(array(
    'id' => $post->ID,
  ), $atts, 'rocznik');

  $rocznik_id = intval($atts['id']);
  $rocznik = get_post($rocznik_id);

  if (!$rocznik || $rocznik->post_type !== 'rocznik') {
    return '<p>Nie znaleziono rocznika.</p>';
  }

  // Pobierz dane meta
  $rocznik_name = get_post_meta($rocznik_id, 'ii_rocznik', true);
  $kierunek = get_post_meta($rocznik_id, 'ii_kierunek', true);
  $rodzaj = get_post_meta($rocznik_id, 'ii_rodzaj', true);
  $sezon = get_post_meta($rocznik_id, 'ii_rocznik_sezon', true);
  $rok = get_post_meta($rocznik_id, 'ii_rocznik_rok', true);
  $sylwetka_absolwenta = get_post_meta($rocznik_id, 'ii_sylwetka_absolwenta', true);
  $efekty_ksztalcenia = get_post_meta($rocznik_id, 'ii_efekty_ksztalcenia', true);

  $sylwetka_absolwenta_url = $sylwetka_absolwenta ? wp_get_attachment_url(intval($sylwetka_absolwenta)) : '';
  $efekty_ksztalcenia_url = $efekty_ksztalcenia ? wp_get_attachment_url(intval($efekty_ksztalcenia)) : '';

  // Tworzenie HTML
  $output = '<div class="rocznik-details">';
  $output .= '<h2>' . esc_html($rocznik_name) . '</h2>';
  $output .= '<p><strong>Kierunek:</strong> ' . esc_html($kierunek) . '</p>';
  $output .= '<p><strong>Rodzaj:</strong> ' . esc_html($rodzaj) . '</p>';
  $output .= '<p><strong>Sezon:</strong> ' . esc_html($sezon) . '</p>';
  $output .= '<p><strong>Początek roku:</strong> ' . esc_html($rok) . '</p>';

  if ($sylwetka_absolwenta_url) {
    $output .= '<p><strong>Sylwetka Absolwenta:</strong> <a href="' . esc_url($sylwetka_absolwenta_url) . '" target="_blank">' . esc_html(basename($sylwetka_absolwenta_url)) . '</a></p>';
  }

  if ($efekty_ksztalcenia_url) {
    $output .= '<p><strong>Efekty Kształcenia:</strong> <a href="' . esc_url($efekty_ksztalcenia_url) . '" target="_blank">' . esc_html(basename($efekty_ksztalcenia_url)) . '</a></p>';
  }

  $output .= '</div>';

  return $output;
}


add_shortcode('rocznik', 'ii_rocznik_shortcode');