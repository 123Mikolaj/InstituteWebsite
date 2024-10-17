<?php
/*
Plugin Name: read excel
Plugin URI: https://ii.uken.krakow.pl
Description: Wtyczka dedykowana dla II
Version: 1.0
Author: Estarte
License: GNU
*/

// Mikołaj - funkcja pozwala dodac zakładke w menu
// Utwórz zakładke sylabusy
// Wyszukaj tam wszystkie sylabusy które są lub które powinny być
// w formie jednej tabelki

// Dodaj wyszukiwarkę i filtrowanie

// Jak chcę mieć formularz to muszę go stworzyć form. To jest pusta zakładka.
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
        'e_start', // parent menu slug
        'New Submenu Item', // submenu page title
        'New Submenu Item', // submenu item title
        'anage_options',
        'new_submenu_item', // submenu page slug
        'new_submenu_item_callback' // callback function
    );
}
add_action('admin_menu', 're_menu');
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
    include(plugin_dir_path(__FILE__) . 'Classes/PHPExcel.php');

    $inputFileName = plugin_dir_path(__FILE__) . 'CB_N_2023.xlsx';

    //  Read your Excel workbook
    try {
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
    } catch (Exception $e) {
        die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    //  Get worksheet dimensions
    $sheet = $objPHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $data = array(); // array witch data
    //  Loop through each row of the worksheet in turn
    for ($row = 1; $row <= $highestRow; $row++) {
        //  Read a row of data into an array
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
        $data[] = $rowData[0];
    }

    foreach ($data as $rowData) {
        echo 'Semestr ' . $rowData[0] . ', przedmiot: ' . $rowData[1] . '<br>';
    }

    echo 'test ok';
}

function add_semesters_excel2($attachment_id, $idRocznik)
{

    static $called = false;
    if ($called) return;
    $called = true;

    // Pobierz lokalną ścieżkę załączonego pliku
    $file_path = get_attached_file($attachment_id);

    // Importuj plik za pomocą PHPExcel
    include_once(plugin_dir_path(__FILE__) . 'Classes/PHPExcel.php');
    include_once plugin_dir_path(__FILE__) . '../ii/semestry.php';

    // Identyfikacja typu pliku Excel, utworzenie odpowiedniego obiektu czytającego i załadowanie pliku Excel do obiektu PHPExcel
    try {
        $inputFileType = PHPExcel_IOFactory::identify($file_path);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);
    } catch (Exception $e) {
        die('Błąd ładowania pliku "' . pathinfo($file_path, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    // Pobierz wymiary arkusza
    $sheet = $objPHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    // Wyświetl zawartość pliku Excel
    echo "<table border='1'>";
    for ($row = 1; $row <= $highestRow; $row++) {
        echo "<tr>";
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $cellValue = $sheet->getCell($col . $row)->getValue();
            echo "<td>$cellValue</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    // Inicjalizuj tablicę danych
    $data = array();

    // Przeiteruj przez każdy wiersz arkusza (zacznij od wiersza 2, aby pominąć nagłówki)
    for ($row = 2; $row <= $highestRow; $row++) {
        // Przeczytaj wiersz danych do tablicy (valueOnly ustawione na TRUE, aby uzyskać jednowymiarową tablicę)
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, TRUE);
        // Dodaj cały wiersz danych do tablicy $data
        $data[] = $rowData[0];
    }

    // Teraz masz czyste dane do pracy

    // Inicjalizacja zmiennych
    $currentSemestr = 0;
    $currentSemestrId = 0;
    $rocznikMeta = get_post_meta($idRocznik);
    $currentSezon = $rocznikMeta['ii_rocznik_sezon'][0]; // zmiana na ii_rocznik_sezon
    $currentYear = intval($rocznikMeta['ii_rocznik_rok'][0]); // zmiana na ii_rocznik_rok

    $rocznikMeta = get_post_meta($idRocznik);
    $rocznikTitle = $rocznikMeta['ii_rocznik'][0];
    $kierunekTitle = $rocznikMeta['ii_kierunek'][0];
    $trybTitle = $rocznikMeta['ii_rodzaj'][0];




    foreach ($data as $rowData) {

        $semesterNumber = intval(explode(', ', $rowData[0])[0]);

        if (!in_array($semesterNumber, $addedSemesters)) { // Sprawdź, czy semestr nie jest już w tablicy

            $currentSemestrId = ii_add_semestr($semesterNumber, $currentSezon, $currentYear, $idRocznik, $rocznikTitle, $kierunekTitle, $trybTitle);

            $addedSemesters[] = $semesterNumber; // Dodaj semestr do tablicy

            // Zmień wartości sezonu i roku
            if ($currentSezon == 'zima') {
                $currentSezon = 'lato';
            } else {
                $currentSezon = 'zima';
                $currentYear++;
            }
        }
        ii_update_przedmiot($rowData[1], $idRocznik, $currentSemestrId, $semesterNumber);
    }
}

function ii_add_semestr($semesterNumber, $currentSezon, $currentYear, $idRocznik, $rocznikTitle, $kierunekTitle, $trybTitle)
{


    // Check if a semester with the same title and rocznik already exists
    $args = array(

        'post_type' => 'semestr',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        's' => "Semestr $semesterNumber, $currentSezon $currentYear ($kierunekTitle $rocznikTitle $trybTitle)",
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

    if (!empty($existing_semesters)) {

        // Semester with the same title and rocznik already exists
        return $existing_semesters[0]->ID;
    } else {

        // Utwórz nowy wpis semestru
        $semester_data = array(

            'post_title' => "Semestr $semesterNumber, $currentSezon $currentYear ($kierunekTitle $rocznikTitle $trybTitle)",
            'post_content' => '',
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

    // Check if a subject with the same title already exists
    $args = array(

        'post_type' => 'przedmiot',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'title' => $subject_name

    );

    $existing_subjects = get_posts($args);

    if (!empty($existing_subjects)) {

        $subject_id = $existing_subjects[0]->ID;
    } else {

        // Create a new subject
        $subject_data = array(

            'post_title' => $subject_name,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'przedmiot'

        );

        $subject_id = wp_insert_post($subject_data);
    }

    // Tworzenie ciągu tekstowego zawierającego nazwę semestru i jego ID
    $semestr_info = "Semestr: $semesterNumber ID: $currentSemestrId";

    // Zaktualizuj metadane dla wpisu przedmiotu

    update_post_meta($subject_id, 'ii_rok', $idRocznik);

    // Store an array of semester IDs for the subject
    $semesters = get_post_meta($subject_id, 'ii_semestry', true);

    if (!is_array($semesters)) {
        $semesters = array();
    }

    if (!in_array($currentSemestrId, $semesters)) {

        $semesters[] = $currentSemestrId;
    }

    update_post_meta($subject_id, 'ii_semestry', $semesters);

    // Sprawdź, czy przedmiot jest już powiązany z semestrem
    $przedmioty = get_post_meta($currentSemestrId, 'ii_przedmioty', true);

    if (!in_array($subject_id, $przedmioty)) {

        // Powiąż przedmiot z semestrem
        update_post_meta($currentSemestrId, 'ii_przedmioty', array_merge($przedmioty, array($subject_id)));
    }
}