<?php
/*
Plugin Name: read excel
Plugin URI: https://ii.uken.krakow.pl
Description: Wtyczka dedykowana dla II
Version: 1.0
Author: Estarte
License: GNU
*/


// Autoload PHPSpreadsheet classes
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


// Dodanie zakładki w menu
function re_menu()
{
    add_menu_page(
        'Excel',
        'Excel',
        'manage_options',
        're_start',
        're_start',
        '',
        4
    );

    add_submenu_page(
        're_start',
        'New Submenu Item',
        'New Submenu Item',
        'anage_options',
        'new_submenu_item',
        'new_submenu_item_callback'
    );
}
add_action('admin_menu', 're_menu');

// Widok strony
function re_start()
{
    if ($_REQUEST['re_add'] == 'true') {
        re_read_excel();
    }


?>
<h2>Wczytywanie danych z excela</h2>
<form method="post" action="#" class="form">
  <div class="form-group">
    <input type="hidden" name="re_add" value="true">

    <input type="submit" class="btn btn-primary" value="Czytaj" />
  </div>
</form>
<?php

}

function re_read_excel()
{
    require 'vendor/autoload.php';

    // Ścieżka do pliku Excel
    $inputFileName = plugin_dir_path(__FILE__) . 'CB_N_2023.xlsx';
    echo $inputFileName;

    // Czytanie pliku Excel
    try {
        // Zamiast PHPExcel_IOFactory używamy IOFactory z PHPSpreadsheet
        $spreadsheet = IOFactory::load($inputFileName);
    } catch (Exception $e) {
        die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    // Uzyskiwanie arkusza
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $data = array(); // Tablica z danymi

    // Pętla przez każdy wiersz arkusza
    for ($row = 1; $row <= $highestRow; $row++) {
        // Wczytywanie wiersza danych do tablicy
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
        $data[] = $rowData[0];
    }

    // Wyświetlanie danych
    foreach ($data as $rowData) {
        echo 'Semestr ' . $rowData[0] . ', przedmiot: ' . $rowData[1] . '<br>';
    }

    echo 'test ok';
}

function add_semesters_excel2($attachment_id, $idRocznik)
{
    require 'vendor/autoload.php';
    include_once plugin_dir_path(__FILE__) . '../ii/semestry.php';
    
    static $called = false;
    if ($called) return;
    $called = true;

    // Pobierz lokalną ścieżkę załączonego pliku
    $file_path = get_attached_file($attachment_id);

    $inputFileName = plugin_dir_path(__FILE__) . 'CB_N_2023.xlsx';
    
    // Czytanie pliku Excel
    try {
        $spreadsheet = IOFactory::load($file_path);
    } catch (Exception $e) {
        die('Błąd ładowania pliku "' . pathinfo($file_path, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }


    // Pobierz arkusz
    $sheet = $spreadsheet->getSheet(0);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    // Inicjalizuj tablicę danych
    $data = [];

    // Przeiteruj przez każdy wiersz arkusza (zacznij od wiersza 2, aby pominąć nagłówki)
    for ($row = 2; $row <= $highestRow; $row++) {
        // Przeczytaj wiersz danych do tablicy
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, TRUE);
        $data[] = $rowData[0];
    }

    // Zainicjalizuj zmienne
    $addedSemesters = [];
    $rocznikMeta = get_post_meta($idRocznik);
    $currentSezon = $rocznikMeta['ii_rocznik_sezon'][0];
    $initialYear = intval($rocznikMeta['ii_rocznik_rok'][0]);
    $rocznikTitle = $rocznikMeta['ii_rocznik'][0];
    $kierunekTitle = $rocznikMeta['ii_kierunek'][0];
    $trybTitle = $rocznikMeta['ii_rodzaj'][0];
    
    $currentYear = $initialYear;

    if ($currentSezon == 'lato') {
    foreach ($data as $rowData) {
        $semesterNumber = intval($rowData[0]);
    
        if ($semesterNumber % 2 == 1) {
            $currentSezon = 'lato';
            $currentYear = $initialYear + intval(($semesterNumber - 1) / 2);
        } else {
            $currentSezon = 'zima';
            $currentYear = $initialYear + intval($semesterNumber / 2);
        }
    
        // Dodaj semestr
        $currentSemestrId = ii_add_semestr($semesterNumber, $currentSezon, $currentYear, $idRocznik, $rocznikTitle, $kierunekTitle, $trybTitle);
        
        // Dodaj przedmiot do semestru
        ii_update_przedmiot($rowData[1], $idRocznik, $currentSemestrId, $semesterNumber);
    }
    }else if ($currentSezon == 'zima') {
        foreach ($data as $rowData) {
            $semesterNumber = intval($rowData[0]);
    
            if ($semesterNumber % 2 == 1) {
                $currentSezon = 'zima';
                $currentYear = $initialYear + intval($semesterNumber / 2);
            } else {
                $currentSezon = 'lato';
                $currentYear = $initialYear + intval(($semesterNumber - 1) / 2);
            }
    
            // Dodaj semestr
            $currentSemestrId = ii_add_semestr($semesterNumber, $currentSezon, $currentYear, $idRocznik, $rocznikTitle, $kierunekTitle, $trybTitle);
            
            // Dodaj przedmiot do semestru
            ii_update_przedmiot($rowData[1], $idRocznik, $currentSemestrId, $semesterNumber);
        }
    }
}

function ii_add_semestr($semesterNumber, $currentSezon, $currentYear, $idRocznik, $rocznikTitle, $kierunekTitle, $trybTitle)
{
    // Wstępna nazwa semestru na podstawie numeru, sezonu i roku
    $semester_name = "Semestr $semesterNumber, $currentSezon $currentYear ($kierunekTitle $rocznikTitle $trybTitle)";

    // Ustawienia argumentów wyszukiwania, aby uniknąć duplikatów
    $args = array(
        'post_type' => 'semestr',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        's' => $semester_name,
        'meta_query' => array(
            array(
                'key' => 'ii_rocznik',
                'value' => $idRocznik
            ),
            array(
                'key' => 'ii_sezon',
                'value' => $currentSezon
            ),
            array(
                'key' => 'ii_rok',
                'value' => $currentYear
            )
        )
    );
    
    $existing_semesters = get_posts($args);
    
    // Sprawdzenie, czy semestr o tej nazwie i roczniku już istnieje
    if (!empty($existing_semesters)) {
        return $existing_semesters[0]->ID;
    } else {
        // Utwórz nowy wpis semestru z wygenerowaną nazwą
        $semester_data = array(
            'post_title' => $semester_name,
            'post_content' => "[semestr]",
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'semestr'
        );

        $semester_id = wp_insert_post($semester_data);

        // Dodaj metadane do wpisu semestru
        update_post_meta($semester_id, 'ii_rocznik', $idRocznik);
        update_post_meta($semester_id, 'ii_semestr', $semester_id);
        update_post_meta($semester_id, 'ii_sezon', $currentSezon);
        update_post_meta($semester_id, 'ii_rok', $currentYear);

        return $semester_id;
    }
}

function ii_update_przedmiot($subject_name, $idRocznik, $currentSemestrId, $semesterNumber)
{
    // Normalizujemy nazwę przedmiotu do wyszukiwania (usuwamy dodatkowe spacje i ustawiamy na małe litery)
    $normalized_subject_name = trim(mb_strtolower($subject_name));

    // Wyszukiwanie przedmiotu na podstawie znormalizowanej nazwy
    $args = array(
        'post_type' => 'przedmiot',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'ii_nazwa',
                'value' => $normalized_subject_name,
                'compare' => '='
            )
        )
    );

    $existing_subjects = get_posts($args);

    // Sprawdzenie, czy istnieje przedmiot o pełnej nazwie (po znormalizowanej wersji `post_title`)
    $subject_id = null;
    if (!empty($existing_subjects)) {
        foreach ($existing_subjects as $existing_subject) {
            if (trim(mb_strtolower($existing_subject->post_title)) === $normalized_subject_name) {
                $subject_id = $existing_subject->ID;
                break;
            }
        }
    }

    // Jeśli nie znaleziono przedmiotu, stwórz nowy
    if (!$subject_id) {
        $subject_data = array(
            'post_title' => $subject_name,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'przedmiot'
        );

        $subject_id = wp_insert_post($subject_data);
        // Dodaj metadane dla nowego przedmiotu (normalizujemy zapisane dane)
        update_post_meta($subject_id, 'ii_nazwa', $normalized_subject_name);
    }

    // Dodaj metadane do przedmiotu
    update_post_meta($subject_id, 'ii_rok', $idRocznik);

    // Pobierz powiązane semestry dla przedmiotu
    $semesters = get_post_meta($subject_id, 'ii_semestry', true);
    if (!is_array($semesters)) {
        $semesters = array();
    }

    // Dodaj semestr do przedmiotu, jeśli jeszcze nie istnieje
    if (!in_array($currentSemestrId, $semesters)) {
        $semesters[] = $currentSemestrId;
        update_post_meta($subject_id, 'ii_semestry', $semesters);
    }

    // Powiąż przedmiot z semestrem
    $przedmioty = get_post_meta($currentSemestrId, 'ii_przedmioty', true);
    if (!is_array($przedmioty)) {
        $przedmioty = array();
    }

    // Upewnij się, że nie dodajemy duplikatów
    if (!in_array($subject_id, $przedmioty)) {
        $przedmioty[] = $subject_id;
        update_post_meta($currentSemestrId, 'ii_przedmioty', $przedmioty);
    }
}