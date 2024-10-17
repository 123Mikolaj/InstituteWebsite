<?php
/*
Plugin Name: Formularz
Description: Wtyczka do obsługi formularza.
Version: 1.3
Author: Estarte
License: GPL2
*/

// Aktywacja i dezaktywacja wtyczki
register_activation_hook(__FILE__, 'formularz_install');
register_deactivation_hook(__FILE__, 'formularz_uninstall');

// Funkcja aktywacji
function formularz_install()
{
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();

  // Tabela formularzy
  $table_forms = $wpdb->prefix . 'form_forms';
  $sql_forms = "CREATE TABLE $table_forms (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

  // Tabela pytań
  $table_questions = $wpdb->prefix . 'form_questions';
  $sql_questions = "CREATE TABLE $table_questions (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        idform mediumint(9) NOT NULL,
        name varchar(255) NOT NULL,
        description text,
        type varchar(50) NOT NULL,
        number int NOT NULL,
        need boolean DEFAULT 0,
        next mediumint(9),
        PRIMARY KEY  (id),
        FOREIGN KEY (idform) REFERENCES $table_forms(id) ON DELETE CASCADE
    ) $charset_collate;";

  // Tabela opcji pytań zamkniętych
  $table_options = $wpdb->prefix . 'form_options';
  $sql_options = "CREATE TABLE $table_options (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        idquestion mediumint(9) NOT NULL,
        value varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (idquestion) REFERENCES $table_questions(id) ON DELETE CASCADE
    ) $charset_collate;";

  // Tabela odpowiedzi
  $table_answers = $wpdb->prefix . 'form_answers';
  $sql_answers = "CREATE TABLE $table_answers (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        idform mediumint(9) NOT NULL,
        idquestion mediumint(9) NOT NULL,
        iduser mediumint(9) NOT NULL,
        time datetime DEFAULT CURRENT_TIMESTAMP,
        answer text NOT NULL,
        description text,
        PRIMARY KEY  (id),
        FOREIGN KEY (idform) REFERENCES $table_forms(id) ON DELETE CASCADE,
        FOREIGN KEY (idquestion) REFERENCES $table_questions(id) ON DELETE CASCADE
    ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql_forms);
  dbDelta($sql_questions);
  dbDelta($sql_options);
  dbDelta($sql_answers);
}

// Funkcja dezaktywacji
function formularz_uninstall()
{
  global $wpdb;
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}form_answers");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}form_options");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}form_questions");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}form_forms");
}

// Dodanie zakładki do menu
add_action('admin_menu', 'formularz_menu');

function formularz_menu()
{
  add_menu_page(
    'Formularz',
    'Formularz',
    'manage_options',
    'formularz',
    'formularz_page',
    'dashicons-feedback',
    6
  );
}

function formularz_page()
{
  global $wpdb;
  $table_forms = $wpdb->prefix . 'form_forms';
  $table_questions = $wpdb->prefix . 'form_questions';
  $table_options = $wpdb->prefix . 'form_options';

  // Obsługa usuwania formularza
  if (isset($_GET['action']) && $_GET['action'] == 'delete_form' && isset($_GET['id'])) {
    $form_id = intval($_GET['id']);
    $wpdb->delete($table_forms, ['id' => $form_id]);
    echo '<div class="notice notice-success is-dismissible"><p>Formularz został usunięty!</p></div>';
  }

  // Obsługa usuwania pytania
  if (isset($_GET['action']) && $_GET['action'] == 'delete_question' && isset($_GET['id'])) {
    $question_id = intval($_GET['id']);
    $wpdb->delete($table_questions, ['id' => $question_id]);
    $wpdb->delete($table_options, ['idquestion' => $question_id]);
    echo '<div class="notice notice-success is-dismissible"><p>Pytanie zostało usunięte!</p></div>';
  }

  // Dodanie nowego formularza
  if (isset($_POST['new_form'])) {
    $name = sanitize_text_field($_POST['form_name']);
    $wpdb->insert($table_forms, ['name' => $name]);
    echo '<div class="notice notice-success is-dismissible"><p>Nowy formularz został dodany!</p></div>';
  }

  // Dodanie nowego pytania
  if (isset($_POST['new_question'])) {
    $form_id = intval($_POST['form_id']);
    $name = sanitize_text_field($_POST['question_name']);
    $description = sanitize_textarea_field($_POST['question_description']);
    $type = sanitize_text_field($_POST['question_type']);
    $need = isset($_POST['question_need']) ? 1 : 0;
    $number = intval($_POST['question_number']);

    $wpdb->insert($table_questions, [
      'idform' => $form_id,
      'name' => $name,
      'description' => $description,
      'type' => $type,
      'need' => $need
    ]);
    echo '<div class="notice notice-success is-dismissible"><p>Nowe pytanie zostało dodane!</p></div>';
  }

  // Dodanie opcji do pytania
  if (isset($_POST['new_option'])) {
    $question_id = intval($_POST['question_id']);
    $value = sanitize_text_field($_POST['option_value']);
    $wpdb->insert($table_options, ['idquestion' => $question_id, 'value' => $value]);
    echo '<div class="notice notice-success is-dismissible"><p>Nowa opcja została dodana!</p></div>';
  }

  // Edycja formularza
  if (isset($_GET['action']) && $_GET['action'] == 'edit_form' && isset($_GET['id'])) {
    $form_id = intval($_GET['id']);
    $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_forms WHERE id = %d", $form_id));

    if ($form) {
      // Obsługa zmiany nazwy formularza
      if (isset($_POST['update_form'])) {
        $new_name = sanitize_text_field($_POST['form_name']);
        $wpdb->update($table_forms, ['name' => $new_name], ['id' => $form_id]);
        echo '<div class="notice notice-success is-dismissible"><p>Nazwa formularza została zmieniona!</p></div>';
      }

      echo '<div class="wrap">';
      echo '<h1>Edytuj Formularz: ' . esc_html($form->name) . '</h1>';
      echo '<form method="post" action="" style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">';
      echo '<input type="hidden" name="form_id" value="' . esc_attr($form_id) . '">';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="form_name" style="display: block; font-weight: bold; margin-bottom: 5px;">Nazwa formularza: </label>';
      echo '<input type="text" id="form_name" name="form_name" value="' . esc_attr($form->name) . '" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">';
      echo '</div>';
      echo '<input type="submit" name="update_form" class="button button-primary" value="Zapisz zmiany">';
      echo '</form>';

      // Dodawanie pytania
      echo '<h2>Dodaj nowe pytanie</h2>';
      echo '<form method="post" action="" style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">';
      echo '<input type="hidden" name="form_id" value="' . esc_attr($form_id) . '">';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="question_name" style="display: block; font-weight: bold; margin-bottom: 5px;">Nazwa pytania: </label>';
      echo '<input type="text" id="question_name" name="question_name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">';
      echo '</div>';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="question_description" style="display: block; font-weight: bold; margin-bottom: 5px;">Opis pytania: </label>';
      echo '<textarea id="question_description" name="question_description" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>';
      echo '</div>';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="question_type" style="display: block; font-weight: bold; margin-bottom: 5px;">Typ pytania: </label>';
      echo '<select id="question_type" name="question_type" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="text">Tekstowe</option>
            <option value="closed">Zamknięte (jednokrotny wybór)</option>
            </select>';
      echo '</div>';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="question_need" style="font-weight: bold; margin-right: 10px;">Wymagane: </label>';
      echo '<input type="checkbox" id="question_need" name="question_need">';
      echo '</div>';
      echo '<input type="submit" name="new_question" class="button button-primary" value="Dodaj Pytanie">';
      echo '</form>';
      echo '</div>';

      // Wyświetlanie pytań dla tego formularza
      $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_questions WHERE idform = %d ORDER BY number", $form_id));

      if ($questions) {
        echo '<h2>Dodane pytania</h2>';
        echo '<ul style="list-style-type: none; padding: 0;">';
        foreach ($questions as $question) {
          echo '<li style="background-color: #f9f9f9; margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">';
          echo '<h3 style="margin: 0; font-size: 18px;">' . esc_html($question->name) . '</h3>';
          echo '<p style="margin-top: 5px; font-style: italic; color: #555;">' . esc_html($question->description) . '</p>';
          echo '<a href="?page=formularz&action=delete_question&id=' . esc_attr($question->id) . '" class="button button-secondary" style="margin-right: 10px;">Usuń Pytanie</a>';

          // Dodawanie opcji do pytania zamkniętego
          if ($question->type === 'closed') {
            echo '<h4>Dodaj opcje odpowiedzi:</h4>';
            echo '<form method="post" action="" style="margin-bottom: 10px;">';
            echo '<input type="hidden" name="question_id" value="' . esc_attr($question->id) . '">';
            echo '<input type="text" id="option_value" name="option_value" required style="width: 100%; padding: 8px; margin-bottom: 5px; border: 1px solid #ccc; border-radius: 4px;">';
            echo '<input type="submit" name="new_option" class="button button-primary" value="Dodaj Opcję">';
            echo '</form>';

            // Wyświetlanie opcji dla pytania
            $options = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_options WHERE idquestion = %d", $question->id));
            if ($options) {
              echo '<ul style="list-style-type: disc; padding-left: 20px; margin-top: 10px;">';
              foreach ($options as $option) {
                echo '<li>' . esc_html($option->value) . '</li>';
              }
              echo '</ul>';
            }
          }

          echo '</li>';
        }
        echo '</ul>';
      } else {
        echo '<p>Brak pytań w formularzu.</p>';
      }

      echo '</div>';
      return;
    } else {
      echo '<div class="notice notice-error is-dismissible"><p>Nie znaleziono formularza.</p></div>';
    }
  }

  // Formularz dodawania nowego formularza
  echo '<div class="wrap"><h1>Formularze</h1>';
  echo '<form method="post" action="">';
  echo '<h2>Dodaj nowy formularz</h2>';
  echo '<label for="form_name">Nazwa: </label>';
  echo '<input type="text" id="form_name" name="form_name" required><br>';
  echo '<input type="submit" name="new_form" class="button button-primary" value="Dodaj Formularz">';
  echo '</form>';


  // Wyświetlanie istniejących formularzy
  $forms = $wpdb->get_results("SELECT * FROM $table_forms");

  if ($forms) {
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Nazwa</th><th>Akcje</th></tr></thead>';
    echo '<tbody>';

    foreach ($forms as $form) {
      echo '<tr>';
      echo '<td>' . esc_html($form->id) . '</td>';  // Wyświetlanie ID formularza
      echo '<td>' . esc_html($form->name) . '</td>';
      echo '<td>';
      echo '<a href="?page=formularz&action=delete_form&id=' . esc_attr($form->id) . '" class="button button-secondary">Usuń Formularz</a> ';
      echo '<a href="?page=formularz&action=edit_form&id=' . esc_attr($form->id) . '" class="button button-secondary">Edytuj Formularz</a>';
      echo '<button class="toggle-form button button-secondary">Pokaż</button>'; // Add the Pokaż button
      echo '</td>';
      echo '</tr>';


      // Add a div to wrap the form content

      echo '<tr><td colspan="3"><div class="form-content" style="display: none;">';


      // Form content goes here

      echo '<div class="wrap">';
      echo '<h1>Edytuj Formularz: ' . esc_html($form->name) . '</h1>';
      echo '<form method="post" action="" style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">';
      echo '<input type="hidden" name="form_id" value="' . esc_attr($form->id) . '">';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="form_name" style="display: block; font-weight: bold; margin-bottom: 5px;">Nazwa formularza: </label>';
      echo '<input type="text" id="form_name" name="form_name" value="' . esc_attr($form->name) . '" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">';
      echo '</div>';
      echo '<input type="submit" name="update_form" class="button button-primary" value="Zapisz zmiany">';
      echo '</form>';

      // Dodawanie pytania
      echo '<h2>Dodaj nowe pytanie</h2>';
      echo '<form method="post" action="" style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">';
      echo '<input type="hidden" name="form_id" value="' . esc_attr($form_id) . '">';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="question_name" style="display: block; font-weight: bold; margin-bottom: 5px;">Nazwa pytania: </label>';
      echo '<input type="text" id="question_name" name="question_name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">';
      echo '</div>';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="question_description" style="display: block; font-weight: bold; margin-bottom: 5px;">Opis pytania: </label>';
      echo '<textarea id="question_description" name="question_description" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>';
      echo '</div>';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="question_type" style="display: block; font-weight: bold; margin-bottom: 5px;">Typ pytania: </label>';
      echo '<select id="question_type" name="question_type" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
      <option value="text">Tekstowe</option>
      <option value="closed">Zamknięte (jednokrotny wybór)</option>
      </select>';
      echo '</div>';
      echo '<div style="margin-bottom: 10px;">';
      echo '<label for="question_need" style="font-weight: bold; margin-right: 10px;">Wymagane: </label>';
      echo '<input type="checkbox" id="question_need" name="question_need">';
      echo '</div>';
      echo '<input type="submit" name="new_question" class="button button-primary" value="Dodaj Pytanie">';
      echo '</form>';
      echo '</div>';

      // Wyświetlanie pytań dla tego formularza
      $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_questions WHERE idform = %d ORDER BY number", $form->id));


      if ($questions) {
        echo '<h2>Dodane pytania</h2>';
        echo '<ul style="list-style-type: none; padding: 0;">';

        foreach ($questions as $question) {
          echo '<li style="background-color: #f9f9f9; margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">';
          echo '<h3 style="margin: 0; font-size: 18px;">' . esc_html($question->name) . '</h3>';
          echo '<p style="margin-top: 5px; font-style: italic; color: #555;">' . esc_html($question->description) . '</p>';
          echo '<a href="?page=formularz&action=delete_question&id=' . esc_attr($question->id) . '" class="button button-secondary" style="margin-right: 10px;">Usuń Pytanie</a>';


          // Dodawanie opcji do pytania zamkniętego
          if ($question->type === 'closed') {
            echo '<h4>Dodaj opcje odpowiedzi:</h4>';
            echo '<form method="post" action="" style="margin-bottom: 10px;">';
            echo '<input type="hidden" name="question_id" value="' . esc_attr($question->id) . '">';
            echo '<input type="text" id="option_value" name="option_value" required style="width: 100%; padding: 8px; margin-bottom: 5px; border: 1px solid #ccc; border-radius: 4px;">';
            echo '<input type="submit" name="new_option" class="button button-primary" value="Dodaj Opcję">';
            echo '</form>';
          }
        }
      }
      echo '</div>'; // Close the form-content div
      echo '</td></tr>'; // Close the table row
      echo '</div></td></tr>';
    }

    echo '</tbody>';
    echo '</table>';
  } else {

    echo '<p>Brak dostępnych formularzy.</p>';
  }

  echo '</div>';


  // Add JavaScript to toggle the form content
?>
  <script>
    jQuery(document).ready(function($) {
      $('.toggle-form').on('click', function() {
        $(this).closest('tr').next('tr').find('.form-content').slideToggle();
      });
    });
  </script>
<?php

}

// Shortcode do wyświetlania formularza
add_shortcode('wyswietl_formularz', 'wyswietl_formularz_func');

// Shortcode do wyświetlania formularza przypisanego do wydarzenia
add_shortcode('formularz', 'wyswietl_formularz_event_func');

function wyswietl_formularz_event_func()
{
  // Get the current post ID (which is the event in this case)
  $post_id = get_the_ID();

  // Retrieve the form ID associated with this event
  $form_id = get_post_meta($post_id, '_event_form_id', true);

  if (!$form_id) {
    return 'Nie przypisano formularza do tego wydarzenia.';
  }

  // Call the function to display the form based on the retrieved form ID
  return wyswietl_formularz_func(array('id' => $form_id));
}

function wyswietl_formularz_func($atts)
{
  global $wpdb;
  $table_forms = $wpdb->prefix . 'form_forms';
  $table_questions = $wpdb->prefix . 'form_questions';
  $table_options = $wpdb->prefix . 'form_options';
  $table_answers = $wpdb->prefix . 'form_answers';
  $form_id = intval($atts['id']);

  $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_forms WHERE id = %d", $form_id));

  if ($form) {
    $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_questions WHERE idform = %d ORDER BY number", $form_id));

    $output = '<div><h2>' . esc_html($form->name) . '</h2>';
    $output .= '<form method="post" action="">';

    // Add nonce field for security
    $output .= wp_nonce_field('wyswietl_formularz_action', 'wyswietl_formularz_nonce', true, false);

    foreach ($questions as $question) {
      $output .= '<label>' . esc_html($question->name) . '</label><br>';

      if ($question->type === 'text') {
        // Text input
        $output .= '<input type="text" name="question_' . esc_attr($question->id) . '"><br>';
      } elseif ($question->type === 'closed') {
        // Closed question - radio buttons
        $options = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_options WHERE idquestion = %d", $question->id));
        foreach ($options as $option) {
          $output .= '<input type="radio" name="question_' . esc_attr($question->id) . '" value="' . esc_attr($option->value) . '">' . esc_html($option->value) . '<br>';
        }
      } elseif ($question->type === 'single') {
        // Single choice question - select box
        $options = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_options WHERE idquestion = %d", $question->id));
        $output .= '<select name="question_' . esc_attr($question->id) . '">';
        foreach ($options as $option) {
          $output .= '<option value="' . esc_attr($option->value) . '">' . esc_html($option->value) . '</option>';
        }
        $output .= '</select><br>';
      } elseif ($question->type === 'multiple') {
        // Multiple choice question - checkboxes
        $options = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_options WHERE idquestion = %d", $question->id));
        foreach ($options as $option) {
          $output .= '<input type="checkbox" name="question_' . esc_attr($question->id) . '[]" value="' . esc_attr($option->value) . '">' . esc_html($option->value) . '<br>';
        }
      }
    }

    $output .= '<input type="submit" value="Wyślij">';
    $output .= '</form></div>';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wyswietl_formularz_nonce']) && wp_verify_nonce($_POST['wyswietl_formularz_nonce'], 'wyswietl_formularz_action')) {
      $user_id = get_current_user_id();
      $submitted_data = array();

      foreach ($_POST as $key => $value) {
        if (strpos($key, 'question_') === 0) {
          $question_id = intval(str_replace('question_', '', $key));
          $submitted_data[$question_id] = $value;
        }
      }

      foreach ($submitted_data as $question_id => $answer) {
        if (is_array($answer)) {
          // Handle multiple choice answers
          foreach ($answer as $selected_option) {
            $wpdb->insert($table_answers, [
              'idform' => $form_id,
              'idquestion' => $question_id,
              'iduser' => $user_id,
              'answer' => $selected_option
            ]);
          }
        } else {
          // Handle single answers
          $wpdb->insert($table_answers, [
            'idform' => $form_id,
            'idquestion' => $question_id,
            'iduser' => $user_id,
            'answer' => $answer
          ]);
        }
      }

      $output .= '<div class="notice notice-success is-dismissible"><p>Wypełniono formularz!</p></div>';
      global $wpdb;

      // Wyświetlanie odpowiedzi dla użytkownika
      $answers = $wpdb->get_results(

        $wpdb->prepare(

          "
        SELECT a.answer, q.name AS question_name
        FROM {$wpdb->prefix}form_answers AS a
        INNER JOIN {$wpdb->prefix}form_questions AS q ON a.idquestion = q.id
        WHERE a.idform = %d AND a.iduser = %d
        ORDER BY a.id ASC
        ",

          $form_id,
          $user_id
        )
      );


      if ($answers) {
        echo "<h3>Answers for User {$user_id} in Form {$form_id}</h3>";
        echo "<ul>";

        foreach ($answers as $answer) {
          echo "<li>{$answer->question_name}: {$answer->answer}</li>";
        }

        echo "</ul>";
      } else {

        echo "No answers found.";
      }
      // Koniec wyświetlania odpowiedzi
    }

    return $output;
  } else {
    return 'Nie znaleziono formularza.';
  }
}
