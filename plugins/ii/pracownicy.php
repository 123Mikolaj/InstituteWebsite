<?php



// Funkcja do dodania metaboxów dla 'Pracownicy'
function ii_pracownik_meta_box(){
	add_meta_box('ii-pracownik','Dane pracownika','ii_pracownik_meta_box_callback');
}

// Callback do wyświetlenia pól w metaboxie dla pracownika
function ii_pracownik_meta_box_callback($post) {
  $post_meta = get_post_meta($post->ID);
  $academic_titles = array('', 'lic.', 'mgr', 'inż.', 'mgr inż.', 'dr', 'dr inż.', 'dr hab.', 'dr hab. inż.','prof.', 'prof. dr hab. inż.');
  $types = array('', 'naukowy', 'administracyjny');
  $positions = array('', 'dyrektor', 'zastępca', 'sekretariat', 'obsługa techniczna');
  $structure_positions = array('', 'kierownik', 'profesor dydaktyczny', 'profesor badawczo-dydaktyczny', 'adiunkt dydaktyczny', 'adiunkt badawczo-dydaktyczny', 'asystent dydaktyczny', 'asystent badawczo-dydaktyczny');

  $current_academic_title = isset($post_meta['ii_academic_title'][0]) ? $post_meta['ii_academic_title'][0] : '';
  $posttitle = isset($post_meta['ii_posttitle'][0]) ? $post_meta['ii_posttitle'][0] : '';
  $current_type = isset($post_meta['ii_type'][0]) ? $post_meta['ii_type'][0] : '';
  $email = isset($post_meta['ii_email'][0]) ? $post_meta['ii_email'][0] : '';
  $phone_number = isset($post_meta['ii_phone_number'][0]) ? $post_meta['ii_phone_number'][0] : '';
  $room = isset($post_meta['ii_room'][0]) ? $post_meta['ii_room'][0] : '';
  $sort = isset($post_meta['ii_sort'][0]) ? $post_meta['ii_sort'][0] : '';
  $current_position = isset($post_meta['ii_position'][0]) ? $post_meta['ii_position'][0] : '';
  $current_structure_position = isset($post_meta['ii_structure_position'][0]) ? $post_meta['ii_structure_position'][0] : '';

  ?>

<div>
  <label>Tytuł</label><br>
  <select name="ii_academic_title">
    <?php foreach ($academic_titles as $academic_title) : ?>
    <option value="<?php echo esc_attr($academic_title); ?>"
      <?php selected($current_academic_title, $academic_title); ?>>
      <?php echo esc_html($academic_title); ?>
    </option>
    <?php endforeach; ?>
  </select>
</div>
<div>
  <label>Tytuł dodatkowy</label><br>
  <input type="text" name="ii_posttitle" value="<?php echo esc_attr($posttitle); ?>">
</div>
<div>
  <label>Typ pracownika</label><br>
  <select name="ii_type">
    <?php foreach ($types as $type) : ?>
    <option value="<?php echo esc_attr($type); ?>" <?php selected($current_type, $type); ?>>
      <?php echo esc_html($type); ?>
    </option>
    <?php endforeach; ?>
  </select>
</div>
<div>
  <label>Email</label><br>
  <input type="text" name="ii_email" value="<?php echo esc_attr($email); ?>">
</div>
<div>
  <label>Telefon</label><br>
  <input type="text" name="ii_phone_number" value="<?php echo esc_attr($phone_number); ?>">
</div>
<div>
  <label>Pokój</label><br>
  <input type="text" name="ii_room" value="<?php echo esc_attr($room); ?>">
</div>
<div>
  <label>Sortowanie</label><br>
  <input type="text" name="ii_sort" value="<?php echo esc_attr($sort); ?>">
</div>
<div>
  <label>Stanowisko</label><br>
  <select name="ii_position">
    <?php foreach ($positions as $position) : ?>
    <option value="<?php echo esc_attr($position); ?>" <?php selected($current_position, $position); ?>>
      <?php echo esc_html($position); ?>
    </option>
    <?php endforeach; ?>
  </select>
</div>
<div>
  <label>Stanowisko w strukturze</label><br>
  <select name="ii_structure_position">
    <?php foreach ($structure_positions as $structure_position) : ?>
    <option value="<?php echo esc_attr($structure_position); ?>"
      <?php selected($current_structure_position, $structure_position); ?>>
      <?php echo esc_html($structure_position); ?>
    </option>
    <?php endforeach; ?>
  </select>
</div>
<?php
}

// Funkcja do zapisu danych z metaboxów dla pracownika
function ii_pracownik_meta_box_save($id_post)
{
    if (isset($_POST['ii_academic_title'])) {
        update_post_meta($id_post, 'ii_academic_title', $_POST['ii_academic_title']);
    }
    if (isset($_POST['ii_posttitle'])) {
        update_post_meta($id_post, 'ii_posttitle', $_POST['ii_posttitle']);
    }
    if (isset($_POST['ii_type'])) {
        update_post_meta($id_post, 'ii_type', $_POST['ii_type']);
    }
    if (isset($_POST['ii_email'])) {
        update_post_meta($id_post, 'ii_email', $_POST['ii_email']);
    }
    if (isset($_POST['ii_phone_number'])) {
        update_post_meta($id_post, 'ii_phone_number', $_POST['ii_phone_number']);
    }
    if (isset($_POST['ii_room'])) {
        update_post_meta($id_post, 'ii_room', $_POST['ii_room']);
    }
    if (isset($_POST['ii_sort'])) {
        update_post_meta($id_post, 'ii_sort', $_POST['ii_sort']);
    }
    if (isset($_POST['ii_position'])) {
        update_post_meta($id_post, 'ii_position', $_POST['ii_position']);
    }
    if (isset($_POST['ii_structure_position'])) {
        update_post_meta($id_post, 'ii_structure_position', $_POST['ii_structure_position']);
    }
}
add_action('add_meta_boxes_pracownik', 'ii_pracownik_meta_box');
add_action('save_post', 'ii_pracownik_meta_box_save');

// Shortcode wyświetlający konkretnego pracownika
function ii_pracownik_shortcode()
{
    global $post;
    $title = get_the_title($post->ID);
    $meta = get_post_meta($post->ID);

    $academic_title = isset($meta['ii_academic_title'][0]) ? $meta['ii_academic_title'][0] : '';
    $posttitle = isset($meta['ii_posttitle'][0]) ? $meta['ii_posttitle'][0] : '';
    $email = isset($meta['ii_email'][0]) ? $meta['ii_email'][0] : '';
    $phone_number = isset($meta['ii_phone_number'][0]) ? $meta['ii_phone_number'][0] : '';
    $room = isset($meta['ii_room'][0]) ? $meta['ii_room'][0] : '';

    $structure_terms = wp_get_post_terms($post->ID, 'structure');
    $structure = !is_wp_error($structure_terms) && !empty($structure_terms) ? $structure_terms[0]->name : '';

    $text = '<div class="worker-item">
    <div class="worker-img-cnt">
        <figure class="worker-img">
            <span class="avatar-img"></span>
            <!-- <img src="" alt=""> -->
        </figure>
    </div>
    <div class="worker-data">
        <h2>' . esc_html($academic_title) . ' ' . esc_html($title);

    if (!empty($posttitle)) {
        $text .= ', ' . esc_html($posttitle);
    }

    $text .= '</h2>';

    if (!empty($structure)) {
        $text .= '<p class="worker-place">' . esc_html($structure) . '</p>';
    }

    $text .= '<p class="worker-email"><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p>
        <p class="worker-phone-number">tel: ' . esc_html($phone_number) . '</p>    
        <p class="worker-room">pok: ' . esc_html($room) . '</p>    
        </div>
      </div>';

    return $text;
}
add_shortcode('pracownik', 'ii_pracownik_shortcode');

// 
function ii_add_taxonomies()
{
    register_taxonomy('structure', 'pracownik', array(
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Struktura instytutu',
            'singular_name' => 'Struktura instytutu',
            'menu_name'           => 'Struktura instytutu',
            'parent_item_colon'   => 'Nadrzędna',
            'all_items'           => 'Wszyscy',
            'view_item'           => 'Zobacz',
            'add_new_item'        => 'Dodaj',
            'add_new'             => 'Dodaj',
            'edit_item'           => 'Edytuj',
            'update_item'         => 'Aktualizuj',
            'search_items'        => 'Szukaj',
            'not_found'           => 'Nie znaleziono',
            'not_found_in_trash'  => 'Nie znaleziono'
        ),
        'rewrite' => array(
            'slug' => 'locations',
            'with_front' => false,
            'hierarchical' => true
        ),
    ));
}
add_action('init', 'ii_add_taxonomies', 0);

// Shortcode wyświetlający wszystkich pracowników
function ii_pracownicy_wszyscy_shortcode()
{
    $output = '<div class="pracownicy-list">';

    $query = new WP_Query(array(
        'post_type'      => 'pracownik',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_key'       => 'ii_sort',
        'orderby'        => 'meta_value',
        'order'          => 'ASC'
    ));


    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $title = get_the_title();
            $meta = get_post_meta($post_id);
            $email = isset($meta['ii_email'][0]) ? $meta['ii_email'][0] : '';
            $academic_title = isset($meta['ii_academic_title'][0]) ? $meta['ii_academic_title'][0] : '';
            $posttitle = isset($meta['ii_posttitle'][0]) ? $meta['ii_posttitle'][0] : '';

            $structure_terms = wp_get_post_terms($post_id, 'structure');
            $structure = !is_wp_error($structure_terms) && !empty($structure_terms) ? $structure_terms[0]->name : '';

            $image_url = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'thumbnail') : '';

            $output .= '<div class="pracownik-item">';
            $output .= '<div class="pracownik-img-cnt"><img src="' . esc_url($image_url) . '" alt="Profile Picture"></div>';
            $output .= '<h3>' . esc_html($academic_title) . ' ' . esc_html($title);

            if (!empty($posttitle)) {
                $output .= ', ' . esc_html($posttitle);
            }

            $output .= '</h3>';

            if (!empty($structure)) {
                $output .= '<p>' . esc_html($structure) . '</p>';
            } else {
                $output .= '<p>Brak struktury</p>';
            }

            $output .= '<p><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p>';
            $output .= '</div>';
        }
    } else {
        $output .= '<p>Nie znaleziono pracowników.</p>';
    }

    $output .= '</div>';

    wp_reset_postdata();

    return $output;
}
add_shortcode('pracownicy_wszyscy', 'ii_pracownicy_wszyscy_shortcode');

//Shortcode wyświetlający Strukturę Instytutu
function ii_pracownicy_struktura_shortcode()
{
    $output = '<div class="pracownicy-struktura">';

    // Dyrekcja
    $output .= '<div class="pracownicy-dyrekcja">';
    $output .= '<h1>Dyrekcja</h1>';

    // Wyświetlanie dyrektora
    $output .= '<h2>Dyrektor:</h2>';
    $args_dyrektor = array(
        'post_type' => 'pracownik',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'ii_position',
                'value' => 'dyrektor',
                'compare' => '=',
            ),
        ),
    );

    $query_dyrektor = new WP_Query($args_dyrektor);

    if ($query_dyrektor->have_posts()) {
        while ($query_dyrektor->have_posts()) {
            $query_dyrektor->the_post();
            $meta = get_post_meta(get_the_ID());

            $academic_title = isset($meta['ii_academic_title'][0]) ? $meta['ii_academic_title'][0] : '';
            $posttitle = isset($meta['ii_posttitle'][0]) ? $meta['ii_posttitle'][0] : '';

            $output .= '<p>' . esc_html($academic_title) . ' ' . get_the_title();
            if (!empty($posttitle)) {
                $output .= ' ' . esc_html($posttitle);
            }
            $output .= '</p>';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>Brak dyrektora.</p>';
    }

    // Wyświetlanie zastępcy dyrektora
    $output .= '<h2>Zastępca Dyrektora:</h2>';
    $args_zastepca = array(
        'post_type' => 'pracownik',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'ii_position',
                'value' => 'zastępca',
                'compare' => '=',
            ),
        ),
    );

    $query_zastepca = new WP_Query($args_zastepca);

    if ($query_zastepca->have_posts()) {
        while ($query_zastepca->have_posts()) {
            $query_zastepca->the_post();
            $meta = get_post_meta(get_the_ID());

            $academic_title = isset($meta['ii_academic_title'][0]) ? $meta['ii_academic_title'][0] : '';
            $posttitle = isset($meta['ii_posttitle'][0]) ? $meta['ii_posttitle'][0] : '';

            $output .= '<p>' . esc_html($academic_title) . ' ' . get_the_title();
            if (!empty($posttitle)) {
                $output .= ' ' . esc_html($posttitle);
            }
            $output .= '</p>';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>Brak zastępcy dyrektora.</p>';
    }

    $output .= '</div>';

// Katedry
$output .= '<div class="pracownicy-katedry">';
$output .= '<h1>Katedry</h1>';

$terms = get_terms(array(
    'taxonomy' => 'structure',
    'hide_empty' => false,
));

if (!empty($terms) && !is_wp_error($terms)) {
    $output .= '<table style="width: 100%;">';

    $term_count = count($terms);
    for ($i = 0; $i < $term_count; $i += 2) {
        $output .= '<tr>';

        $output .= '<td style="vertical-align: top;">';
        $output .= '<h2>' . esc_html($terms[$i]->name) . '</h2>';

        $args_kierownik = array(
            'post_type' => 'pracownik',
            'posts_per_page' => 1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'structure',
                    'field' => 'term_id',
                    'terms' => $terms[$i]->term_id,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => 'ii_structure_position',
                    'value' => 'kierownik',
                    'compare' => '=',
                ),
            ),
        );

        $query_kierownik = new WP_Query($args_kierownik);
        if ($query_kierownik->have_posts()) {
            while ($query_kierownik->have_posts()) {
                $query_kierownik->the_post();
                $meta = get_post_meta(get_the_ID());
                $output .= '<p>' . esc_html($meta['ii_academic_title'][0]) . ' ' . esc_html(get_the_title()) . ' (Kierownik katedry)</p>';
            }
            wp_reset_postdata();
        } else {
            $output .= '<p>Brak kierownika katedry.</p>';
        }

        $args_pozostali = array(
            'post_type' => 'pracownik',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'structure',
                    'field' => 'term_id',
                    'terms' => $terms[$i]->term_id,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => 'ii_structure_position',
                    'value' => 'kierownik',
                    'compare' => '!=',
                ),
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'ii_sort',
            'order' => 'ASC',
        );

        $query_pozostali = new WP_Query($args_pozostali);
        if ($query_pozostali->have_posts()) {
            while ($query_pozostali->have_posts()) {
                $query_pozostali->the_post();
                $meta = get_post_meta(get_the_ID());
                $output .= '<p>' . esc_html($meta['ii_academic_title'][0]) . ' ' . esc_html(get_the_title()) . ' ' . esc_html($meta['ii_posttitle'][0]) . '</p>';
            }
            wp_reset_postdata();
        } else {
            $output .= '<p>Brak pozostałych pracowników katedry.</p>';
        }
        
        $output .= '</td>';

        if (isset($terms[$i + 1])) {
            $output .= '<td style="vertical-align: top;">';
            $output .= '<h2>' . esc_html($terms[$i + 1]->name) . '</h2>';

            $args_kierownik['tax_query'][0]['terms'] = $terms[$i + 1]->term_id;
            $query_kierownik = new WP_Query($args_kierownik);
            if ($query_kierownik->have_posts()) {
                while ($query_kierownik->have_posts()) {
                    $query_kierownik->the_post();
                    $meta = get_post_meta(get_the_ID());
                    $output .= '<p>' . esc_html($meta['ii_academic_title'][0]) . ' ' . esc_html(get_the_title()) . ' (Kierownik katedry)</p>';
                }
                wp_reset_postdata();
            } else {
                $output .= '<p>Brak kierownika katedry.</p>';
            }

            $args_pozostali['tax_query'][0]['terms'] = $terms[$i + 1]->term_id;
            $query_pozostali = new WP_Query($args_pozostali);
            if ($query_pozostali->have_posts()) {
                while ($query_pozostali->have_posts()) {
                    $query_pozostali->the_post();
                    $meta = get_post_meta(get_the_ID());
                    $output .= '<p>' . esc_html($meta['ii_academic_title'][0]) . ' ' . esc_html(get_the_title()) . ' ' . esc_html($meta['ii_posttitle'][0]) . '</p>';
                }
                wp_reset_postdata();
            } else {
                $output .= '<p>Brak pozostałych pracowników katedry.</p>';
            }

            $output .= '</td>';
        } else {
            $output .= '<td></td>';
        }

        $output .= '</tr>';
    }

    $output .= '</table>';
} else {
    $output .= '<p>Brak katedr.</p>';
}

$output .= '</div>';



    // Administracja
    $output .= '<div class="pracownicy-administracja">';
    $output .= '<h1>Obsługa administracyjno-techniczna</h1>';

    // Sekretariat
    $output .= '<h2>Sekretariat:</h2>';
    $args_sekretariat = array(
        'post_type' => 'pracownik',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'ii_position',
                'value' => 'sekretariat',
                'compare' => '=',
            ),
        ),
    );

    $query_sekretariat = new WP_Query($args_sekretariat);

    if ($query_sekretariat->have_posts()) {
        while ($query_sekretariat->have_posts()) {
            $query_sekretariat->the_post();
            $meta = get_post_meta(get_the_ID());

            $academic_title = isset($meta['ii_academic_title'][0]) ? $meta['ii_academic_title'][0] : '';
            $posttitle = isset($meta['ii_posttitle'][0]) ? $meta['ii_posttitle'][0] : '';
            $room = isset($meta['ii_room'][0]) ? $meta['ii_room'][0] : '';

            $output .= '<p>' . esc_html($academic_title) . ' ' . get_the_title();
            if (!empty($posttitle)) {
                $output .= ', ' . esc_html($posttitle);
            }
            if (!empty($room)) {
                $output .= ' pok. ' . esc_html($room);
            }
            $output .= '</p>';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>Brak pracowników sekretariatu.</p>';
    }

    // Obsługa techniczna
    $output .= '<h2>Obsługa techniczna:</h2>';
    $args_techniczna = array(
        'post_type' => 'pracownik',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'ii_position',
                'value' => 'obsługa techniczna',
                'compare' => '=',
            ),
        ),
    );

    $query_techniczna = new WP_Query($args_techniczna);

    if ($query_techniczna->have_posts()) {
        while ($query_techniczna->have_posts()) {
            $query_techniczna->the_post();
            $meta = get_post_meta(get_the_ID());

            $academic_title = isset($meta['ii_academic_title'][0]) ? $meta['ii_academic_title'][0] : '';
            $posttitle = isset($meta['ii_posttitle'][0]) ? $meta['ii_posttitle'][0] : '';
            $room = isset($meta['ii_room'][0]) ? $meta['ii_room'][0] : '';

            $output .= '<p>' . esc_html($academic_title) . ' ' . get_the_title();
            if (!empty($posttitle)) {
                $output .= ', ' . esc_html($posttitle);
            }
            if (!empty($room)) {
                $output .= ' pok. ' . esc_html($room);
            }
            $output .= '</p>';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>Brak pracowników obsługi technicznej.</p>';
    }

    $output .= '</div>';

    $output .= '</div>';

    return $output;
}
add_shortcode('pracownicy_struktura', 'ii_pracownicy_struktura_shortcode');