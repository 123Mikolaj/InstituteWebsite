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
  $stopien = isset($post_meta['ii_stopien'][0]) ? $post_meta['ii_stopien'][0] : '';
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

// Funkcjonalność do wyświetlania semestrów w metaboksie rocznika
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
  if (isset($_POST['ii_stopien'])) {
    update_post_meta($post_id, 'ii_stopien', sanitize_text_field($_POST['ii_stopien']));
  }

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

// Rejestracja taksonomii dla roczników do wyświetlania na stronie Sylabusy
function ii_register_taxonomies() {
  // Taksonomia 'Kierunek'
  $args_kierunek = array(
      'hierarchical' => true,
      'labels' => array(
          'name'              => 'Kierunek',
          'singular_name'     => 'Kierunek',
          'search_items'      => 'Wyszukaj kierunek',
          'all_items'         => 'Wszystkie kierunki',
          'parent_item'       => 'Rodzaj kierunku',
          'parent_item_colon' => 'Rodzaj kierunku:',
          'edit_item'         => 'Edytuj kierunek',
          'update_item'       => 'Zaktualizuj kierunek',
          'add_new_item'      => 'Dodaj nowy kierunek',
          'new_item_name'     => 'Nazwa nowego kierunku',
          'menu_name'         => 'Kierunek',
      ),
      'rewrite' => array(
          'slug' => 'kierunek',
          'with_front' => false,
          'hierarchical' => true,
      ),
      'show_ui' => true,
      'show_admin_column' => true,
      'query_var' => true,
      'show_in_rest' => true,
  );
  register_taxonomy('kierunek', 'rocznik', $args_kierunek);

  // Taksonomia 'Stopień'
  $args_stopien = array(
      'hierarchical' => true,
      'labels' => array(
          'name'              => 'Stopień',
          'singular_name'     => 'Stopień',
          'search_items'      => 'Wyszukaj stopień',
          'all_items'         => 'Wszystkie stopnie',
          'parent_item'       => 'Rodzaj stopnia',
          'parent_item_colon' => 'Rodzaj stopnia:',
          'edit_item'         => 'Edytuj stopień',
          'update_item'       => 'Zaktualizuj stopień',
          'add_new_item'      => 'Dodaj nowy stopień',
          'new_item_name'     => 'Nazwa nowego stopnia',
          'menu_name'         => 'Stopień',
      ),
      'rewrite' => array(
          'slug' => 'stopien',
          'with_front' => false,
          'hierarchical' => true,
      ),
      'show_ui' => true,
      'show_admin_column' => true,
      'query_var' => true,
      'show_in_rest' => true,
  );
  register_taxonomy('stopien', 'rocznik', $args_stopien);

  // Taksonomia 'Rodzaj'
  $args_rodzaj = array(
    'hierarchical' => true,
    'labels' => array(
        'name'              => 'Rodzaj',
        'singular_name'     => 'Rodzaj',
        'search_items'      => 'Wyszukaj rodzaj',
        'all_items'         => 'Wszystkie rodzaje',
        'parent_item'       => 'Nadrzędny rodzaj',
        'parent_item_colon' => 'Nadrzędny rodzaj:',
        'edit_item'         => 'Edytuj rodzaj',
        'update_item'       => 'Zaktualizuj rodzaj',
        'add_new_item'      => 'Dodaj nowy rodzaj',
        'new_item_name'     => 'Nazwa nowego rodzaju',
        'menu_name'         => 'Rodzaj',
    ),
    'rewrite' => array(
        'slug' => 'rodzaj',
        'with_front' => false,
        'hierarchical' => true,
    ),
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'show_in_rest' => true,
  );
  register_taxonomy('rodzaj', 'rocznik', $args_rodzaj);
}
add_action('init', 'ii_register_taxonomies');


function load_custom_scripts() {
  wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'load_custom_scripts');


// Dodajemy skrypt do obsługi AJAX
function enqueue_sylabus_script() {
  wp_enqueue_script('sylabus-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery'), null, true);
  wp_localize_script('sylabus-script', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'enqueue_sylabus_script');


// Funkcja do wyświetlania sylabusa
function wyswietl_sylabus() {
  // Pobranie dostępnych kierunków i stopni
  $kierunki = get_terms(array(
      'taxonomy' => 'kierunek',
      'orderby' => 'name',
      'order' => 'ASC',
      'hide_empty' => false,
  ));

  $output = '<div class="sylabus-filter">';
  $output .= '<h3>Sylabus</h3>';

  $output .= '<div id="breadcrumbs" class="breadcrumbs">
  <span id="breadcrumb-kierunek"></span>
  <span id="breadcrumb-separator-kierunek" style="display: none;"> → </span>
  <span id="breadcrumb-stopien"></span>
  <span id="breadcrumb-separator-stopien" style="display: none;"> → </span>
  <span id="breadcrumb-rodzaj"></span>
  <span id="breadcrumb-separator-rodzaj" style="display: none;"> → </span>
  <span id="breadcrumb-rocznik"></span>
</div>';

  foreach ($kierunki as $kierunek) {
    // Pobieranie stopni powiązanych z kierunkiem
    $stopnie = get_terms(array(
        'taxonomy' => 'stopien',
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => true,
        'tax_query' => array(
            array(
                'taxonomy' => 'kierunek',
                'field' => 'term_id',
                'terms' => $kierunek->term_id,
                'operator' => 'IN',
            ),
        ),
    ));

    $output .= '<div id="kierunek-row-' . esc_attr($kierunek->term_id) . '" class="sylabus-row">';
    $output .= '<button class="kierunek-button" data-kierunek-id="' . esc_attr($kierunek->term_id) . '">' . esc_html($kierunek->name) . '</button>';

    // Stopnie
    $output .= '<div id="stopien-row-' . esc_attr($kierunek->term_id) . '" class="sylabus-row" style="display:none;">';
    foreach ($stopnie as $stopien) {
        $output .= '<button class="stopien-button" data-stopien-id="' . esc_attr($stopien->term_id) . '" data-kierunek-id="' . esc_attr($kierunek->term_id) . '">' . esc_html($stopien->name) . '</button>';

        // Rodzaje (stacjonarne, niestacjonarne)
        $rodzaje = get_terms(array(
            'taxonomy' => 'rodzaj',
            'hide_empty' => true,
        ));

        $output .= '<div id="rodzaj-row-' . esc_attr($kierunek->term_id) . '-' . esc_attr($stopien->term_id) . '" class="sylabus-row" style="display:none;">';
        foreach ($rodzaje as $rodzaj) {
            $output .= '<button class="rodzaj-button" data-rodzaj-id="' . esc_attr($rodzaj->term_id) . '" data-stopien-id="' . esc_attr($stopien->term_id) . '" data-kierunek-id="' . esc_attr($kierunek->term_id) . '">' . esc_html($rodzaj->name) . '</button>';

            // Pobranie roczników dla konkretnego rodzaju
            $query_args = array(
                'post_type' => 'rocznik',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'kierunek',
                        'field' => 'term_id',
                        'terms' => $kierunek->term_id,
                    ),
                    array(
                        'taxonomy' => 'stopien',
                        'field' => 'term_id',
                        'terms' => $stopien->term_id,
                    ),
                    array(
                        'taxonomy' => 'rodzaj',
                        'field' => 'term_id',
                        'terms' => $rodzaj->term_id,
                    ),
                ),
                'posts_per_page' => -1,
            );
            $rocznik_query = new WP_Query($query_args);

            if ($rocznik_query->have_posts()) {
                $output .= '<div id="rocznik-row-' . esc_attr($kierunek->term_id) . '-' . esc_attr($stopien->term_id) . '-' . esc_attr($rodzaj->term_id) . '" class="rocznik-row" style="display:none;">';  // Ukryj początkowo
                while ($rocznik_query->have_posts()) {
                    $rocznik_query->the_post();
                    // Obliczenie numeru rocznika
                    $rocznik_year = get_rocznik_year(get_the_ID(), $stopien->name);

                    if ($rocznik_year !== 'N/A') {
                        $output .= '<button class="rocznik-button" data-rocznik-id="' . esc_attr(get_the_ID()) . '">' . esc_html('Rok ' . $rocznik_year) . '</button>';
                    }
                }
                $output .= '</div>';  // Zamknięcie kontenera roczników
            }

            wp_reset_postdata();
        }
        $output .= '</div>'; // Zamykanie rodzaj-row
    }
    $output .= '</div>'; // Zamykanie stopien-row
    $output .= '</div>'; // Zamykanie kierunek-row
  }

  $output .= '<div id="sylabus-semesters"></div>'; // Miejsce na semestry
  $output .= '</div>'; // Zamykamy sylabus-filter

  $output .= '
  <script type="text/javascript">
  jQuery(document).ready(function($) {

      function updateBreadcrumbs(selectedData) {
          // Resetowanie wszystkich okruszków
          $("#breadcrumb-kierunek").text("");
          $("#breadcrumb-stopien").text("");
          $("#breadcrumb-rodzaj").text("");
          $("#breadcrumb-rocznik").text("");
          $("#breadcrumb-separator-kierunek").hide();
          $("#breadcrumb-separator-stopien").hide();
          $("#breadcrumb-separator-rodzaj").hide();
          $("#breadcrumb-separator-rocznik").hide();

          // Aktualizacja każdego poziomu, jeśli wybrano
          if (selectedData.kierunek) {
              var kierunekText = $(".kierunek-button.active").text();
              $("#breadcrumb-kierunek").text(kierunekText);
              $("#breadcrumb-separator-kierunek").show();
          }
          if (selectedData.stopien) {
              var stopienText = $(".stopien-button.active").text();
              $("#breadcrumb-stopien").text(stopienText);
              $("#breadcrumb-separator-stopien").show();
          }
          if (selectedData.rodzaj) {
              var rodzajText = $(".rodzaj-button.active").text();
              $("#breadcrumb-rodzaj").text(rodzajText);
              $("#breadcrumb-separator-rodzaj").show();
          }
          if (selectedData.rocznik) {
              var rocznikText = $(".rocznik-button.active").text();
              $("#breadcrumb-rocznik").text(rocznikText);
              $("#breadcrumb-separator-rocznik").show();
          }
      }



      var selectedData = {
          kierunek: null,
          stopien: null,
          rodzaj: null,
          rocznik: null
      };

      $(".kierunek-button").click(function() {
          var kierunekId = $(this).data("kierunek-id");

          // Zmieniamy klasę aktywnego przycisku
          $(".kierunek-button").removeClass("active");
          $(this).addClass("active");

          // Ukrywamy wszystkie stopnie, rodzaje i roczniki
          $(".stopien-row").slideUp();
          $(".rodzaj-row").slideUp();
          $(".rocznik-row").slideUp();

          // Pokazujemy stopnie dla wybranego kierunku
          $("#stopien-row-" + kierunekId).slideDown();

          // Zapisujemy wybrany kierunek i resetujemy dalsze poziomy
          selectedData.kierunek = kierunekId;
          selectedData.stopien = null;
          selectedData.rodzaj = null;
          selectedData.rocznik = null;
          $("#sylabus-semesters").html("");

          // Aktualizacja okruszków
          updateBreadcrumbs(selectedData);
      });


      // Funkcja kliknięcia na stopień
      $(".stopien-button").click(function() {
          var stopienId = $(this).data("stopien-id");
          var kierunekId = $(this).data("kierunek-id");

          // Zmieniamy klasę aktywnego przycisku
          $(".stopien-button").removeClass("active");
          $(this).addClass("active");

          // Ukrywamy wszystkie rodzaje i roczniki
          $(".rodzaj-row").slideUp();
          $(".rocznik-row").slideUp();

          // Pokazujemy rodzaje dla wybranego stopnia
          $("#rodzaj-row-" + kierunekId + "-" + stopienId).slideDown();
          
          // Zapisujemy wybrany stopień
          selectedData.stopien = stopienId;
          selectedData.rodzaj = null; // Resetowanie rodzaju
          selectedData.rocznik = null; // Resetowanie rocznika
          $("#sylabus-semesters").html(""); // Czyszczenie semestrów

          // Aktualizacja okruszków
          updateBreadcrumbs(selectedData);
      });

      // Funkcja kliknięcia na rodzaj
      $(".rodzaj-button").click(function() {
          var rodzajId = $(this).data("rodzaj-id");
          var stopienId = $(this).data("stopien-id");
          var kierunekId = $(this).data("kierunek-id");

          // Zmieniamy klasę aktywnego przycisku
          $(".rodzaj-button").removeClass("active");
          $(this).addClass("active");

          // Ukrywamy roczniki dla innych rodzajów
          $(".rocznik-row").slideUp();

          // Pokazujemy roczniki dla wybranego rodzaju
          var rodzajRowId = "#rocznik-row-" + kierunekId + "-" + stopienId + "-" + rodzajId;
          $(rodzajRowId).slideDown();

          // Zapisujemy wybrany rodzaj
          selectedData.rodzaj = rodzajId;
          selectedData.rocznik = null; // Resetowanie rocznika
          $("#sylabus-semesters").html(""); // Czyszczenie semestrów

          // Aktualizacja okruszków
          updateBreadcrumbs(selectedData);
      });

      // Funkcja kliknięcia na rocznik
      $(".rocznik-button").click(function() {
          var rocznikId = $(this).data("rocznik-id");
          var stopienId = selectedData.stopien;
          var kierunekId = selectedData.kierunek;
          var rodzajId = selectedData.rodzaj;

          // Zmieniamy klasę aktywnego przycisku
          $(".rocznik-button").removeClass("active");
          $(this).addClass("active");

          // Zapisujemy wybrany rocznik
          selectedData.rocznik = rocznikId;

          // Aktualizacja okruszków
          updateBreadcrumbs(selectedData);

          // Wysyłanie żądania AJAX po kliknięciu rocznika
          var data = {
              action: "get_semesters_by_rocznik",
              kierunek: kierunekId,
              stopien: stopienId,
              rocznik: rocznikId,
              rodzaj: rodzajId,
              nonce: "' . wp_create_nonce('sylabus_nonce') . '"
          };

          $.post(ajaxurl, data, function(response) {
              console.log("Response:", response);
              $("#sylabus-semesters").html(response);
          });
      });
  });
</script>';

  return $output;
}
add_shortcode('wyswietl_sylabus', 'wyswietl_sylabus');


// Pobieranie semestrów dla wybranego rocznika
function get_semesters_by_rocznik() {

    if( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'sylabus_nonce' ) ) {
      die('Permission Denied');
  }

  if ( isset( $_POST['rocznik'] ) ) {
      $rocznik_id = sanitize_text_field( $_POST['rocznik'] );
      $rocznik_title = get_the_title($rocznik_id);
  }
  
$query = new WP_Query(array(
  'post_type'      => 'semestr',
  'post_status'    => 'publish',
  'posts_per_page' => -1,
  'meta_query'     => array(
      array(
          'key'     => 'ii_rocznik',
          'value'   => $rocznik_id,
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
          'title' => preg_replace('/(Semestr \d+).*/', '$1', get_the_title()),
      );
  }
}

// Sortowanie semestrów po numerze w tytule
usort($semestry, function ($a, $b) {
  $a_semestr = preg_replace('/[^0-9]/', '', $a['title']);
  $b_semestr = preg_replace('/[^0-9]/', '', $b['title']);
  return $a_semestr - $b_semestr;
});

if (!empty($semestry)) {
  $output = '';
  $output .= '<h2>' . esc_html($rocznik_title) . '</h2>';
  foreach ($semestry as $semestr) {
      $output .= '<div class="semestr" id="semestr_' . $semestr['ID'] . '">';
      $output .= '<h3>' . $semestr['title'] . '</h3>';
      $output .= '<div class="przedmioty">' . get_semester_subjects_sylabus($semestr['ID']) . '</div>';
      $output .= '</div>';
  }
  echo $output;
} else {
  echo '<p>Brak semestrów dla wybranego rocznika.</p>';
}

wp_reset_postdata();
wp_die();
}


// Funkcja do pobierania przedmiotów dla semestru
function get_semester_subjects_sylabus($semester_id) {
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

  $output = '';

  if (!empty($subjects)) {
    $output .= '<table class="widefat striped">';
    $output .= '<thead><tr><th scope="col">Nr</th><th scope="col">Przedmiot</th><th scope="col">Sylabus</th></tr></thead>';
    $output .= '<tbody>';

    $i = 1;
    foreach ($subjects as $subject) {
      $subject_title = get_the_title($subject->ID);
      $file_id = get_post_meta($subject->ID, 'ii_file', true);
      $file_url = $file_id ? wp_get_attachment_url($file_id) : '#';

      $output .= "<tr><td>$i</td><td><a href='" . get_permalink($subject->ID) . "'>$subject_title</a></td>";
      $output .= "<td><a href='{$file_url}' target='_blank'>Pobierz Sylabus</a></td></tr>";

      $i++;
    }

    $output .= '</tbody></table>';
  } else {
    $output .= '<p>Nie znaleziono przedmiotów dla tego semestru.</p>';
  }

  return $output;
}
add_action('wp_ajax_get_semesters_by_rocznik', 'get_semesters_by_rocznik');
add_action('wp_ajax_nopriv_get_semesters_by_rocznik', 'get_semesters_by_rocznik');


// Funkcja do obliczania numeru rocznika
function get_rocznik_year($post_id, $stopien) {
  $current_year = date("Y");
  $start_year = get_post_meta($post_id, 'ii_rocznik_rok', true);

  if ($start_year) {
      $year_diff = $current_year - $start_year + 1;

      if ($stopien === 'I stopnia' && $year_diff <= 4 && $year_diff > 0) {
          return $year_diff;
      }

      if ($stopien === 'II stopnia' && $year_diff <= 2 && $year_diff > 0) {
          return $year_diff;
      }
      
      return 'N/A';
  } else {
      return 'N/A';
  }
}